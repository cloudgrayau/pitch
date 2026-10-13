<?php
/**
 * Pitch config
 *
 * Publish this file to `config/craft/pitch.php` and change the settings there;
  * values set here override the ones saved in the control panel.
 */
 
return [
  'cssFormat' => 'Compressed',
  'minifyFiles' => true,
  'useCache' => true,
  'regenerateCache' => true,
  'advancedCache' => false,
  'cacheDir' => '@storage/pitch',
  'cacheDuration' => 31536000,
  'fileHeaders' => [],
];