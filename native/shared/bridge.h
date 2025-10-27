#ifndef PHPN_BRIDGE_H
#define PHPN_BRIDGE_H

void phpn_bridge_init(void);
void phpn_bridge_register_handler(const char *name, void (*handler)(const char *));
char* phpn_bridge_call(const char *function_name, const char *json_args);
void phpn_bridge_cleanup(void);

#endif
