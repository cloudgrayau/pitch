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

SCSS compiling uses the latest version of [ScssPhp](https://scssphp.github.io/) and five formatters are included:

- `Formatter\Expanded`
- `Formatter\Nested`
- `Formatter\Compressed`
- `Formatter\Compact`
- `Formatter\Crunched` *(default)*

CSS and JS minifying uses a custom basic minifier.

Caching can be enabled (recommended) and the cache directory can be customised. The cache can be cleared via the plugin settings page.

## Using Pitch

- **SCSS** - `{% do view.registerCssFile(url('scss/FILENAME.css')) %}`
- **CSS** - `{% do view.registerCssFile(url('css/FILENAME.css')) %}`
- **JS** - `{% do view.registerJsFile(url('js/FILENAME.js')) %}`

To merge files, use commas (this will merge the included files based on the directory location of the original file).

**SCSS** - For example, `'scss/assets/style,chosen,plugin/owl.css'` will merge the following files and parse via SCSS:

- `/CRAFT/web/assets/style.scss`
- `/CRAFT/web/assets/chosen.scss`
- `/CRAFT/web/assets/plugin/owl.scss`

**CSS** - For example, `'css/assets/style,chosen,plugin/owl.css'` will merge the following files and minify the CSS:

- `/CRAFT/web/assets/style.css`
- `/CRAFT/web/assets/chosen.css`
- `/CRAFT/web/assets/plugin/owl.css`

**JS** - For example, `'js/assets/site,plugin/chosen,plugin/test.js'` will merge the following files and minify the JavaScript:

- `/CRAFT/web/assets/site.js`
- `/CRAFT/web/assets/plugin/chosen.js`
- `/CRAFT/web/assets/plugin/test.js`

You can also force the browser to re-cache asset files by using `:DIGIT` in the asset URL prior to the extension, for example `'js/assets/site,plugin/chosen:01.js'`.

Whilst in development mode, the browser cache of all assets will be forced to refresh on each page load.

## Templating

- In SCSS, `$baseUrl` refers to the relative `@web` directory (no trailing slash).
- In JS, `$baseUrl` refers to the relative `@web` directory (no trailing slash).

Brought to you by [Cloud Gray Pty Ltd](https://cloudgray.com.au/)
