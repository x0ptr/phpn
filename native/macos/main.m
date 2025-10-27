#import <Cocoa/Cocoa.h>
#import "PHPNAppDelegate.h"

int main(int argc, const char *argv[]) {
    @autoreleasepool {
        NSApplication *app = [NSApplication sharedApplication];
        [app setActivationPolicy:NSApplicationActivationPolicyRegular];
        
        PHPNAppDelegate *delegate = [[PHPNAppDelegate alloc] init];
        
        if (argc > 1) {
            delegate.appPath = [NSString stringWithUTF8String:argv[1]];
        } else {
            delegate.appPath = [[NSFileManager defaultManager] currentDirectoryPath];
        }
        
        const char *widthEnv = getenv("PHPN_WINDOW_WIDTH");
        const char *heightEnv = getenv("PHPN_WINDOW_HEIGHT");
        const char *titleEnv = getenv("PHPN_WINDOW_TITLE");
        
        if (widthEnv) {
            delegate.windowWidth = atoi(widthEnv);
        }
        if (heightEnv) {
            delegate.windowHeight = atoi(heightEnv);
        }
        if (titleEnv) {
            delegate.windowTitle = [NSString stringWithUTF8String:titleEnv];
        }
        
        [app setDelegate:delegate];
        [app run];
    }
    
    return 0;
}

