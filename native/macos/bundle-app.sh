#!/bin/bash

set -e

if [ "$#" -lt 2 ]; then
    echo "Usage: $0 <php-app-directory> <output-name> [--icon=path/to/icon.icns]"
    echo ""
    echo "Example:"
    echo "  $0 ../../examples/laravel-app MyApp"
    echo "  $0 ~/my-php-project \"My Cool App\" --icon=icon.icns"
    exit 1
fi

PHP_APP_DIR="$1"
APP_NAME="$2"
ICON_PATH=""

for arg in "${@:3}"; do
    case $arg in
        --icon=*)
            ICON_PATH="${arg#*=}"
            ;;
    esac
done

PHP_APP_DIR=$(cd "$PHP_APP_DIR" && pwd)
SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)
BUILD_DIR="$SCRIPT_DIR/build"
RUNTIME_BINARY="$BUILD_DIR/phpn-runtime"

if [ ! -f "$RUNTIME_BINARY" ]; then
    echo "Error: Runtime not found. Run ./build.sh first."
    exit 1
fi

APP_BUNDLE="$BUILD_DIR/${APP_NAME}.app"
CONTENTS_DIR="$APP_BUNDLE/Contents"
MACOS_DIR="$CONTENTS_DIR/MacOS"
RESOURCES_DIR="$CONTENTS_DIR/Resources"

echo "Creating app bundle: ${APP_NAME}.app"
rm -rf "$APP_BUNDLE"
mkdir -p "$MACOS_DIR"
mkdir -p "$RESOURCES_DIR"

cp "$RUNTIME_BINARY" "$MACOS_DIR/phpn-runtime"

if [ -d "$SCRIPT_DIR/runtime" ]; then
    echo "Copying PHP runtime..."
    cp -R "$SCRIPT_DIR/runtime" "$RESOURCES_DIR/"
fi

echo "Copying PHP application..."
cp -R "$PHP_APP_DIR" "$RESOURCES_DIR/app"

cat > "$MACOS_DIR/${APP_NAME}" << 'EOF'
#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
RESOURCES_DIR="$(dirname "$DIR")/Resources"

if [ -d "$RESOURCES_DIR/runtime" ]; then
    export DYLD_LIBRARY_PATH="$RESOURCES_DIR/runtime/lib:$DYLD_LIBRARY_PATH"
fi

if [ -f "$RESOURCES_DIR/app/public/index.php" ]; then
    ENTRY_FILE="$RESOURCES_DIR/app/public/index.php"
elif [ -f "$RESOURCES_DIR/app/index.php" ]; then
    ENTRY_FILE="$RESOURCES_DIR/app/index.php"
else
    echo "Error: Could not find entry point (public/index.php or index.php)"
    exit 1
fi

exec "$DIR/phpn-runtime" "$ENTRY_FILE"
EOF

chmod +x "$MACOS_DIR/${APP_NAME}"

cat > "$CONTENTS_DIR/Info.plist" << EOF
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>CFBundleExecutable</key>
    <string>${APP_NAME}</string>
    <key>CFBundleIconFile</key>
    <string>AppIcon</string>
    <key>CFBundleIdentifier</key>
    <string>com.phpn.$(echo "$APP_NAME" | tr '[:upper:] ' '[:lower:]-')</string>
    <key>CFBundleName</key>
    <string>${APP_NAME}</string>
    <key>CFBundlePackageType</key>
    <string>APPL</string>
    <key>CFBundleShortVersionString</key>
    <string>1.0.0</string>
    <key>CFBundleVersion</key>
    <string>1</string>
    <key>LSMinimumSystemVersion</key>
    <string>11.0</string>
    <key>NSHighResolutionCapable</key>
    <true/>
    <key>NSSupportsAutomaticGraphicsSwitching</key>
    <true/>
</dict>
</plist>
EOF

if [ -n "$ICON_PATH" ] && [ -f "$ICON_PATH" ]; then
    cp "$ICON_PATH" "$RESOURCES_DIR/AppIcon.icns"
fi

echo ""
echo "Done: $APP_BUNDLE"
echo ""
echo "Run with: open \"$APP_BUNDLE\""
