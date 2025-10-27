cmake_minimum_required(VERSION 3.15)
project(phpn-macos OBJC C)

set(CMAKE_OSX_DEPLOYMENT_TARGET "11.0")
set(CMAKE_OSX_ARCHITECTURES "arm64")

# Find PHP - check for custom runtime first, then system PHP
if(EXISTS "${CMAKE_SOURCE_DIR}/runtime/bin/php-config")
    set(PHP_CONFIG "${CMAKE_SOURCE_DIR}/runtime/bin/php-config")
    set(PHP_LIB_DIR "${CMAKE_SOURCE_DIR}/runtime/lib")
    message(STATUS "Using custom PHP runtime")
else()
    set(PHP_CONFIG "php-config")
    execute_process(
        COMMAND ${PHP_CONFIG} --prefix
        OUTPUT_VARIABLE PHP_PREFIX
        OUTPUT_STRIP_TRAILING_WHITESPACE
    )
    set(PHP_LIB_DIR "${PHP_PREFIX}/lib")
    message(STATUS "Using system PHP")
endif()

execute_process(
    COMMAND ${PHP_CONFIG} --includes
    OUTPUT_VARIABLE PHP_INCLUDES
    OUTPUT_STRIP_TRAILING_WHITESPACE
)
string(REPLACE "-I" "" PHP_INCLUDE_DIRS "${PHP_INCLUDES}")
string(REPLACE " " ";" PHP_INCLUDE_DIRS "${PHP_INCLUDE_DIRS}")

# Find libphp
find_library(PHP_LIBRARY
    NAMES php libphp php8 libphp.dylib
    PATHS ${PHP_LIB_DIR}
    NO_DEFAULT_PATH
)

if(NOT PHP_LIBRARY)
    message(FATAL_ERROR "Could not find libphp in ${PHP_LIB_DIR}")
endif()

message(STATUS "Found PHP library: ${PHP_LIBRARY}")

# macOS Frameworks
find_library(COCOA_LIBRARY Cocoa REQUIRED)
find_library(WEBKIT_LIBRARY WebKit REQUIRED)

# Include directories
include_directories(
    ${CMAKE_SOURCE_DIR}/../shared
    ${PHP_INCLUDE_DIRS}
)

# Shared library
add_library(phpn-shared STATIC
    ../shared/php_runtime.c
    ../shared/bridge.c
)

set_target_properties(phpn-shared PROPERTIES
    LINKER_LANGUAGE C
)

target_link_libraries(phpn-shared
    ${PHP_LIBRARY}
)

# Main executable
add_executable(phpn-runtime
    main.m
    PHPNAppDelegate.m
    PHPNWindow.m
)

target_link_libraries(phpn-runtime
    phpn-shared
    ${COCOA_LIBRARY}
    ${WEBKIT_LIBRARY}
)

# Set properties
set_target_properties(phpn-runtime PROPERTIES
    MACOSX_BUNDLE FALSE
    OUTPUT_NAME "phpn-runtime"
)

