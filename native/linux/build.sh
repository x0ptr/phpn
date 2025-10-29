#!/bin/bash
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BUILD_DIR="${SCRIPT_DIR}/build"
RUNTIME_DIR="${SCRIPT_DIR}/runtime"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Building PHPN for Linux"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Check dependencies
echo "Checking dependencies..."
command -v cmake >/dev/null 2>&1 || { echo "cmake is required but not installed. Aborting." >&2; exit 1; }
command -v make >/dev/null 2>&1 || { echo "make is required but not installed. Aborting." >&2; exit 1; }
command -v pkg-config >/dev/null 2>&1 || { echo "pkg-config is required but not installed. Aborting." >&2; exit 1; }

# Check for GTK3 and WebKitGTK
if ! pkg-config --exists gtk4; then
    echo "Error: GTK4 not found. Install with:"
    echo "  Ubuntu/Debian: sudo apt-get install libgtk-4-dev"
    echo "  Fedora: sudo dnf install gtk3-devel"
    echo "  Arch: sudo pacman -S gtk3"
    exit 1
fi

if ! pkg-config --exists webkitgtk-6.0; then
    echo "Error: WebKitGTK 6.0 not found. Install with:"
    echo "  Ubuntu/Debian: sudo apt-get install libwebkitgtk-6.0-dev"
    echo "  Fedora: sudo dnf install webkitgtk6.0-devel"
    echo "  Arch: sudo pacman -S webkitgtk-6.0"
    exit 1
fi

echo "✓ All dependencies found"

# Build PHP if needed
if [ ! -d "$RUNTIME_DIR" ]; then
    echo ""
    echo "━━━ Building PHP Runtime ━━━"
    cd "$SCRIPT_DIR"
    ./build-php.sh
else
    echo ""
    echo "✓ PHP runtime already exists at $RUNTIME_DIR"
    echo "  (Run './build-php.sh' to rebuild)"
fi

# Build native code
echo ""
echo "━━━ Building Native Code ━━━"
mkdir -p "$BUILD_DIR"
cd "$BUILD_DIR"

echo "Running CMake..."
cmake .. -DCMAKE_BUILD_TYPE=Release

echo "Compiling..."
make -j$(nproc)

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✓ Build complete!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Runtime: $BUILD_DIR/phpn-runtime"
echo ""
echo "Run with: ./bin/phpn run examples/test-app/public/index.php"
