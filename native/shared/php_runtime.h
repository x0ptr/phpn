#ifndef PHP_RUNTIME_H
#define PHP_RUNTIME_H

#include <sapi/embed/php_embed.h>

#define MAX_COOKIES 50

typedef struct {
    char *header;
} CookieHeader;

typedef struct {
    void *engine;
    char *document_root;
    int request_count;
    CookieHeader response_cookies[MAX_COOKIES];
    int cookie_count;
} PHPRuntime;

typedef struct {
    const char *method;
    const char *content_type;
    const char *post_data;
    size_t post_data_length;
    const char *cookies;
    const char *user_agent;
    const char *xsrf_token;
} PHPRequest;

PHPRuntime* php_runtime_create(const char *document_root);
char* php_runtime_execute(PHPRuntime *runtime, const char *script_path, const char *request_uri, PHPRequest *request);
void php_runtime_destroy(PHPRuntime *runtime);
CookieHeader* php_runtime_get_response_cookies(PHPRuntime *runtime, int *count);
void php_runtime_clear_response_cookies(PHPRuntime *runtime);

#endif

