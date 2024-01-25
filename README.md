# Pitch for Craft CMS 4/5

On the go SCSS compiling, CSS/JS minifying, merging and caching.

![Screenshot](resources/pitch.png)

## Requirements

This plugin requires Craft CMS 4.0.0 or later.

## Installation

`composer require cloudgrayau/pitch`

## Pitch Overview

Pitch is a plugin that allows for on the go SCSS compiling, CSS/JS minifying, merging and caching.

## Configuring Pitch

SCSS compiling uses the latest version of [scssphp](https://scssphp.github.io/scssphp/) and two output styles are included:

- `Expanded`
- `Compressed` *(default)*

CSS and JS minifying uses the 'MatthiasMullie\Minify' package (which can be disabled via the settings).

Caching is enabled by default (recommended) and the cache directory and duration can be customised. 

### Advanced Caching Mode

**Advanced Caching Mode** can also be enabled, which offers superior performance but requires server rewrites and changes to the default storage path. For instructions on how to setup Advanced Caching Mode, please refer to the [ADVANCED.md](https://github.com/cloudgrayau/pitch/blob/craft4/ADVANCED.md).

## Using Pitch

- **SCSS** - `{% do view.registerCssFile(url('scss/FILENAME.scss')) %}`
- **CSS** - `{% do view.registerCssFile(url('css/FILENAME.css')) %}`
- **JS** - `{% do view.registerJsFile(url('js/FILENAME.js')) %}`

For [example files](https://github.com/cloudgrayau/pitch/tree/craft4/examples), please browse to the `/vendor/cloudgrayau/pitch/examples/` directory for installation.

You can now compile inline SCSS and minify CSS/JS directly via your templates in Twig.

- **SCSS** - `<style>{{ craft.pitch.renderSCSS(entry.field)|raw }}</style>`
- **CSS** - `<style>{{ craft.pitch.renderCSS(entry.field)|raw }}</style>`
- **JS** - `<script type="text/javascript">{{ craft.pitch.renderJS(entry.field)|raw }}</script>`

Further instructions for the inline method are found below.

## Merging & Loading Files

### SCSS ###

`{% do view.registerCssFile(url('scss/style.scss')) %}` will load and compile the following SCSS file:

- `/CRAFT/web/style.scss`

`{% do view.registerCssFile(url('scss/assets/style,chosen,plugin/owl.scss')) %}` will merge and compile the following SCSS files:

- `/CRAFT/web/assets/style.scss`
- `/CRAFT/web/assets/chosen.scss`
- `/CRAFT/web/assets/plugin/owl.scss`

All files being merged will need to have the `.scss` extension.

In SCSS, `$baseUrl` refers to the relative `@web` directory (no trailing slash).

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

In JS, `$baseUrl` refers to the relative `@web` directory (no trailing slash).

--------

You can also force the browser to re-cache asset files by using `:DIGIT` in the asset URL prior to the extension, for example `'js/assets/site,plugin/chosen:01.js'`.

Whilst in development mode, the browser cache of all assets will be forced to refresh on each page load.

## Inline Compiling & Minifying

You can now compile inline SCSS and minify CSS/JS directly via your templates in Twig, as follows:

- **SCSS** - `<style>{{ craft.pitch.renderSCSS(entry.field)|raw }}</style>`
- **CSS** - `<style>{{ craft.pitch.renderCSS(entry.field)|raw }}</style>`
- **JS** - `<script type="text/javascript">{{ craft.pitch.renderJS(entry.field)|raw }}</script>`

Please note, the `@import` command in SCSS, will be relative to the `@web` directory.

I would highly recommend that the inline method is inclosed in `{% cache %}{% endcache %}` tags for performance reasons.

The Inline Compiling & Minifying integrates great with the [Code Field](https://github.com/nystudio107/craft-code-field) from @nystudio107.

## Clearing Cache

The Pitch cache can be cleared via the following methods:
	
1. Via the CraftCMS Settings => Pitch
2. Via the CraftCMS Utilities => Caches
3. Via the console `./craft pitch/clear`

Brought to you by [Cloud Gray Pty Ltd](https://cloudgray.com.au/)
