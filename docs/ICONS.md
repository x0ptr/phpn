# Creating App Icons

macOS app icons are .icns files. You need a 1024x1024 PNG to start.

## Using macOS tools

Create the icon set:

```bash
mkdir MyIcon.iconset
sips -z 16 16     icon.png --out MyIcon.iconset/icon_16x16.png
sips -z 32 32     icon.png --out MyIcon.iconset/icon_16x16@2x.png
sips -z 32 32     icon.png --out MyIcon.iconset/icon_32x32.png
sips -z 64 64     icon.png --out MyIcon.iconset/icon_32x32@2x.png
sips -z 128 128   icon.png --out MyIcon.iconset/icon_128x128.png
sips -z 256 256   icon.png --out MyIcon.iconset/icon_128x128@2x.png
sips -z 256 256   icon.png --out MyIcon.iconset/icon_256x256.png
sips -z 512 512   icon.png --out MyIcon.iconset/icon_256x256@2x.png
sips -z 512 512   icon.png --out MyIcon.iconset/icon_512x512.png
cp icon.png       MyIcon.iconset/icon_512x512@2x.png
```

Convert to .icns:

```bash
iconutil -c icns MyIcon.iconset
```

Then bundle with:

```bash
./bin/phpn bundle ./my-app "My App" --icon=MyIcon.icns
```

## ImageMagick

```bash
brew install imagemagick
convert icon.png -resize 1024x1024 icon.icns
```

## Online

- iConvert Icons: https://iconverticons.com/online/
- CloudConvert: https://cloudconvert.com/png-to-icns
