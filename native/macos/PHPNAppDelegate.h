#import <Cocoa/Cocoa.h>

@interface PHPNAppDelegate : NSObject <NSApplicationDelegate>

@property (strong, nonatomic) NSString *appPath;
@property (strong, nonatomic) NSWindow *mainWindow;
@property (nonatomic) int windowWidth;
@property (nonatomic) int windowHeight;
@property (strong, nonatomic) NSString *windowTitle;

@end

