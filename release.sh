#!/bin/bash

# SiteSphere Release Packager
# Creates a distributable ZIP file

set -e

echo "🚀 SiteSphere Release Packager"
echo "================================"

# Clean up previous release
rm -rf release-build
rm -f sitesphere.zip

# Create build directory
mkdir -p release-build

echo "📦 Copying project files..."

# Copy essential files
cp -r app release-build/
cp -r bootstrap release-build/
cp -r config release-build/
cp -r database release-build/
cp -r lang release-build/
cp -r public release-build/
cp -r resources release-build/
cp -r routes release-build/
cp -r storage release-build/
cp -r vendor release-build/

# Copy root files
cp artisan release-build/
cp composer.json release-build/
cp composer.lock release-build/
cp package.json release-build/
cp package-lock.json release-build/
cp vite.config.js release-build/
cp tailwind.config.js release-build/
cp postcss.config.js release-build/
cp .env.example release-build/
cp README.md release-build/
cp INSTALL.md release-build/
cp LICENSE release-build/

# Create .env from example
cp .env.example release-build/.env

# Create empty database
touch release-build/database/database.sqlite

# Create storage directories
mkdir -p release-build/storage/app/public
mkdir -p release-build/storage/framework/{cache,sessions,testing,views}
mkdir -p release-build/storage/logs
mkdir -p release-build/bootstrap/cache

# Create .gitkeep files
touch release-build/storage/app/public/.gitkeep
touch release-build/storage/logs/.gitkeep

# Set permissions
chmod -R 775 release-build/storage
chmod -R 775 release-build/bootstrap/cache

echo "📁 Creating ZIP file..."

# Create ZIP
cd release-build
zip -r ../sitesphere.zip . -x "*.git*"
cd ..

# Get file size
SIZE=$(du -h sitesphere.zip | cut -f1)

echo ""
echo "✅ Release created successfully!"
echo "📦 File: sitesphere.zip ($SIZE)"
echo ""
echo "📤 Ready to upload to GitHub Releases"

# Cleanup
rm -rf release-build
