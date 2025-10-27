#import "PHPNWindow.h"
#import "bridge.h"

@implementation PHPNWindow

- (instancetype)initWithPHPRuntime:(PHPRuntime *)runtime 
                           appPath:(NSString *)path
                             width:(int)width
                            height:(int)height
                             title:(NSString *)title {
    NSRect frame = NSMakeRect(0, 0, width, height);
    
    self = [super initWithContentRect:frame
                            styleMask:(NSWindowStyleMaskTitled |
                                     NSWindowStyleMaskClosable |
                                     NSWindowStyleMaskMiniaturizable |
                                     NSWindowStyleMaskResizable)
                              backing:NSBackingStoreBuffered
                                defer:NO];
    
    if (self) {
        self.phpRuntime = runtime;
        self.appPath = path;
        self.appDirectory = [path stringByDeletingLastPathComponent];
        
        if (title && title.length > 0) {
            [self setTitle:title];
        } else {
            [self setTitle:@"PHPN Application"];
        }
        [self center];
        
        [self setupWebView];
        [self loadInitialPage];
    }
    
    return self;
}

- (void)setupWebView {
    WKWebViewConfiguration *config = [[WKWebViewConfiguration alloc] init];
    WKUserContentController *controller = [[WKUserContentController alloc] init];
    
    NSString *bridgeScript = @
        "window.PHPN = {"
        "    call: function(method, args) {"
        "        return new Promise((resolve, reject) => {"
        "            window.webkit.messageHandlers.phpn.postMessage({"
        "                method: method,"
        "                args: args || [],"
        "                callback: function(result) {"
        "                    resolve(result);"
        "                }"
        "            });"
        "        });"
        "    },"
        "    version: '1.0.0'"
        "};";
    
    WKUserScript *script = [[WKUserScript alloc] initWithSource:bridgeScript
                                                  injectionTime:WKUserScriptInjectionTimeAtDocumentStart
                                               forMainFrameOnly:YES];
    
    [controller addUserScript:script];
    [controller addScriptMessageHandler:self name:@"phpn"];
    
    config.userContentController = controller;
    
    self.webView = [[WKWebView alloc] initWithFrame:self.contentView.bounds
                                      configuration:config];
    
    self.webView.navigationDelegate = self;
    self.webView.UIDelegate = self;
    self.webView.autoresizingMask = NSViewWidthSizable | NSViewHeightSizable;
    
    [self.contentView addSubview:self.webView];
}

- (void)loadInitialPage {
    NSLog(@"Loading initial page");
    
    PHPRequest request = {
        .method = "GET",
        .content_type = NULL,
        .post_data = NULL,
        .post_data_length = 0,
        .cookies = NULL,
        .user_agent = "PHPN/1.0 (WebKit)"
    };
    
    char *html = php_runtime_execute(self.phpRuntime, [self.appPath UTF8String], "/", &request);
    
    if (html) {
        NSString *htmlString = [NSString stringWithUTF8String:html];
        [self.webView loadHTMLString:htmlString baseURL:nil];
        free(html);
    } else {
        NSString *errorHTML = @"<html><body><h1>Error loading PHP application</h1></body></html>";
        [self.webView loadHTMLString:errorHTML baseURL:nil];
    }
}

#pragma mark - WKScriptMessageHandler

- (void)userContentController:(WKUserContentController *)userContentController
      didReceiveScriptMessage:(WKScriptMessage *)message {
    
    if ([message.name isEqualToString:@"phpn"]) {
        NSDictionary *body = (NSDictionary *)message.body;
        NSString *method = body[@"method"];
        NSArray *args = body[@"args"];
        
        NSLog(@"Bridge call: %@ with args: %@", method, args);
        
        NSError *error;
        NSData *jsonData = [NSJSONSerialization dataWithJSONObject:args
                                                           options:0
                                                             error:&error];
        
        if (!error) {
            NSString *jsonString = [[NSString alloc] initWithData:jsonData
                                                         encoding:NSUTF8StringEncoding];
            
            char *result = phpn_bridge_call([method UTF8String], [jsonString UTF8String]);
            
            if (result) {
                NSString *jsCallback = [NSString stringWithFormat:@"console.log('Bridge result:', %s);",
                                       result];
                [self.webView evaluateJavaScript:jsCallback completionHandler:nil];
                free(result);
            }
        }
    }
}

