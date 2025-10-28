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
        self.cookieJar = [NSMutableDictionary dictionary];  // Initialize cookie jar
        
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
        "};"
        ""
        "// Intercept form submissions"
        "document.addEventListener('submit', function(e) {"
        "    const form = e.target;"
        "    e.preventDefault();"
        "    "
        "    const formData = new FormData(form);"
        "    const method = (form.method || 'GET').toUpperCase();"
        "    const action = form.action || window.location.href;"
        "    "
        "    if (method === 'POST') {"
        "        // Convert FormData to URLSearchParams to preserve all fields including _token"
        "        const params = new URLSearchParams();"
        "        for (let [key, value] of formData.entries()) {"
        "            params.append(key, value);"
        "        };"
        "        "
        "        window.webkit.messageHandlers.formSubmit.postMessage({"
        "            method: method,"
        "            action: action,"
        "            data: params.toString()"
        "        });"
        "    } else {"
        "        window.location.href = action;"
        "    }"
        "}, true);";
    
    WKUserScript *script = [[WKUserScript alloc] initWithSource:bridgeScript
                                                  injectionTime:WKUserScriptInjectionTimeAtDocumentStart
                                               forMainFrameOnly:YES];
    
    [controller addUserScript:script];
    [controller addScriptMessageHandler:self name:@"phpn"];
    [controller addScriptMessageHandler:self name:@"formSubmit"];
    
    config.userContentController = controller;
    
    self.webView = [[WKWebView alloc] initWithFrame:self.contentView.bounds
                                      configuration:config];
    
    self.webView.navigationDelegate = self;
    self.webView.UIDelegate = self;
    self.webView.autoresizingMask = NSViewWidthSizable | NSViewHeightSizable;
    
    [self.contentView addSubview:self.webView];
}

- (void)storeCookiesFromPHPResponse {
    int cookieCount = 0;
    CookieHeader *cookies = php_runtime_get_response_cookies(self.phpRuntime, &cookieCount);
    
    if (cookieCount == 0) {
        return;
    }
    
    for (int i = 0; i < cookieCount; i++) {
        if (!cookies[i].header) continue;
        
        NSString *headerString = [NSString stringWithUTF8String:cookies[i].header];
        
        // Parse Set-Cookie header: "Set-Cookie: name=value; attributes..."
        if ([headerString hasPrefix:@"Set-Cookie:"]) {
            NSString *cookieString = [headerString substringFromIndex:11];
            cookieString = [cookieString stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceCharacterSet]];
            
            // Split by semicolon to get cookie parts
            NSArray *parts = [cookieString componentsSeparatedByString:@";"];
            if (parts.count == 0) continue;
            
            // Parse the name=value part
            NSString *nameValue = [[parts firstObject] stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceCharacterSet]];
            NSArray *nvParts = [nameValue componentsSeparatedByString:@"="];
            if (nvParts.count < 2) continue;
            
            NSString *name = nvParts[0];
            NSString *value = [[nvParts subarrayWithRange:NSMakeRange(1, nvParts.count - 1)] componentsJoinedByString:@"="];
            
            // Store in our simple cookie jar (ignore all attributes - we don't need security checks)
            self.cookieJar[name] = value;
            
            NSString *displayValue = value.length > 20 ? 
                [NSString stringWithFormat:@"%@...", [value substringToIndex:20]] : value;
            NSLog(@"Stored cookie: %@ = %@", name, displayValue);
        }
    }
}

- (void)loadInitialPage {
    PHPRequest request = {
        .method = "GET",
        .content_type = NULL,
        .post_data = NULL,
        .post_data_length = 0,
        .cookies = NULL,
        .user_agent = "PHPN/1.0 (WebKit)",
        .xsrf_token = NULL
    };
    
    NSLog(@"━━━ REQUEST ━━━");
    NSLog(@"GET /");
    NSLog(@"Cookies: (none)");
    
    char *html = php_runtime_execute(self.phpRuntime, [self.appPath UTF8String], "/", &request);
    
    // Store any cookies from the response
    [self storeCookiesFromPHPResponse];
    
    if (html) {
        NSString *htmlString = [NSString stringWithUTF8String:html];
        NSLog(@"━━━ RESPONSE ━━━");
        NSLog(@"Status: 200 OK");
        NSLog(@"Size: %lu bytes", (unsigned long)htmlString.length);
        [self.webView loadHTMLString:htmlString baseURL:nil];
        free(html);
    } else {
        NSLog(@"━━━ RESPONSE ━━━");
        NSLog(@"Status: ERROR - No response");
        NSString *errorHTML = @"<html><body><h1>Error loading PHP application</h1></body></html>";
        [self.webView loadHTMLString:errorHTML baseURL:nil];
    }
}

#pragma mark - WKScriptMessageHandler

