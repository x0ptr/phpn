#ifndef PHP_RUNTIME_H
#define PHP_RUNTIME_H

#include <sapi/embed/php_embed.h>

typedef struct {
    void *engine;
    char *document_root;
    int request_count;
} PHPRuntime;

typedef struct {
    const char *method;
    const char *content_type;
    const char *post_data;
    size_t post_data_length;
    const char *cookies;
    const char *user_agent;
} PHPRequest;

PHPRuntime* php_runtime_create(const char *document_root);
char* php_runtime_execute(PHPRuntime *runtime, const char *script_path, const char *request_uri, PHPRequest *request);
void php_runtime_destroy(PHPRuntime *runtime);

#endif

