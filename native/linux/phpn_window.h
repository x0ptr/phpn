#ifndef PHPN_WINDOW_H
#define PHPN_WINDOW_H

#include <gtk/gtk.h>
#include <webkitgtk-6.0/webkit/webkit.h>
#include "php_runtime.h"

G_BEGIN_DECLS

#define PHPN_TYPE_WINDOW (phpn_window_get_type())
G_DECLARE_FINAL_TYPE(PHPNWindow, phpn_window, PHPN, WINDOW, GtkWindow)

/**
 * phpn_window_new:
 * @runtime: PHP runtime instance
 * @path: Path to the PHP application entry point
 * @width: Initial window width
 * @height: Initial window height
 * @title: Window title (or NULL for default)
 *
 * Creates a new PHPN window.
 *
 * Returns: A new PHPNWindow instance
 */
PHPNWindow* phpn_window_new(PHPRuntime *runtime,
                            const char *path,
                            int width,
                            int height,
                            const char *title);

/**
 * phpn_window_load_initial_page:
 * @window: The PHPNWindow instance
 *
 * Loads the initial PHP page.
 */
void phpn_window_load_initial_page(PHPNWindow *window);

/**
 * phpn_window_execute_php:
 * @window: The PHPNWindow instance
 * @uri: The URI to execute
 *
 * Executes PHP for the given URI and loads the result.
 */
void phpn_window_execute_php(PHPNWindow *window, const char *uri);

/**
 * phpn_window_reload:
 * @window: The PHPNWindow instance
 *
 * Reloads the current page.
 */
void phpn_window_reload(PHPNWindow *window);

G_END_DECLS

#endif /* PHPN_WINDOW_H */