- (void)userContentController:(WKUserContentController *)userContentController
      didReceiveScriptMessage:(WKScriptMessage *)message {
    
    if ([message.name isEqualToString:@"formSubmit"]) {
        NSDictionary *body = (NSDictionary *)message.body;
        NSString *method = body[@"method"];
        NSString *action = body[@"action"];
        NSString *postString = body[@"data"]; // Now it's already a URL-encoded string
        
        // Parse URL
        NSURL *url = [NSURL URLWithString:action];
        NSString *path = url.path ?: @"/index.php";
        if ([path isEqualToString:@"/"]) {
            path = @"/index.php";
        }
        
        NSString *requestURI = path;
        if (url.query && url.query.length > 0) {
            requestURI = [NSString stringWithFormat:@"%@?%@", path, url.query];
        }
        
        NSData *postData = [postString dataUsingEncoding:NSUTF8StringEncoding];
        NSString *scriptPath = [self.appDirectory stringByAppendingString:@"/index.php"];
        
        // Get cookies from our cookie jar
        NSMutableString *cookieString = [NSMutableString string];
        NSMutableString *cookieDisplay = [NSMutableString string];
        for (NSString *name in self.cookieJar) {
            NSString *value = self.cookieJar[name];
            if (cookieString.length > 0) {
                [cookieString appendString:@"; "];
                [cookieDisplay appendString:@"; "];
            }
            [cookieString appendFormat:@"%@=%@", name, value];
            // Show first 20 chars of cookie value for debugging
            NSString *displayValue = value.length > 20 ? 
                [NSString stringWithFormat:@"%@...", [value substringToIndex:20]] : value;
            [cookieDisplay appendFormat:@"%@=%@", name, displayValue];
        }
        
        // Parse POST data for display (show keys only, truncate values)
        NSMutableString *postDataDisplay = [NSMutableString string];
        NSArray *params = [postString componentsSeparatedByString:@"&"];
        for (NSString *param in params) {
            NSArray *parts = [param componentsSeparatedByString:@"="];
            if (parts.count >= 1) {
                if (postDataDisplay.length > 0) [postDataDisplay appendString:@", "];
                [postDataDisplay appendString:parts[0]];
                if (parts.count >= 2) {
                    NSString *value = parts[1];
                    NSString *displayValue = value.length > 20 ? 
                        [NSString stringWithFormat:@"=%@...", [value substringToIndex:20]] : 
                        [NSString stringWithFormat:@"=%@", value];
                    [postDataDisplay appendString:displayValue];
                }
            }
        }
        
        NSLog(@"━━━ REQUEST ━━━");
        NSLog(@"%@ %@", method, requestURI);
        NSLog(@"Content-Type: application/x-www-form-urlencoded");
        NSLog(@"Content-Length: %lu", (unsigned long)postData.length);
        NSLog(@"Cookies: %@", cookieDisplay.length > 0 ? cookieDisplay : @"(none)");
        NSLog(@"POST Data: %@", postDataDisplay);
        
        // Get XSRF-TOKEN value from cookie jar for the X-XSRF-TOKEN header
        NSString *xsrfToken = self.cookieJar[@"XSRF-TOKEN"];
        const char *xsrfTokenCStr = xsrfToken ? [xsrfToken UTF8String] : NULL;
        
        if (xsrfToken) {
            NSString *displayToken = xsrfToken.length > 30 ? 
                [[xsrfToken substringToIndex:30] stringByAppendingString:@"..."] : xsrfToken;
            NSLog(@"X-XSRF-TOKEN: %@", displayToken);
        }
        
        // Create POST request
        PHPRequest request = {
            .method = "POST",
            .content_type = "application/x-www-form-urlencoded",
            .post_data = [postData bytes],
            .post_data_length = postData.length,
            .cookies = cookieString.length > 0 ? [cookieString UTF8String] : NULL,
            .user_agent = "PHPN/1.0 (WebKit)",
            .xsrf_token = xsrfTokenCStr
        };
        
        char *html = php_runtime_execute(self.phpRuntime, [scriptPath UTF8String], [requestURI UTF8String], &request);
        
        // Store any cookies from the response
        [self storeCookiesFromPHPResponse];
        
        if (html) {
            NSString *htmlString = [NSString stringWithUTF8String:html];
            NSLog(@"━━━ RESPONSE ━━━");
            NSLog(@"Status: 200 OK");
            NSLog(@"Size: %lu bytes", (unsigned long)htmlString.length);
            [self.webView loadHTMLString:htmlString baseURL:nil];
            free(html);
        } else {
            NSLog(@"━━━ RESPONSE ━━━");
            NSLog(@"Status: ERROR - No response");
            NSString *errorHTML = @"<html><body><h1>Error: Could not process form submission</h1></body></html>";
            [self.webView loadHTMLString:errorHTML baseURL:nil];
        }
        
        return;
    }
    
    if ([message.name isEqualToString:@"phpn"]) {
        NSDictionary *body = (NSDictionary *)message.body;
        NSString *method = body[@"method"];
        NSArray *args = body[@"args"];
        
        NSLog(@"━━━ BRIDGE CALL ━━━");
        NSLog(@"Method: %@", method);
        
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
    
    if (navigationAction.navigationType == WKNavigationTypeReload) {
        [self loadInitialPage];
        decisionHandler(WKNavigationActionPolicyCancel);
        return;
    }
    
    // Check if this is an external URL (http/https with a real host)
    if ([url.scheme isEqualToString:@"http"] || [url.scheme isEqualToString:@"https"]) {
        NSString *host = url.host;
        
        // If there's a host and it's not localhost/127.0.0.1, open externally
        if (host && host.length > 0 && 
            ![host isEqualToString:@"localhost"] && 
            ![host isEqualToString:@"127.0.0.1"] &&
            ![host hasPrefix:@"localhost:"] &&
            ![host hasPrefix:@"127.0.0.1:"]) {
            NSLog(@"━━━ EXTERNAL URL ━━━");
            NSLog(@"Opening: %@", url);
            [[NSWorkspace sharedWorkspace] openURL:url];
            decisionHandler(WKNavigationActionPolicyCancel);
            return;
        }
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
    
    // For Laravel apps, always route through index.php
    NSString *scriptPath = [self.appDirectory stringByAppendingString:@"/index.php"];
    
    // Get cookies from our cookie jar
    NSMutableString *cookieString = [NSMutableString string];
    NSMutableString *cookieDisplay = [NSMutableString string];
    for (NSString *name in self.cookieJar) {
        NSString *value = self.cookieJar[name];
        if (cookieString.length > 0) {
            [cookieString appendString:@"; "];
            [cookieDisplay appendString:@"; "];
        }
        [cookieString appendFormat:@"%@=%@", name, value];
        // Show first 20 chars of cookie value for debugging
        NSString *displayValue = value.length > 20 ? 
            [NSString stringWithFormat:@"%@...", [value substringToIndex:20]] : value;
        [cookieDisplay appendFormat:@"%@=%@", name, displayValue];
    }
    
    // Get HTTP method from request
    NSString *httpMethod = navigationAction.request.HTTPMethod ?: @"GET";
    
    // Get POST data if available
    NSData *httpBody = navigationAction.request.HTTPBody;
    const char *postData = NULL;
    size_t postDataLength = 0;
    const char *contentType = NULL;
    NSMutableString *postDataDisplay = [NSMutableString string];
    
    if (httpBody && httpBody.length > 0) {
        postData = [httpBody bytes];
        postDataLength = httpBody.length;
        
        NSString *contentTypeHeader = [navigationAction.request valueForHTTPHeaderField:@"Content-Type"];
        if (contentTypeHeader) {
            contentType = [contentTypeHeader UTF8String];
        }
        
        // Parse POST data for display
        NSString *postString = [[NSString alloc] initWithData:httpBody encoding:NSUTF8StringEncoding];
        if (postString) {
            NSArray *params = [postString componentsSeparatedByString:@"&"];
            for (NSString *param in params) {
                NSArray *parts = [param componentsSeparatedByString:@"="];
                if (parts.count >= 1) {
                    if (postDataDisplay.length > 0) [postDataDisplay appendString:@", "];
                    [postDataDisplay appendString:parts[0]];
                    if (parts.count >= 2) {
                        NSString *value = parts[1];
                        NSString *displayValue = value.length > 20 ? 
                            [NSString stringWithFormat:@"=%@...", [value substringToIndex:20]] : 
                            [NSString stringWithFormat:@"=%@", value];
                        [postDataDisplay appendString:displayValue];
                    }
                }
            }
        }
    }
    
    NSLog(@"━━━ REQUEST ━━━");
    NSLog(@"%@ %@", httpMethod, requestURI);
    if (contentType) {
        NSLog(@"Content-Type: %s", contentType);
    }
    if (postDataLength > 0) {
        NSLog(@"Content-Length: %lu", (unsigned long)postDataLength);
        NSLog(@"POST Data: %@", postDataDisplay);
    }
    NSLog(@"Cookies: %@", cookieDisplay.length > 0 ? cookieDisplay : @"(none)");
    
    // Get XSRF-TOKEN value from cookie jar for the X-XSRF-TOKEN header
    NSString *xsrfToken = self.cookieJar[@"XSRF-TOKEN"];
    const char *xsrfTokenCStr = xsrfToken ? [xsrfToken UTF8String] : NULL;
    
    // Create request structure
    PHPRequest request = {
        .method = [httpMethod UTF8String],
        .content_type = contentType,
        .post_data = postData,
        .post_data_length = postDataLength,
        .cookies = cookieString.length > 0 ? [cookieString UTF8String] : NULL,
        .user_agent = "PHPN/1.0 (WebKit)",
        .xsrf_token = xsrfTokenCStr
    };
    
    char *html = php_runtime_execute(self.phpRuntime, [scriptPath UTF8String], [requestURI UTF8String], &request);
    
    // Store any cookies from the response
    [self storeCookiesFromPHPResponse];
    
    if (html) {
        NSString *htmlString = [NSString stringWithUTF8String:html];
        NSLog(@"━━━ RESPONSE ━━━");
        NSLog(@"Status: 200 OK");
        NSLog(@"Size: %lu bytes", (unsigned long)htmlString.length);
        [webView loadHTMLString:htmlString baseURL:nil];
        free(html);
    } else {
        NSLog(@"━━━ RESPONSE ━━━");
        NSLog(@"Status: ERROR - No response");
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

