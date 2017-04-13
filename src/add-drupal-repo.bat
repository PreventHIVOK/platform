@echo Off
SetLocal DisableDelayedExpansion

set MIN_STABLE=dev

echo.
echo Drupal 8 Project setup script for Windows.
echo.

echo Setting minimum package stability to %MIN_STABLE%
call composer config "minimum-stability" %MIN_STABLE%

echo.

echo Ensuring stable packages are preferred
call composer config "prefer-stable" true

echo.

echo Optimizing your Drupal project

call composer config "bin-dir" bin
echo   - Bin directory configured

call composer config "optimize-autoloader" true
call composer config "apcu-autoloader" true
echo   - Autoloader optimized

call composer -q require --dev "hirak/prestissimo:~0.3"
echo   - Package download parallelizer installed
echo.

call composer config "repositories.drupal" composer "https://packages.drupal.org/8"
echo Added Drupal 8 repository to your composer.json file

echo.

echo Adding necessary packages for Drupal 8 installation.

call composer -q require "composer/installers:~1.2"
echo   - Composer Installers installed

call composer -q require "cweagans/composer-patches:~1.6"
call composer config "extra.enable-patching" true
echo   - Composer Patches installed

call composer -q require "drupal/console:~1.0.0-rc16"
echo   - Drupal Console installed

call composer -q require "drush/drush:~8.0"
echo   - Drush installed

call composer require "drupal-composer/drupal-scaffold:dev-master"
echo   - Drupal scaffold installed

echo.

echo.
echo Your project has been configured. Please enjoy! :)
echo.

rem @TODO COULD NOT GET TO WORK, REVISIT OR FIX IN CODE.

rem call composer config "repositories.packagist\.org" composer "https://packagist.org"
rem echo Used a workaround to fix slow Packagist resolve on Windows
