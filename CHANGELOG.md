# Pitch Changelog

Release notes for the Pitch Craft CMS plugin.

## 2.3.2 - 2023-08-31
- Code optimisations
- Increased storage path normalisation
- Fix error with clearing cache if the storage directory doesn't exist
- Updated minimum requirement of `MatthiasMullie\Minify` to version 1.3.71

## 2.3.1 - 2023-05-22
- Numerous code optimisations
- Updated minimum requirement of `MatthiasMullie\Minify` to version 1.3.70

## 2.2.1 - 2022-12-22
- Fixed minimum requirement of `scssphp/scssphp`

## 2.2.0 - 2022-12-07
- Added twig commands for inline compiling of SCSS and/or minification of CSS/JS
- Updated minimum requirement of `scssphp/scssphp` to version 1.11.0
- Updated minimum requirement of `MatthiasMullie\Minify` to version 1.3.69

## 2.1.0 - 2022-06-28
- Implemented 'MatthiasMullie\Minify' for CSS/JS minification
- Updated `scssphp/scssphp` to version 1.10.3

## 2.0.0 - 2022-03-25
- Craft 4 release

## 1.2.1 - 2022-03-25
- Updated `scssphp/scssphp` to version 1.10.2

## 1.2.0 - 2021-10-29
- Added a console command to clear the Pitch cache `./craft pitch/clear`
- Added a `Cache Duration` setting
- Added an `Advanced Caching` setting that offers superior performance by bypassing PHP on subsequent Pitch requests. This mode requires additional configuration and is highly recommended for use on production environments
- Added an [ADVANCED.md](https://github.com/cloudgrayau/pitch/blob/main/ADVANCED.md) readme file for instructions on setting up advanced caching mode
- Pitch is now officially a year old, YAY

## 1.1.1 - 2021-10-11
- Removed plugin loaded queue log
- Updated `scssphp/scssphp` requirement to version 1.8.1
- `scssphp/scssphp` is now compatible with PHP 8.1
- `scssphp/scssphp` now requires a minimum version of PHP 7.2 or above

## 1.1.0 - 2021-07-02
- Pitch will now work whilst the website is in offline mode.

## 1.0.9 - 2021-05-18
- Updated `scssphp/scssphp` to version 1.5.2
- Fixed settings issue

## 1.0.8 - 2021-05-17
- Updated `scssphp/scssphp` to version 1.5
- `Utilities/ClearCaches` now clears cache

## 1.0.7 - 2020-11-11
- Fixed SCSS formatter

## 1.0.6 - 2020-11-09
- Updated `scssphp/scssphp` to version 1.4

## 1.0.5 - 2020-11-04
- Added CSS/JS minify setting
- Updated SCSS formatter options (for `scssphp/scssphp` 1.4)
- Fixed PHP 7.4 compliance

## 1.0.3 - 2020-10-31
- Added `example` files
- Minor fixes
- Expanded `README`

## 1.0.2 - 2020-10-30
- Updated `scssphp/scssphp` to version 1.3
- Minor fixes

## 1.0.1 - 2020-10-29
- Added `$baseUrl` templating for SCSS and JavaScript
- Caching is now enabled by default
- Expanded `README`
- Fixed Prevent parsing of PHP code in files

## 1.0.0 - 2020-10-29
- Initial release