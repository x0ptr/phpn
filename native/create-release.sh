#!/bin/bash

set -e

if [ "$#" -ne 1 ]; then
    echo "Usage: $0 <version>"
    echo "Example: $0 1.0.0"
    exit 1
fi

VERSION="$1"
SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)
DIST_DIR="$SCRIPT_DIR/dist"

rm -rf "$DIST_DIR"
mkdir -p "$DIST_DIR"

echo "Creating release archives for PHPN v${VERSION}"
echo ""

PLATFORM=$(uname -s)
ARCH=$(uname -m)

if [ "$PLATFORM" = "Darwin" ]; then
    echo "Building macOS release..."
    
    cd "$SCRIPT_DIR/macos"
    ./build.sh
    
    RELEASE_DIR="$DIST_DIR/phpn-macos-${ARCH}"
    mkdir -p "$RELEASE_DIR"
    
    cp -R build "$RELEASE_DIR/"
    cp -R runtime "$RELEASE_DIR/"
    
    cd "$DIST_DIR"
    tar -czf "phpn-macos-${ARCH}.tar.gz" "phpn-macos-${ARCH}"
    rm -rf "phpn-macos-${ARCH}"
    
    echo "✓ Created: phpn-macos-${ARCH}.tar.gz"
    
elif [ "$PLATFORM" = "Linux" ]; then
    echo "Building Linux release..."
    
    cd "$SCRIPT_DIR/linux"
    ./build.sh
    
    RELEASE_DIR="$DIST_DIR/phpn-linux-${ARCH}"
    mkdir -p "$RELEASE_DIR"
    
    cp -R build "$RELEASE_DIR/"
    cp -R runtime "$RELEASE_DIR/"
    
    cd "$DIST_DIR"
    tar -czf "phpn-linux-${ARCH}.tar.gz" "phpn-linux-${ARCH}"
    rm -rf "phpn-linux-${ARCH}"
    
    echo "✓ Created: phpn-linux-${ARCH}.tar.gz"
    
else
    echo "Unsupported platform: $PLATFORM"
    exit 1
fi

echo ""
echo "Release archives created in: $DIST_DIR"
echo ""
echo "Upload these to GitHub release v${VERSION}:"
ls -lh "$DIST_DIR"/*.tar.gz "$DIST_DIR"/*.zip 2>/dev/null || true
