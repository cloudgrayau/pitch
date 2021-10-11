# Pitch for Craft CMS 3

On the go SCSS compiling, CSS/JS minifying, merging and caching.

![Screenshot](resources/pitch.png)

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require cloudgrayau/pitch

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Pitch.

## Pitch Overview

Pitch is a plugin that allows for on the go SCSS compiling, CSS/JS minifying, merging and caching.

## Configuring Pitch

SCSS compiling uses the latest version of [scssphp](https://scssphp.github.io/scssphp/) and two output styles are included:

- `Expanded`
- `Compressed` *(default)*

CSS and JS minifying uses a custom basic minifier (which can be disabled via the settings).

Caching is enabled by default (recommended) and the cache directory can be customised. The cache can be cleared via the plugin settings page.

## Using Pitch

- **SCSS** - `{% do view.registerCssFile(url('scss/FILENAME.scss')) %}`
- **CSS** - `{% do view.registerCssFile(url('css/FILENAME.css')) %}`
- **JS** - `{% do view.registerJsFile(url('js/FILENAME.js')) %}`

For [example files](https://github.com/cloudgrayau/pitch/tree/main/examples), please browse to the `/vendor/cloudgrayau/pitch/examples/` directory for installation.

## Merging & Loading Files

### SCSS ###

`{% do view.registerCssFile(url('scss/style.scss')) %}` will load and compile the following SCSS file:

- `/CRAFT/web/style.scss`

`{% do view.registerCssFile(url('scss/assets/style,chosen,plugin/owl.scss')) %}` will merge and compile the following SCSS files:

- `/CRAFT/web/assets/style.scss`
- `/CRAFT/web/assets/chosen.scss`
- `/CRAFT/web/assets/plugin/owl.scss`

All files being merged will need to have the `.scss` extension.

--------

### CSS ###

`{% do view.registerCssFile(url('css/style.css')) %}` will load and minify the following CSS file:

- `/CRAFT/web/style.css`

`{% do view.registerCssFile(url('css/assets/style,chosen,plugin/owl.css')) %}` will merge and minify the following CSS files:

- `/CRAFT/web/assets/style.css`
- `/CRAFT/web/assets/chosen.css`
- `/CRAFT/web/assets/plugin/owl.css`

All files being merged will need to have the `.css` extension.

--------

### JS ###

`{% do view.registerJsFile(url('js/script.js')) %}` will load and minify the following JS file:

- `/CRAFT/web/script.js`

`{% do view.registerJsFile(url('js/assets/script,chosen,plugin/owl.js')) %}` will merge and minify the following JS files:

- `/CRAFT/web/assets/script.js`
- `/CRAFT/web/assets/chosen.js`
- `/CRAFT/web/assets/plugin/owl.js`

All files being merged will need to have the `.js` extension.

--------

You can also force the browser to re-cache asset files by using `:DIGIT` in the asset URL prior to the extension, for example `'js/assets/site,plugin/chosen:01.js'`.

Whilst in development mode, the browser cache of all assets will be forced to refresh on each page load.

## Templating

- In SCSS, `$baseUrl` refers to the relative `@web` directory (no trailing slash).
- In JS, `$baseUrl` refers to the relative `@web` directory (no trailing slash).

Brought to you by [Cloud Gray Pty Ltd](https://cloudgray.com.au/)
