#!/bin/sh
folder="dist"

echo "Clearing \"$folder\" folder..."
rm -f ./$folder/*

if ! [ -x "$(command -v scss)" ]; then
	echo "SCSS/SASS is not installed. Install and add it to the \$PATH variable"
	exit 1
else
	echo "Compiling SCSS file..."
	scss --no-cache --style compressed ./css/template.scss ./$folder/template.min.css
fi