- (void)webView:(WKWebView *)webView
decidePolicyForNavigationAction:(WKNavigationAction *)navigationAction
decisionHandler:(void (^)(WKNavigationActionPolicy))decisionHandler {
    
    NSURL *url = navigationAction.request.URL;
    
    NSLog(@"Navigation URL: %@", url);
    NSLog(@"Navigation Type: %ld", (long)navigationAction.navigationType);
    
    if (navigationAction.navigationType == WKNavigationTypeReload) {
        NSLog(@"Reload detected, reloading index.php");
        [self loadInitialPage];
        decisionHandler(WKNavigationActionPolicyCancel);
        return;
    }
    
    if ([url.scheme isEqualToString:@"http"] || [url.scheme isEqualToString:@"https"]) {
        [[NSWorkspace sharedWorkspace] openURL:url];
        decisionHandler(WKNavigationActionPolicyCancel);
        return;
    }
    
    if ([url.scheme isEqualToString:@"about"] || 
        [url.scheme isEqualToString:@"data"] ||
        url == nil) {
        decisionHandler(WKNavigationActionPolicyAllow);
        return;
    }
    
    NSString *path = url.path ?: @"/index.php";
    if ([path isEqualToString:@"/"]) {
        path = @"/index.php";
    }
    
    NSString *requestURI = path;
    if (url.query && url.query.length > 0) {
        requestURI = [NSString stringWithFormat:@"%@?%@", path, url.query];
    }
    
    NSLog(@"Loading PHP: %@ (Request URI: %@)", path, requestURI);
    
    NSString *scriptPath = [self.appDirectory stringByAppendingString:path];
    
    NSHTTPCookieStorage *cookieStorage = [NSHTTPCookieStorage sharedHTTPCookieStorage];
    NSArray *cookies = [cookieStorage cookies];
    NSMutableString *cookieString = [NSMutableString string];
    for (NSHTTPCookie *cookie in cookies) {
        if (cookieString.length > 0) {
            [cookieString appendString:@"; "];
        }
        [cookieString appendFormat:@"%@=%@", cookie.name, cookie.value];
    }
    
    // Create request structure
    PHPRequest request = {
        .method = "GET",
        .content_type = NULL,
        .post_data = NULL,
        .post_data_length = 0,
        .cookies = cookieString.length > 0 ? [cookieString UTF8String] : NULL,
        .user_agent = "PHPN/1.0 (WebKit)"
    };
    
    char *html = php_runtime_execute(self.phpRuntime, [scriptPath UTF8String], [requestURI UTF8String], &request);
    
    if (html) {
        NSString *htmlString = [NSString stringWithUTF8String:html];
        [webView loadHTMLString:htmlString baseURL:nil];
        free(html);
    } else {
        NSString *errorHTML = @"<html><body><h1>Error: Could not load PHP file</h1></body></html>";
        [webView loadHTMLString:errorHTML baseURL:nil];
    }
    
    decisionHandler(WKNavigationActionPolicyCancel);
}

- (void)webView:(WKWebView *)webView
runJavaScriptAlertPanelWithMessage:(NSString *)message
initiatedByFrame:(WKFrameInfo *)frame
completionHandler:(void (^)(void))completionHandler {
    
    NSAlert *alert = [[NSAlert alloc] init];
    [alert setMessageText:@"PHPN"];
    [alert setInformativeText:message];
    [alert addButtonWithTitle:@"OK"];
    [alert beginSheetModalForWindow:self completionHandler:^(NSModalResponse returnCode) {
        completionHandler();
    }];
}

- (void)webView:(WKWebView *)webView
runJavaScriptConfirmPanelWithMessage:(NSString *)message
initiatedByFrame:(WKFrameInfo *)frame
completionHandler:(void (^)(BOOL))completionHandler {
    
    NSAlert *alert = [[NSAlert alloc] init];
    [alert setMessageText:@"PHPN"];
    [alert setInformativeText:message];
    [alert addButtonWithTitle:@"OK"];
    [alert addButtonWithTitle:@"Cancel"];
    [alert beginSheetModalForWindow:self completionHandler:^(NSModalResponse returnCode) {
        completionHandler(returnCode == NSAlertFirstButtonReturn);
    }];
}

- (void)webView:(WKWebView *)webView
runJavaScriptTextInputPanelWithPrompt:(NSString *)prompt
defaultText:(NSString *)defaultText
initiatedByFrame:(WKFrameInfo *)frame
completionHandler:(void (^)(NSString *))completionHandler {
    
    NSAlert *alert = [[NSAlert alloc] init];
    [alert setMessageText:@"PHPN"];
    [alert setInformativeText:prompt];
    [alert addButtonWithTitle:@"OK"];
    [alert addButtonWithTitle:@"Cancel"];
    
    NSTextField *input = [[NSTextField alloc] initWithFrame:NSMakeRect(0, 0, 200, 24)];
    [input setStringValue:defaultText ?: @""];
    [alert setAccessoryView:input];
    
    [alert beginSheetModalForWindow:self completionHandler:^(NSModalResponse returnCode) {
        if (returnCode == NSAlertFirstButtonReturn) {
            completionHandler([input stringValue]);
        } else {
            completionHandler(nil);
        }
    }];
}

- (void)reload {
    [self loadInitialPage];
}

@end

