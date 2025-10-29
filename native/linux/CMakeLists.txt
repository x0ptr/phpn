cmake_minimum_required(VERSION 3.15)
project(phpn-linux C)

if(EXISTS "${CMAKE_SOURCE_DIR}/runtime/bin/php-config")
	set(PHP_CONFIG "${CMAKE_SOURCE_DIR}/runtime/bin/php-config")
	set(PHP_LIB_DIR "${CMAKE_SOURCE_DIR}/runtime/lib")
else()
	set(PHP_CONFIG "php-config")
	execute_process(
		COMMAND ${PHP_CONFIG} --prefix
		OUTPUT_VARIABLE PHP_PREFIX
		OUTPUT_STRIP_TRAILING_WHITESPACE
	)
	set(PHP_LIB_DIR "${PHP_PREFIX}/lib")
endif()

execute_process(
	COMMAND ${PHP_CONFIG} --includes
	OUTPUT_VARIABLE PHP_INCLUDES
	OUTPUT_STRIP_TRAILING_WHITESPACE
)
string(REPLACE "-I" "" PHP_INCLUDE_DIRS "${PHP_INCLUDES}")
string(REPLACE " " ";" PHP_INCLUDE_DIRS "${PHP_INCLUDE_DIRS}")

find_library(PHP_LIBRARY
	NAMES libphp libphp8 libphp.so
	PATHS ${PHP_LIB_DIR}
	NO_DEFAULT_PATH
)

if(NOT PHP_LIBRARY)
	string(REPLACE "/lib" "/lib64" PHP_LIB64_DIR "${PHP_LIB_DIR}")
	find_library(PHP_LIBRARY
		NAMES libphp.so libphp8.so libphp
		PATHS ${PHP_LIB64_DIR}
		NO_DEFAULT_PATH
	)
	if(PHP_LIBRARY)
		set(PHP_LIB_DIR ${PHP_LIB64_DIR})
	endif()
endif()

if(NOT PHP_LIBRARY)
	message(FATAL_ERROR "Could not find libphp in ${PHP_LIB_DIR}")
endif()

message(STATUS "Found PHP library: ${PHP_LIBRARY}")

find_package(PkgConfig REQUIRED)
pkg_check_modules(GTK3 REQUIRED gtk+-3.0)
pkg_check_modules(WEBKIT REQUIRED webkitgtk-6.0)

include_directories(
	${CMAKE_SOURCE_DIR}/../shared
	${PHP_INCLUDE_DIRS}
	${GTK4_INCLUDE_DIRS}
	${WEBKIT_INCLUDE_DIRS}
)

add_library(phpn-shared STATIC
	../shared/php_runtime.c
)

target_link_libraries(phpn-shared
	${PHP_LIBRARY}
)

add_executable(phpn-runtime
	main.c
	phpn_app.c
	phpn_window.c
)

target_link_libraries(phpn-runtime
	phpn-shared
	${GTK4_LIBRARIES}
	${WEBKIT_LIBRARIES}
)

