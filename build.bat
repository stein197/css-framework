@ECHO OFF
ECHO Clearing .\dist folder...
CALL DEL /q dist\*

ECHO Compiling SCSS file...
CALL scss --style compressed css\template.scss dist\template.min.css

ECHO Minifying JS file...
CALL uglifyjs -o dist\template.min.js js\template.js

ECHO Complete
