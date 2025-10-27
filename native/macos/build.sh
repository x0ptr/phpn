#!/bin/bash
set -e

SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)

echo "Building PHPN for macOS..."

# Check if custom PHP runtime exists, if not compile it
if [ ! -f "$SCRIPT_DIR/runtime/bin/php" ]; then
    echo "Custom PHP runtime not found. Compiling PHP with embed SAPI..."
    echo "This will take about 5-10 minutes..."
    echo ""
    
    if [ ! -f "$SCRIPT_DIR/compile-php.sh" ]; then
        echo "ERROR: compile-php.sh not found"
        exit 1
    fi
    
    "$SCRIPT_DIR/compile-php.sh"
fi

rm -rf build dist
mkdir -p build dist

cd build

cmake .. \
    -DCMAKE_BUILD_TYPE=Release \
    -DCMAKE_OSX_ARCHITECTURES="arm64;x86_64" \
    -DCMAKE_OSX_DEPLOYMENT_TARGET=11.0

cmake --build . --config Release -j$(sysctl -n hw.ncpu)

echo ""
echo "Build complete!"
echo ""
echo "Output:"
echo "  CLI Executable: build/phpn-runtime"
echo ""

cp phpn-runtime ../dist/phpn-runtime

chmod +x ../dist/phpn-runtime

echo "Usage:"
echo "  ./dist/phpn-runtime /path/to/php/app"
echo ""
