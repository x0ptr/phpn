#define _GNU_SOURCE
#include <gtk/gtk.h>
#include "phpn_app.h"

int main(int argc, char *argv[]) {
	PHPNApp *app = phpn_app_new();

	if (argc > 1) {
		phpn_app_set_path(app, argv[1]);
	}

	const char *width_env = g_getenv("PHPN_WINDOW_WIDTH");
	const char *height_env = g_getenv("PHPN_WINDOW_HEIGHT");
	const char *title_env = g_getenv("PHPN_WINDOW_TITLE");

	if (width_env) phpn_app_set_width(app, atoi(width_env));
	if (height_env) phpn_app_set_width(app, atoi(height_env));
	if (title_env) phpn_app_set_width(app, atoi(title_env));

	fprintf(stderr, "Calling g_application_run...\n");

	char *gtk_argv[] = { argv[0] };
	int status = g_application_run(G_APPLICATION(app), 1, gtk_argv);
	fprintf(stderr, "g_application_run returned: %d\n", status);

	g_object_unref(app);

	return status;
}
