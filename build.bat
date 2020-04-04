@ECHO OFF
SETLOCAL
SET folder=dist
SET msg=Complete

ECHO Clearing "%folder%" folder...
CALL DEL /q .\%folder%\*

WHERE /Q scss
IF %ERRORLEVEL% NEQ 0 (
	SET msg=SCSS/SASS is not installed. Install and add it to the %%PATH%% variable
	GOTO END
)

ECHO Compiling SCSS file...
CALL scss --no-cache --style compressed .\css\template.scss .\%folder%\template.min.css

WHERE /Q uglifyjs
IF %ERRORLEVEL% NEQ 0 (
	SET msg=uglifyjs for Node.js is not installed. Install and add it to the %%PATH%% variable
	GOTO END
)

ECHO Minifying JS file...
CALL uglifyjs -o .\%folder%\template.min.js js\template.js

:END
ECHO %msg%
EXIT /B %ERRORLEVEL%
