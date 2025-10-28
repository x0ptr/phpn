# PHPN (WIP - So many stuff not working right now. Sessions etc.)

PHP Native - Build desktop apps with PHP and native WebKit.

https://github.com/user-attachments/assets/fbf6d3f2-e1f4-487d-adfb-f104e6004d1f

## Why?

- Smaller bundle size (~50-100MB vs Electron's ~150MB+)
- Uses native WebKit instead of bundling Chromium
- Write desktop apps in PHP like you'd write web apps
- Works with Laravel, Symfony, or any PHP code
- Proper $_GET, $_POST, $_COOKIE support

## Requirements


- PHP 8.1+
- macOS 11+ (Windows/Linux support coming)
- CMake 3.15+
- Xcode CLI tools

## Tested Frameworks

Currently tested and working with:
- **Laravel 11** - Not fully working. WIP
- Plain PHP - Works with any standard PHP application

Other frameworks like Symfony, CodeIgniter, and Slim should work but haven't been tested yet. Let me know if you try one!

## Quick Start

Clone and build:

```bash
git clone https://github.com/phpn/phpn.git
cd phpn
./bin/phpn build
```

Run the example:

```bash
./bin/phpn run examples/test-app/public/index.php
```

## Usage with Laravel

Install:

```bash
composer require --dev phpn/phpn
```

Run (downloads prebuilt runtime automatically on first use):

```bash
php artisan desktop:serve
```

Bundle as .app:

```bash
php artisan desktop:bundle "My App"
php artisan desktop:bundle --icon=resources/icon.icns
```

Or just use the binary:

```bash
vendor/bin/phpn run public/index.php --width=1200 --height=800
vendor/bin/phpn bundle . "My App" --icon=icon.icns
```

## Basic Example

```php
<?php
// app.php
$name = $_GET['name'] ?? 'World';
?>
<!DOCTYPE html>
<html>
<body>
    <h1>Hello <?= htmlspecialchars($name) ?>!</h1>
    <p>PHP <?= phpversion() ?></p>
</body>
</html>
```

```bash
./bin/phpn run app.php
```

## How It Works

PHPN embeds PHP's SAPI library and runs your PHP code, then displays the output in a native WebKit view. Request data ($_GET, $_POST, etc.) gets populated from the URL bar, so your existing routing works.

The native/macos folder has the Objective-C code for the window. The shared folder has the C code that talks to PHP. When you run a file, it spins up PHP, executes your code, and renders the HTML output.

## Configuration

Set defaults in config/phpn.php or pass CLI options:

```bash
./bin/phpn run app.php --width=1600 --height=1000 --title="My App"
```

## Keyboard Shortcuts

- Cmd+Q: Quit
- Cmd+W: Close window
- Cmd+R: Reload
- Cmd+C/V/X/A: Copy/Paste/Cut/Select All

## Bundling as .app

Create a standalone macOS app:

```bash
# Using phpn
./bin/phpn bundle examples/test-app "My App Name"

# With Laravel
php artisan desktop:bundle "My App"
php artisan desktop:bundle --icon=resources/icon.icns
```

With a custom icon:

```bash
./bin/phpn bundle ./my-app "My App" --icon=icon.icns
```

The bundled app will be in `native/macos/build/My App.app` (or `vendor/phpn/phpn/native/macos/build/` for Laravel projects). You can:
- Double-click to run it
- Move it to Applications folder
- Distribute it (zip or create a DMG)

The bundle includes the PHP runtime and your entire app, so users don't need PHP installed.

### Reducing Bundle Size

The bundling process automatically removes build artifacts from `vendor/x0ptr/phpn/`. For even smaller bundles:

```bash
composer install --no-dev
php artisan desktop:bundle "My App"
composer install 
```

You can also exclude unnecessary files by creating a custom bundle script or using `.gitattributes` export-ignore.

## Building

```bash
./bin/phpn build
```

This compiles PHP with the embed SAPI, builds the native macOS app, and puts everything in native/macos/build/.

**Note:** On first use, PHPN automatically downloads prebuilt binaries from GitHub releases. Building from source is only needed if you're developing PHPN itself or if no prebuilt binary is available for your platform.

## Creating Releases

To create a release with prebuilt binaries:

```bash
git tag v1.0.0
git push origin v1.0.0
```

GitHub Actions will automatically build and upload binaries for macOS (and Linux when implemented).

Or manually create archives:

```bash
cd native
./create-release.sh 1.0.0
```

Then upload the archives in `native/dist/` to the GitHub release.

## Limitations

- macOS only right now (planning Windows/Linux)
- No system dialogs yet (file open/save)
- JavaScript bridge is basic
- Single window only

## Why Not Just Use Electron?

Electron bundles an entire Chromium browser. PHPN uses the WebKit that's already on your Mac. A typical bundle is 50-100MB (mostly your app + PHP runtime + dependencies) instead of 150MB+ with Electron.

Also I like PHP.

## License

MIT - See [LICENSE](LICENSE) file for details.

- [ ] Menu bar integration
- [ ] Windows support (Win32/WebView2)
- [ ] Linux support (GTK/WebKitGTK)
- [ ] Composer package for easy installation

## Requirements

### Development
- macOS 11.0+
- Xcode Command Line Tools
- CMake 3.15+
- pkg-config, libxml2, sqlite (via Homebrew)

### Runtime
- macOS 11.0+
- Bundled PHP runtime (included in built app)

## License

MIT

## Inspiration

- [Electron](https://www.electronjs.org/) - Desktop apps with web technologies
- [Tauri](https://tauri.app/) - Lightweight Electron alternative (Rust)
- [Neutralinojs](https://neutralino.js.org/) - Lightweight cross-platform apps
- [PHP Desktop](https://github.com/cztomczak/phpdesktop) - PHP desktop apps (older)

## Contributing

Contributions welcome! This is an early-stage project.

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request
