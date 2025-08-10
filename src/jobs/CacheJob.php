<?php
namespace cloudgrayau\pitch\jobs;

use cloudgrayau\pitch\Pitch;

use Craft;
use craft\queue\BaseJob;
use craft\helpers\UrlHelper;

class CacheJob extends BaseJob {
  
  protected function defaultDescription(): string {
    return Craft::t('pitch', 'Generating Pitch cache');
  }
  public function execute($queue): void {
    $directoryPath = CRAFT_BASE_PATH.'/templates/';
    $pattern = '/\bpitch\s*\(\s*(?!\/|\\\\[\'"])((?:\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*"|[^\'",\)]+)*)\s*(?:,|\))/';
    $output = [];
    if (function_exists('exec')) {
      exec('grep -rl "pitch(" '.$directoryPath, $output);
    } else {
      try {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directoryPath, \RecursiveDirectoryIterator::SKIP_DOTS),\RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($iterator as $file){
          if ($file->isFile()){
            $output[] = $file;
          }
        }
      } catch (\UnexpectedValueException $e){
      }
    }
    $files = [];
    foreach($output as $file){
      $data = file_get_contents($file);
      preg_match_all($pattern, $data, $matches, PREG_SET_ORDER);
      foreach($matches as $match){
        $pitchUrl = Pitch::$plugin->twigExtension->generatePitch(str_replace(['\'','~'],'',$match[1]), false);
        if ($pitchUrl){
          $url = UrlHelper::siteUrl($pitchUrl);
          if (!in_array($url, $files)){
            $files[] = $url;
          }
        }
      }
    }
    if (!empty($files)){
      $count = count($files);
      $curl = curl_init();
      foreach ($files as $i => $file) {
        $this->setProgress(
          $queue,
          $i / $count,
          Craft::t('pitch', 'Generating {step, number} of {total, number}', [
            'step' => ($i+1),
            'total' => $count
          ])
        );
        try {
          curl_setopt($curl, CURLOPT_URL, $file);
          curl_setopt($curl, CURLOPT_NOBODY, true);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($curl, CURLOPT_HEADER, true);
          curl_exec($curl);
        } catch (\Exception $e) {
        }
      }
      curl_close($curl); 
    }
  }
  
}