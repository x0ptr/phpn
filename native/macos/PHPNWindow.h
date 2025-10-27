#import <Cocoa/Cocoa.h>
#import <WebKit/WebKit.h>
#import "php_runtime.h"

@interface PHPNWindow : NSWindow <WKNavigationDelegate, WKScriptMessageHandler, WKUIDelegate>

@property (strong, nonatomic) WKWebView *webView;
@property (nonatomic) PHPRuntime *phpRuntime;
@property (strong, nonatomic) NSString *appPath;
@property (strong, nonatomic) NSString *appDirectory;

- (instancetype)initWithPHPRuntime:(PHPRuntime *)runtime 
                           appPath:(NSString *)path
                             width:(int)width
                            height:(int)height
                             title:(NSString *)title;

@end

