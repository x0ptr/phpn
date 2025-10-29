#ifndef PHPN_APP_H
#define PHPN_APP_H

#include <gtk/gtk.h>
#include "php_runtime.h"

G_BEGIN_DECLS

#define PHPN_TYPE_APP (phpn_app_get_type())
G_DECLARE_FINAL_TYPE(PHPNApp, phpn_app, PHPN, APP, GtkApplication)

/**
 * phpn_app_new:
 *
 * Creates a new PHPN application.
 *
 * Returns: A new PHPNApp instance
 */
PHPNApp* phpn_app_new(void);

/**
 * phpn_app_set_path:
 * @app: The PHPNApp instance
 * @path: Path to the PHP application
 */
void phpn_app_set_path(PHPNApp *app, const char *path);

/**
 * phpn_app_set_width:
 * @app: The PHPNApp instance
 * @width: Window width
 */
void phpn_app_set_width(PHPNApp *app, int width);

/**
 * phpn_app_set_height:
 * @app: The PHPNApp instance
 * @height: Window height
 */
void phpn_app_set_height(PHPNApp *app, int height);

/**
 * phpn_app_set_title:
 * @app: The PHPNApp instance
 * @title: Window title
 */
void phpn_app_set_title(PHPNApp *app, const char *title);

G_END_DECLS

#endif /* PHPN_APP_H */
