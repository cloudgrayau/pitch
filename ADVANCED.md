# Pitch - Advanced Caching Mode

![Screenshot](resources/pitch.png)

**Advanced Caching Mode** offers superior performance but requires additional server rewrites and changes to the default storage path. It is highly recommended for use on production environments.

This mode works by completely bypassing PHP on subsequent asset requests by loading the generated files directly. For this to work, both the **Enable Cache** and **Advanced Caching Mode** settings need to be enabled in Pitch and the cache storage path needs to be updated from `@storage/<PATH>` to `@webroot/<PATH>`. 

Please note, this mode completely ignores the `cacheDuration` setting and requires a reset of the Pitch cache anytime an asset file is modified so that Pitch can re-generate the rendered files. For this reason, this mode is NOT recommended for use in development environments.

## Setup

### Configuration ###

1. Enable both the **Enable Cache** and **Enable Advanced Caching** settings in Pitch.
2. Update the Pitch **Cache Storage Path** from `@storage/pitch` to `@webroot/pitch`. The cached Pitch files need to be public for advanced caching to work.
3. Update your server rewrites for either Apache or Nginx.
4. Enjoy the updated performance.

## Apache

The following `.htaccess` rewrite rules are required for advanced caching to load the generated files:

*Please replace the `/pitch` part of the rewrite below with the updated path, if the cache storage path differs from the recommended path `@webroot/pitch`.*

 	# Pitch advanced cache rewrite
  	RewriteCond %{DOCUMENT_ROOT}/pitch/%{REQUEST_URI} -s
  	RewriteCond %{REQUEST_METHOD} GET
  	RewriteRule .* /pitch/%{REQUEST_URI} [L]
  		
The following `.htaccess` rules are also highly recommended:

	<IfModule mod_mime.c>
  		AddType text/css scss
	</IfModule>
	<IfModule mod_deflate.c>
  		AddOutputFilterByType DEFLATE text/css application/javascript
	</IfModule>

A full example `.htaccess` is as follows:

	<IfModule mod_rewrite.c>
  		RewriteEngine On
  
 		# Pitch advanced cache rewrite
  		RewriteCond %{DOCUMENT_ROOT}/pitch/%{REQUEST_URI} -s
  		RewriteCond %{REQUEST_METHOD} GET
  		RewriteRule .* /pitch/%{REQUEST_URI} [L]

  		# Send would-be 404 requests to Craft
  		RewriteCond %{REQUEST_FILENAME} !-f
  		RewriteCond %{REQUEST_FILENAME} !-d
  		RewriteCond %{REQUEST_URI} !^/(favicon\.ico|apple-touch-icon.*\.png)$ [NC]
  		RewriteRule (.+) index.php?p=$1 [QSA,L]
	</IfModule>
	<IfModule mod_mime.c>
  		AddType text/css scss
	</IfModule>
	<IfModule mod_deflate.c>
  		AddOutputFilterByType DEFLATE text/css application/javascript
	</IfModule>
	
## Nginx

The following conf rules are required for advanced caching to load the generated files:

*Please replace the `/pitch` part of the rewrite below with the updated path, if the cache storage path differs from the recommended path `@webroot/pitch`.*

	# Pitch advanced cache rewrite
	set $cache_path false;
	if ($request_method = GET) {
		set $cache_path /pitch/$host/$uri/$args;
	}
	location / {
		try_files $cache_path $uri $uri/ /index.php?$query_string;
	}

*Can anyone help with the translation of the above `.htaccess` rules for Nginx?*

Brought to you by [Cloud Gray Pty Ltd](https://cloudgray.com.au/)
