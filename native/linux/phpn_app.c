#define _GNU_SOURCE
#include "phpn_app.h"
#include "phpn_window.h"
#include "php_runtime.h"
#include <stdio.h>
#include <libgen.h>
#include <string.h>

struct _PHPNApp {
    GtkApplication parent;
    PHPRuntime *php_runtime;
    PHPNWindow *window;
    char *app_path;
    int window_width;
    int window_height;
    char *window_title;
};

G_DEFINE_TYPE(PHPNApp, phpn_app, GTK_TYPE_APPLICATION)

static void phpn_app_activate(GApplication *app);
static void phpn_app_dispose(GObject *object);

static void phpn_app_init(PHPNApp *app) {
    app->php_runtime = NULL;
    app->window = NULL;
    app->app_path = NULL;
    app->window_width = 800;
    app->window_height = 600;
    app->window_title = NULL;
}

static void phpn_app_class_init(PHPNAppClass *class) {
    GObjectClass *object_class = G_OBJECT_CLASS(class);
    GApplicationClass *app_class = G_APPLICATION_CLASS(class);
    
    object_class->dispose = phpn_app_dispose;
    app_class->activate = phpn_app_activate;
}

static void phpn_app_dispose(GObject *object) {
    PHPNApp *app = PHPN_APP(object);
    
    if (app->php_runtime) {
        php_runtime_destroy(app->php_runtime);
        app->php_runtime = NULL;
    }
    
    g_free(app->app_path);
    g_free(app->window_title);
    
    G_OBJECT_CLASS(phpn_app_parent_class)->dispose(object);
}

static void phpn_app_activate(GApplication *application) {
    PHPNApp *app = PHPN_APP(application);
    
    fprintf(stderr, "phpn_app_activate called\n");
    fprintf(stderr, "App path: %s\n", app->app_path ? app->app_path : "(null)");
    
    // Initialize PHP runtime - get directory from file path
    if (!app->php_runtime) {
        // Extract directory from file path
        char *path_copy = strdup(app->app_path ? app->app_path : ".");
        char *doc_root = dirname(path_copy);
        
        fprintf(stderr, "Creating PHP runtime with doc_root: %s\n", doc_root);
        
        app->php_runtime = php_runtime_create(doc_root);
        free(path_copy);
        
        if (!app->php_runtime) {
            g_critical("Failed to initialize PHP runtime");
            return;
        }
        fprintf(stderr, "PHP runtime created successfully\n");
    }
    
    // Create window
    fprintf(stderr, "Creating window: %dx%d\n", app->window_width, app->window_height);
    app->window = phpn_window_new(
        app->php_runtime,
        app->app_path ? app->app_path : ".",
        app->window_width,
        app->window_height,
        app->window_title
    );
    
    if (!app->window) {
        g_critical("Failed to create window");
        return;
    }
    fprintf(stderr, "Window created successfully\n");
    
    gtk_window_set_application(GTK_WINDOW(app->window), GTK_APPLICATION(app));
    fprintf(stderr, "Presenting window...\n");
    gtk_window_present(GTK_WINDOW(app->window));
    fprintf(stderr, "Window presented\n");
}

PHPNApp* phpn_app_new(void) {
    return g_object_new(PHPN_TYPE_APP,
                       "application-id", "com.phpn.app",
                       "flags", G_APPLICATION_DEFAULT_FLAGS,
                       NULL);
}

void phpn_app_set_path(PHPNApp *app, const char *path) {
    g_return_if_fail(PHPN_IS_APP(app));
    
    g_free(app->app_path);
    app->app_path = g_strdup(path);
}

void phpn_app_set_width(PHPNApp *app, int width) {
    g_return_if_fail(PHPN_IS_APP(app));
    app->window_width = width;
}

void phpn_app_set_height(PHPNApp *app, int height) {
    g_return_if_fail(PHPN_IS_APP(app));
    app->window_height = height;
}

void phpn_app_set_title(PHPNApp *app, const char *title) {
    g_return_if_fail(PHPN_IS_APP(app));
    
    g_free(app->window_title);
    app->window_title = g_strdup(title);
}
