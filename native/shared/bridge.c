#include "bridge.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

typedef struct {
    char *name;
    void (*handler)(const char *);
} BridgeHandler;

static BridgeHandler *handlers = NULL;
static int handler_count = 0;

void phpn_bridge_init(void) {
    handlers = NULL;
    handler_count = 0;
}

void phpn_bridge_register_handler(const char *name, void (*handler)(const char *)) {
    handlers = realloc(handlers, sizeof(BridgeHandler) * (handler_count + 1));
    handlers[handler_count].name = strdup(name);
    handlers[handler_count].handler = handler;
    handler_count++;
}

char* phpn_bridge_call(const char *function_name, const char *json_args) {
    for (int i = 0; i < handler_count; i++) {
        if (strcmp(handlers[i].name, function_name) == 0) {
            handlers[i].handler(json_args);
            return strdup("{\"success\":true}");
        }
    }
    
    return strdup("{\"error\":\"Function not found\"}");
}

void phpn_bridge_cleanup(void) {
    for (int i = 0; i < handler_count; i++) {
        free(handlers[i].name);
    }
    free(handlers);
    handlers = NULL;
    handler_count = 0;
}

