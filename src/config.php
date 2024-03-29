<?php
/**
 * Pitch config.php
 *
 * This file exists only as a template for the Pitch settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'pitch.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
  'cssFormat' => 'Compressed',
  'minifyFiles' => true,
  'useCache' => true,
  'advancedCache' => false,
  'cacheDir' => '@storage/pitch',
  'cacheDuration' => 2592000
];
