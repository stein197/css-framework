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
CALL scss --no-cache --style compressed .\css\index.scss .\%folder%\template.min.css

:END
ECHO %msg%
EXIT /B %ERRORLEVEL%
