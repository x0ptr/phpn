#!/bin/bash
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BUILD_DIR="${SCRIPT_DIR}/build"
RUNTIME_DIR="${SCRIPT_DIR}/runtime"

# Parse arguments
APP_DIR="${1:-.}"
APP_NAME="${2:-PHPN App}"
ICON_PATH="${3}"

if [ -z "$APP_DIR" ] || [ -z "$APP_NAME" ]; then
    echo "Usage: $0 <app-directory> <app-name> [icon-path]"
    echo ""
    echo "Example:"
    echo "  $0 examples/test-app \"My App\" icon.png"
    exit 1
fi

APP_DIR="$(cd "$APP_DIR" && pwd)"
BUNDLE_DIR="${BUILD_DIR}/${APP_NAME}"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Bundling: ${APP_NAME}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Check if runtime exists
if [ ! -f "${BUILD_DIR}/phpn-runtime" ]; then
    echo "Error: phpn-runtime not found. Build first with ./build.sh"
    exit 1
fi

# Create bundle structure
echo "Creating bundle structure..."
rm -rf "$BUNDLE_DIR"
mkdir -p "$BUNDLE_DIR"/{bin,lib,app,share/icons}

# Copy runtime
echo "Copying runtime..."
cp "${BUILD_DIR}/phpn-runtime" "${BUNDLE_DIR}/bin/"

# Copy PHP runtime
echo "Copying PHP runtime..."
cp -r "${RUNTIME_DIR}"/* "${BUNDLE_DIR}/"

# Copy application
echo "Copying application files..."
rsync -av --exclude='node_modules' --exclude='.git' --exclude='vendor/x0ptr/phpn' \
    "$APP_DIR/" "${BUNDLE_DIR}/app/"

# Copy icon if provided
if [ -n "$ICON_PATH" ] && [ -f "$ICON_PATH" ]; then
    cp "$ICON_PATH" "${BUNDLE_DIR}/share/icons/app-icon.png"
fi

# Create launcher script
echo "Creating launcher..."
cat > "${BUNDLE_DIR}/run.sh" << 'EOF'
#!/bin/bash
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
export LD_LIBRARY_PATH="${SCRIPT_DIR}/lib:${LD_LIBRARY_PATH}"
cd "${SCRIPT_DIR}/app"
exec "${SCRIPT_DIR}/bin/phpn-runtime" "${SCRIPT_DIR}/app/public/index.php" "$@"
EOF
chmod +x "${BUNDLE_DIR}/run.sh"

# Create .desktop file
echo "Creating desktop entry..."
cat > "${BUNDLE_DIR}/${APP_NAME}.desktop" << EOF
[Desktop Entry]
Version=1.0
Type=Application
Name=${APP_NAME}
Comment=PHPN Application
Exec=run.sh
Icon=share/icons/app-icon.png
Terminal=false
Categories=Utility;
EOF

# Calculate bundle size
BUNDLE_SIZE=$(du -sh "$BUNDLE_DIR" | cut -f1)

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✓ Bundle complete!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Location: $BUNDLE_DIR"
echo "Size: $BUNDLE_SIZE"
echo ""
echo "Run with: ${BUNDLE_DIR}/run.sh"
echo "Or create tarball: tar -czf \"${APP_NAME}.tar.gz\" -C \"${BUILD_DIR}\" \"${APP_NAME}\""
