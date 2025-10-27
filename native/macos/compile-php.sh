#!/bin/bash

set -e

echo "Compiling PHP with embed SAPI..."

# Check for dependencies
echo "Checking dependencies..."
if ! command -v pkg-config &> /dev/null; then
    echo "ERROR: pkg-config not found. Installing via Homebrew..."
    brew install pkg-config
fi

if ! pkg-config --exists libxml-2.0; then
    echo "ERROR: libxml2 not found. Installing via Homebrew..."
    brew install libxml2
fi

if ! pkg-config --exists sqlite3; then
    echo "Installing sqlite..."
    brew install sqlite
fi

echo "All dependencies satisfied"

# Configuration
PHP_VERSION="8.4.14"
PHP_URL="https://www.php.net/distributions/php-${PHP_VERSION}.tar.gz"
INSTALL_DIR="${PWD}/runtime"
BUILD_DIR="${PWD}/php-build"

# Create directories
mkdir -p "${BUILD_DIR}"
mkdir -p "${INSTALL_DIR}"

cd "${BUILD_DIR}"

# Download PHP if not already downloaded
if [ ! -f "php-${PHP_VERSION}.tar.gz" ]; then
    echo "Downloading PHP ${PHP_VERSION}..."
    curl -O "${PHP_URL}"
fi

# Extract
if [ ! -d "php-${PHP_VERSION}" ]; then
    echo "Extracting PHP..."
    tar -xzf "php-${PHP_VERSION}.tar.gz"
fi

cd "php-${PHP_VERSION}"

# Configure PHP with embed SAPI
echo "Configuring PHP..."

export PKG_CONFIG_PATH="/opt/homebrew/opt/libxml2/lib/pkgconfig:/opt/homebrew/opt/sqlite/lib/pkgconfig:${PKG_CONFIG_PATH}"

export CPPFLAGS="-I/opt/homebrew/opt/libiconv/include -I/opt/homebrew/opt/openssl@3/include"
export LDFLAGS="-L/opt/homebrew/opt/libiconv/lib -L/opt/homebrew/opt/openssl@3/lib"

./configure \
    --prefix="${INSTALL_DIR}" \
    --enable-embed=shared \
    --disable-cgi \
    --disable-cli \
    --with-libxml \
    --enable-mbstring \
    --with-iconv=/opt/homebrew/opt/libiconv \
    --with-openssl \
    --without-pear \
    --without-pcre-jit

echo ""
echo "NOTE: If configure fails, you may need to install additional dependencies:"
echo "    brew install pkg-config libxml2 sqlite openssl@3"

# Build
echo "Building PHP (this may take 5-10 minutes)..."
make -j$(sysctl -n hw.ncpu)

# Install
echo "Installing PHP to ${INSTALL_DIR}..."
make install

echo "PHP compiled successfully!"
echo "libphp installed at: ${INSTALL_DIR}/lib/libphp.so"
echo "Headers installed at: ${INSTALL_DIR}/include/php"
echo ""
echo "To use this PHP, update CMakeLists.txt to use:"
echo "  set(PHP_ROOT \"${INSTALL_DIR}\")"
