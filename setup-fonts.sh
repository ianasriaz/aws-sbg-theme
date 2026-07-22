#!/bin/bash

# AWS SBG Theme - Font Setup Helper Script
# This script helps set up the fonts folder and provides guidance

THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
FONTS_DIR="$THEME_DIR/fonts"

echo "=================================="
echo "AWS SBG Theme - Font Setup Helper"
echo "=================================="
echo ""

# Check if fonts folder exists
if [ ! -d "$FONTS_DIR" ]; then
    echo "📁 Creating fonts folder..."
    mkdir -p "$FONTS_DIR"
    echo "✅ Fonts folder created at: $FONTS_DIR"
else
    echo "📁 Fonts folder already exists"
fi

echo ""
echo "📋 Font Setup Instructions:"
echo "=================================="
echo ""
echo "1. Obtain Amazon Ember fonts (.woff2 format)"
echo "   - From AWS Design System"
echo "   - Or convert from .ttf using: https://convertio.co/ttf-woff2/"
echo ""
echo "2. Place these files in: $FONTS_DIR/"
echo "   Required files:"
echo "   - amazon-ember-regular.woff2"
echo "   - amazon-ember-medium.woff2"
echo "   - amazon-ember-semibold.woff2"
echo "   - amazon-ember-bold.woff2"
echo ""
echo "3. Set file permissions (if needed):"
echo "   chmod 644 $FONTS_DIR/*.woff2"
echo ""
echo "4. Clear WordPress cache"
echo ""
echo "5. Test in browser - fonts should load automatically"
echo ""

# Check for existing font files
echo "🔍 Current font files in $FONTS_DIR:"
echo "---"
if [ -z "$(ls -A $FONTS_DIR 2>/dev/null)" ]; then
    echo "   (No fonts found - please add .woff2 files)"
else
    ls -lh "$FONTS_DIR"/*.woff2 2>/dev/null || echo "   (No .woff2 files found)"
fi

echo ""
echo "📖 For more information, see: FONTS_IMPLEMENTATION.md"
echo ""
