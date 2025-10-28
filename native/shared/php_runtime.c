#include "php_runtime.h"
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <libgen.h>
#include <sys/time.h>
#include <time.h>

PHPRuntime *php_runtime_create(const char *document_root)
{
    PHPRuntime *runtime = malloc(sizeof(PHPRuntime));
    runtime->document_root = strdup(document_root);
    runtime->request_count = 0;

    php_embed_init(0, NULL);

    char ini_settings[512];
    snprintf(ini_settings, sizeof(ini_settings),
             "document_root=%s\ninclude_path=%s",
             document_root, document_root);

    char session_path[1024];
    snprintf(session_path, sizeof(session_path), "%s/storage/framework/sessions", document_root);

    mkdir_p(session_path);

    zend_alter_ini_entry_chars("session.save_path", session_path, strlen(session_path),
                               PHP_INI_SYSTEM, PHP_INI_STAGE_RUNTIME);
    zend_alter_ini_entry_chars("session.gc_probability", "1", 1,
                               PHP_INI_SYSTEM, PHP_INI_STAGE_RUNTIME);
    zend_alter_ini_entry_chars("session.gc_divisor", "100", 3,
                               PHP_INI_SYSTEM, PHP_INI_STAGE_RUNTIME);

    return runtime;
}

char *php_runtime_execute(PHPRuntime *runtime, const char *script_path, const char *request_uri, PHPRequest *request)
{
    if (runtime->request_count > 0)
    {
        php_request_shutdown(NULL);
        if (php_request_startup() == FAILURE)
        {
            return strdup("Error starting PHP request");
        }
    }
    runtime->request_count++;

    char *script_copy = strdup(script_path);
    char *script_dir = dirname(script_copy);
    char *script_dir_resolved = strdup(script_dir);

    char *dir_copy = strdup(script_dir_resolved);
    char *dir_name = basename(dir_copy);
    if (strcmp(dir_name, "public") == 0)
    {
        char *parent_copy = strdup(script_dir_resolved);
        char *parent_dir = dirname(parent_copy);
        chdir(parent_dir);
        free(parent_copy);
    }
    else
    {
        chdir(script_dir_resolved);
    }

    free(script_copy);
    free(dir_copy);

    zend_is_auto_global_str(ZEND_STRL("_SESSION"));
    zend_is_auto_global_str(ZEND_STRL("_SERVER"));
    zend_is_auto_global_str(ZEND_STRL("_GET"));
    zend_is_auto_global_str(ZEND_STRL("_POST"));
    zend_is_auto_global_str(ZEND_STRL("_COOKIE"));
    zend_is_auto_global_str(ZEND_STRL("_REQUEST"));

    zval server_var;

    const char *method = (request && request->method) ? request->method : "GET";
    ZVAL_STRING(&server_var, method);
    zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                         "REQUEST_METHOD", sizeof("REQUEST_METHOD") - 1, &server_var);

    ZVAL_STRING(&server_var, request_uri);
    zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                         "REQUEST_URI", sizeof("REQUEST_URI") - 1, &server_var);

    ZVAL_STRING(&server_var, script_path);
    zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                         "SCRIPT_FILENAME", sizeof("SCRIPT_FILENAME") - 1, &server_var);

    ZVAL_STRING(&server_var, script_path);
    zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                         "SCRIPT_NAME", sizeof("SCRIPT_NAME") - 1, &server_var);

    ZVAL_STRING(&server_var, script_dir_resolved);
    zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                         "DOCUMENT_ROOT", sizeof("DOCUMENT_ROOT") - 1, &server_var);

    ZVAL_STRING(&server_var, "/index.php");
    zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                         "PHP_SELF", sizeof("PHP_SELF") - 1, &server_var);

    free(script_dir_resolved);

    ZVAL_STRING(&server_var, "HTTP/1.1");
    zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                         "SERVER_PROTOCOL", sizeof("SERVER_PROTOCOL") - 1, &server_var);

    ZVAL_STRING(&server_var, "localhost");
    zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                         "SERVER_NAME", sizeof("SERVER_NAME") - 1, &server_var);

    ZVAL_STRING(&server_var, "localhost");
    zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                         "HTTP_HOST", sizeof("HTTP_HOST") - 1, &server_var);

    if (request && request->user_agent)
    {
        ZVAL_STRING(&server_var, request->user_agent);
    }
    else
    {
        ZVAL_STRING(&server_var, "PHPN/1.0");
    }
    zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                         "HTTP_USER_AGENT", sizeof("HTTP_USER_AGENT") - 1, &server_var);

    ZVAL_STRING(&server_var, "127.0.0.1");
    zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                         "REMOTE_ADDR", sizeof("REMOTE_ADDR") - 1, &server_var);

    ZVAL_LONG(&server_var, (zend_long)time(NULL));
    zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                         "REQUEST_TIME", sizeof("REQUEST_TIME") - 1, &server_var);

    struct timeval tv;
    gettimeofday(&tv, NULL);
    double request_time_float = tv.tv_sec + (tv.tv_usec / 1000000.0);
    ZVAL_DOUBLE(&server_var, request_time_float);
    zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                         "REQUEST_TIME_FLOAT", sizeof("REQUEST_TIME_FLOAT") - 1, &server_var);

    ZVAL_STRING(&server_var, "CGI/1.1");
    zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                         "GATEWAY_INTERFACE", sizeof("GATEWAY_INTERFACE") - 1, &server_var);

    ZVAL_STRING(&server_var, "80");
    zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                         "SERVER_PORT", sizeof("SERVER_PORT") - 1, &server_var);

    const char *query_start = strchr(request_uri, '?');
    if (query_start != NULL)
    {
        query_start++;
        ZVAL_STRING(&server_var, query_start);
        zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                             "QUERY_STRING", sizeof("QUERY_STRING") - 1, &server_var);

        char *query_copy = strdup(query_start);
        char *saveptr;
        char *pair = strtok_r(query_copy, "&", &saveptr);
        while (pair != NULL)
        {
            char *equals = strchr(pair, '=');
            if (equals != NULL)
            {
                *equals = '\0';
                char *key = pair;
                char *value = equals + 1;

                char decoded_value[1024];
                int j = 0;
                for (int i = 0; value[i] && j < 1023; i++)
                {
                    if (value[i] == '%' && value[i + 1] && value[i + 2])
                    {
                        int hex;
                        sscanf(&value[i + 1], "%2x", &hex);
                        decoded_value[j++] = (char)hex;
                        i += 2;
                    }
                    else if (value[i] == '+')
                    {
                        decoded_value[j++] = ' ';
                    }
                    else
                    {
                        decoded_value[j++] = value[i];
                    }
                }
                decoded_value[j] = '\0';

                zval get_var;
                ZVAL_STRING(&get_var, decoded_value);
                zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_GET]),
                                     key, strlen(key), &get_var);
            }
            pair = strtok_r(NULL, "&", &saveptr);
        }
        free(query_copy);
    }
    else
    {
        ZVAL_STRING(&server_var, "");
        zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                             "QUERY_STRING", sizeof("QUERY_STRING") - 1, &server_var);
    }

    if (request && request->post_data && request->post_data_length > 0)
    {
        if (request->content_type)
        {
            ZVAL_STRING(&server_var, request->content_type);
            zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                                 "CONTENT_TYPE", sizeof("CONTENT_TYPE") - 1, &server_var);
        }

        ZVAL_LONG(&server_var, request->post_data_length);
        zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                             "CONTENT_LENGTH", sizeof("CONTENT_LENGTH") - 1, &server_var);

        if (!request->content_type || strstr(request->content_type, "application/x-www-form-urlencoded"))
        {
            char *post_copy = strndup(request->post_data, request->post_data_length);
            char *saveptr;
            char *pair = strtok_r(post_copy, "&", &saveptr);
            while (pair != NULL)
            {
                char *equals = strchr(pair, '=');
                if (equals != NULL)
                {
                    *equals = '\0';
                    char *key = pair;
                    char *value = equals + 1;

                    zval post_var;
                    ZVAL_STRING(&post_var, value);
                    zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_POST]),
                                         key, strlen(key), &post_var);
                }
                pair = strtok_r(NULL, "&", &saveptr);
            }
            free(post_copy);
        }
    }

    if (request && request->cookies)
    {
        ZVAL_STRING(&server_var, request->cookies);
        zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]),
                             "HTTP_COOKIE", sizeof("HTTP_COOKIE") - 1, &server_var);

        char *cookie_copy = strdup(request->cookies);
        char *saveptr;
        char *pair = strtok_r(cookie_copy, ";", &saveptr);
        while (pair != NULL)
        {
            while (*pair == ' ')
                pair++;

            char *equals = strchr(pair, '=');
            if (equals != NULL)
            {
                *equals = '\0';
                char *key = pair;
                char *value = equals + 1;

                zval cookie_var;
                ZVAL_STRING(&cookie_var, value);
                zend_hash_str_update(Z_ARRVAL(PG(http_globals)[TRACK_VARS_COOKIE]),
                                     key, strlen(key), &cookie_var);
            }
            pair = strtok_r(NULL, ";", &saveptr);
        }
        free(cookie_copy);
    }

    php_output_start_default();

    zend_file_handle file_handle;
    zend_stream_init_filename(&file_handle, script_path);

    int result = php_execute_script(&file_handle);
    if (result == FAILURE)
    {
        php_output_end();
        return strdup("Error executing PHP script");
    }

    zval return_value;
    if (php_output_get_contents(&return_value) == FAILURE)
    {
        php_output_end();
        return strdup("");
    }

    char *output = strdup(Z_STRVAL(return_value));
    zval_ptr_dtor(&return_value);
    php_output_end();

    // Note: We don't shutdown here because we want to keep the runtime alive
    // for subsequent requests. Shutdown happens at the START of the next request.

    return output;
}

void php_runtime_destroy(PHPRuntime *runtime)
{
    php_embed_shutdown();
    free(runtime->document_root);
    free(runtime);
}
