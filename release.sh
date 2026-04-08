#!/bin/bash

# Simple WP Plugin SVN Release Script for Multilingual Forms for Fluent Forms with WPML
# Usage: ./release.sh <version> [source-dir] [svn-dir]

set -e

# Default values
DEFAULT_SOURCE_DIR="/Volumes/Projects/Release/Current-Release/multilingual-forms-fluent-forms-wpml"
DEFAULT_SVN_DIR="/Volumes/Projects/svn/multilingual-forms-fluent-forms-wpml"

VERSION=$1
SOURCE_DIR=${2:-$DEFAULT_SOURCE_DIR}
SVN_DIR=${3:-$DEFAULT_SVN_DIR}

if [ -z "$VERSION" ]; then
    echo "Error: Version is required"
    echo "Usage: $0 <version> [source-dir] [svn-dir]"
    echo "Example: $0 1.0.5  (uses defaults: $DEFAULT_SOURCE_DIR, $DEFAULT_SVN_DIR)"
    echo "Example: $0 1.0.5 ./my-plugin ./my-plugin-svn"
    exit 1
fi

cd "$SVN_DIR"

echo "→ Updating SVN..."
svn update

echo "→ Copying files to trunk..."
rsync -a --exclude='release.sh' "$SOURCE_DIR/" trunk/

echo "→ Adding new files..."
svn add trunk/* --force

echo "→ Creating tag..."
svn cp trunk tags/$VERSION

echo "→ Committing..."
#svn ci -m "Releases version $VERSION"

echo "✓ Done! Released version $VERSION"
