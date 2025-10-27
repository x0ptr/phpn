#import "PHPNAppDelegate.h"
#import "PHPNWindow.h"
#import "php_runtime.h"
#import "bridge.h"

@implementation PHPNAppDelegate {
    PHPRuntime *_phpRuntime;
}

- (instancetype)init {
    self = [super init];
    if (self) {
        self.windowWidth = 1200;
        self.windowHeight = 800;
    }
    return self;
}

- (void)applicationDidFinishLaunching:(NSNotification *)notification {
    NSLog(@"PHPN Starting");
    NSLog(@"App Path: %@", self.appPath);
    
    [self setupMenuBar];
    
    _phpRuntime = php_runtime_create([self.appPath UTF8String]);
    
    if (_phpRuntime == NULL) {
        NSAlert *alert = [[NSAlert alloc] init];
        [alert setMessageText:@"Failed to initialize PHP runtime"];
        [alert setInformativeText:@"Make sure PHP is installed and the app path is correct."];
        [alert runModal];
        [NSApp terminate:nil];
        return;
    }
    
    phpn_bridge_init();
    
    PHPNWindow *window = [[PHPNWindow alloc] initWithPHPRuntime:_phpRuntime
                                                         appPath:self.appPath
                                                           width:self.windowWidth
                                                          height:self.windowHeight
                                                           title:self.windowTitle];
    self.mainWindow = window;
    
    NSLog(@"Window created: %@", window);
    NSLog(@"Window frame: %@", NSStringFromRect([window frame]));
    
    [window makeKeyAndOrderFront:nil];
    [window orderFrontRegardless];
    [NSApp activateIgnoringOtherApps:YES];
    
    NSLog(@"Window should be visible now");
    NSLog(@"PHPN Ready");
}

- (void)setupMenuBar {
    NSMenu *mainMenu = [[NSMenu alloc] init];
    
    // App Menu
    NSMenuItem *appMenuItem = [[NSMenuItem alloc] init];
    NSMenu *appMenu = [[NSMenu alloc] init];
    
    [appMenu addItemWithTitle:@"Quit PHPN"
                       action:@selector(terminate:)
                keyEquivalent:@"q"];
    
    [appMenuItem setSubmenu:appMenu];
    [mainMenu addItem:appMenuItem];
    
    // File Menu
    NSMenuItem *fileMenuItem = [[NSMenuItem alloc] init];
    NSMenu *fileMenu = [[NSMenu alloc] initWithTitle:@"File"];
    
    [fileMenu addItemWithTitle:@"Close Window"
                        action:@selector(performClose:)
                 keyEquivalent:@"w"];
    
    [fileMenuItem setSubmenu:fileMenu];
    [mainMenu addItem:fileMenuItem];
    
    // Edit Menu
    NSMenuItem *editMenuItem = [[NSMenuItem alloc] init];
    NSMenu *editMenu = [[NSMenu alloc] initWithTitle:@"Edit"];
    
    [editMenu addItemWithTitle:@"Cut"
                        action:@selector(cut:)
                 keyEquivalent:@"x"];
    
    [editMenu addItemWithTitle:@"Copy"
                        action:@selector(copy:)
                 keyEquivalent:@"c"];
    
    [editMenu addItemWithTitle:@"Paste"
                        action:@selector(paste:)
                 keyEquivalent:@"v"];
    
    [editMenu addItemWithTitle:@"Select All"
                        action:@selector(selectAll:)
                 keyEquivalent:@"a"];
    
    [editMenuItem setSubmenu:editMenu];
    [mainMenu addItem:editMenuItem];
    
    // View Menu
    NSMenuItem *viewMenuItem = [[NSMenuItem alloc] init];
    NSMenu *viewMenu = [[NSMenu alloc] initWithTitle:@"View"];
    
    [viewMenu addItemWithTitle:@"Reload"
                        action:@selector(reload:)
                 keyEquivalent:@"r"];
    
    [viewMenuItem setSubmenu:viewMenu];
    [mainMenu addItem:viewMenuItem];
    
    [NSApp setMainMenu:mainMenu];
}

- (void)reload:(id)sender {
    if (self.mainWindow && [self.mainWindow respondsToSelector:@selector(reload)]) {
        [self.mainWindow performSelector:@selector(reload)];
    }
}

- (void)applicationWillTerminate:(NSNotification *)notification {
    NSLog(@"PHPN Shutting down");
    
    phpn_bridge_cleanup();
    php_runtime_destroy(_phpRuntime);
}

- (BOOL)applicationShouldTerminateAfterLastWindowClosed:(NSApplication *)sender {
    return YES;
}

@end

