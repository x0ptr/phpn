#!/bin/bash
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RUNTIME_DIR="${SCRIPT_DIR}/runtime"
PHP_VERSION="8.4.1"
PHP_SRC_DIR="${SCRIPT_DIR}/php-${PHP_VERSION}"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Building PHP ${PHP_VERSION} with embed SAPI"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Check dependencies
echo "Checking build dependencies..."
MISSING_DEPS=()

for pkg in libssl; do
    if ! pkg-config --exists $pkg 2>/dev/null; then
        MISSING_DEPS+=($pkg)
    fi
done

if [ ${#MISSING_DEPS[@]} -ne 0 ]; then
    echo "Error: Missing dependencies: ${MISSING_DEPS[*]}"
    echo ""
    echo "Install with:"
    echo "  Ubuntu/Debian: sudo apt-get install libxml2-dev libsqlite3-dev libssl-dev build-essential"
    echo "  Fedora: sudo dnf install libxml2-devel sqlite-devel openssl-devel gcc make"
    echo "  Arch: sudo pacman -S libxml2 sqlite openssl base-devel"
    exit 1
fi

# Download PHP if needed
if [ ! -d "$PHP_SRC_DIR" ]; then
    echo ""
    echo "━━━ Downloading PHP ${PHP_VERSION} ━━━"
    cd "$SCRIPT_DIR"
    
    PHP_URL="https://www.php.net/distributions/php-${PHP_VERSION}.tar.gz"
    echo "Downloading from: $PHP_URL"
    
    curl -L -O "$PHP_URL"
    tar -xzf "php-${PHP_VERSION}.tar.gz"
    rm "php-${PHP_VERSION}.tar.gz"
fi

# Create runtime directory
echo ""
echo "━━━ Creating runtime directory ━━━"
mkdir -p "$RUNTIME_DIR"

# Build PHP
echo ""
echo "━━━ Configuring PHP ━━━"
cd "$PHP_SRC_DIR"

./configure \
    --enable-embed=shared \
    --prefix="$RUNTIME_DIR" \
    --with-config-file-path="$RUNTIME_DIR/etc" \
    --enable-mbstring \
    --with-openssl \
    --with-sqlite3 \
    --with-pdo-sqlite \
    --enable-bcmath \
    --enable-calendar \
    --enable-ftp \
    --enable-sockets \
    --with-curl \
    --disable-cgi \
    --disable-cli

echo ""
echo "━━━ Compiling PHP (this may take a while) ━━━"
make -j$(nproc)

echo ""
echo "━━━ Installing PHP ━━━"
make install

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✓ PHP build complete!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "PHP runtime installed at: $RUNTIME_DIR"
echo "PHP version: $(${RUNTIME_DIR}/bin/php --version | head -n 1)"
