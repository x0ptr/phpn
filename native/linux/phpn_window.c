#define _GNU_SOURCE
#include "phpn_window.h"
#include "php_runtime.h"
#include "bridge.h"
#include <stdio.h>
#include <string.h>

struct _PHPNWindow {
    GtkWindow parent;
    WebKitWebView *webview;
    PHPRuntime *php_runtime;
    char *app_path;
    char *app_directory;
    GHashTable *cookie_jar;
};

G_DEFINE_TYPE(PHPNWindow, phpn_window, GTK_TYPE_WINDOW)

// Forward declare callback
static gboolean on_decide_policy(WebKitWebView *webview,
                                 WebKitPolicyDecision *decision,
                                 WebKitPolicyDecisionType type,
                                 PHPNWindow *window);

static void phpn_window_init(PHPNWindow *window) {
    window->cookie_jar = g_hash_table_new_full(g_str_hash, g_str_equal, g_free, g_free);
}

static void phpn_window_class_init(PHPNWindowClass *class) {
    // Setup class
}

PHPNWindow* phpn_window_new(PHPRuntime *runtime, const char *path, 
                            int width, int height, const char *title) {
    PHPNWindow *window = g_object_new(PHPN_TYPE_WINDOW, NULL);
    
    window->php_runtime = runtime;
    window->app_path = g_strdup(path);
    window->app_directory = g_path_get_dirname(path);
    
    gtk_window_set_title(GTK_WINDOW(window), title ? title : "PHPN Application");
    gtk_window_set_default_size(GTK_WINDOW(window), width, height);
    
    // Create WebView with settings for better compatibility
    window->webview = WEBKIT_WEB_VIEW(webkit_web_view_new());
    
    // Configure settings to disable hardware acceleration
    WebKitSettings *settings = webkit_web_view_get_settings(WEBKIT_WEB_VIEW(window->webview));
    webkit_settings_set_enable_developer_extras(settings, TRUE);
    webkit_settings_set_enable_write_console_messages_to_stdout(settings, TRUE);
    webkit_settings_set_hardware_acceleration_policy(settings, 
        WEBKIT_HARDWARE_ACCELERATION_POLICY_NEVER); // Disable hardware acceleration
    
    // GTK4: Use gtk_window_set_child instead of gtk_container_add
    gtk_window_set_child(GTK_WINDOW(window), GTK_WIDGET(window->webview));
    
    // Connect navigation signals
    g_signal_connect(window->webview, "decide-policy",
                     G_CALLBACK(on_decide_policy), window);
    
    // Load initial page
    phpn_window_load_initial_page(window);
    
    return window;
}

static gboolean on_decide_policy(WebKitWebView *webview,
                                 WebKitPolicyDecision *decision,
                                 WebKitPolicyDecisionType type,
                                 PHPNWindow *window) {
    // Handle navigation decisions
    if (type == WEBKIT_POLICY_DECISION_TYPE_NAVIGATION_ACTION) {
        WebKitNavigationPolicyDecision *nav_decision = WEBKIT_NAVIGATION_POLICY_DECISION(decision);
        WebKitNavigationAction *action = webkit_navigation_policy_decision_get_navigation_action(nav_decision);
        
        WebKitURIRequest *request = webkit_navigation_action_get_request(action);
        const char *uri = webkit_uri_request_get_uri(request);
        
        // Allow about:, data:, and file: URIs to load normally
        if (strncmp(uri, "about:", 6) == 0 || 
            strncmp(uri, "data:", 5) == 0 ||
            strncmp(uri, "file:", 5) == 0) {
            webkit_policy_decision_use(decision);
            return TRUE;
        }
        
        // For other navigations, execute PHP
        phpn_window_execute_php(window, uri);
        
        webkit_policy_decision_ignore(decision);
        return TRUE;
    }
    
    return FALSE;
}

void phpn_window_load_initial_page(PHPNWindow *window) {
    g_return_if_fail(PHPN_IS_WINDOW(window));
    
    // Execute PHP for initial load
    PHPRequest request = {
        .method = "GET",
        .content_type = NULL,
        .post_data = NULL,
        .post_data_length = 0,
        .cookies = NULL,
        .user_agent = "PHPN/1.0 (WebKitGTK)",
        .xsrf_token = NULL
    };
    
    char *html = php_runtime_execute(
        window->php_runtime,
        window->app_path,
        "/",
        &request
    );
    
    if (html) {
        fprintf(stderr, "Loading HTML (%zu bytes)\n", strlen(html));
        // Use file:// URI as base so relative URLs work
        char base_uri[1024];
        snprintf(base_uri, sizeof(base_uri), "file://%s", window->app_directory);
        webkit_web_view_load_html(window->webview, html, base_uri);
        free(html);
    } else {
        fprintf(stderr, "PHP execution failed\n");
        const char *error_html = 
            "<html><body><h1>Error: Could not load PHP file</h1></body></html>";
        webkit_web_view_load_html(window->webview, error_html, NULL);
    }
}

void phpn_window_execute_php(PHPNWindow *window, const char *uri) {
    g_return_if_fail(PHPN_IS_WINDOW(window));
    
    // Simple execution for now
    PHPRequest request = {
        .method = "GET",
        .content_type = NULL,
        .post_data = NULL,
        .post_data_length = 0,
        .cookies = NULL,
        .user_agent = "PHPN/1.0 (WebKitGTK)",
        .xsrf_token = NULL
    };
    
    // Execute PHP
    char *html = php_runtime_execute(
        window->php_runtime,
        window->app_path,
        "/",
        &request
    );
    
    if (html) {
        char base_uri[1024];
        snprintf(base_uri, sizeof(base_uri), "file://%s", window->app_directory);
        webkit_web_view_load_html(window->webview, html, base_uri);
        free(html);
    } else {
        const char *error_html = 
            "<html><body><h1>Error: Could not load PHP file</h1></body></html>";
        webkit_web_view_load_html(window->webview, error_html, NULL);
    }
}

void phpn_window_reload(PHPNWindow *window) {
    g_return_if_fail(PHPN_IS_WINDOW(window));
    phpn_window_load_initial_page(window);
}
