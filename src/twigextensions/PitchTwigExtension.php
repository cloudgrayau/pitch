<?php
namespace cloudgrayau\pitch\twigextensions;

use cloudgrayau\pitch\Pitch;
use cloudgrayau\pitch\models\Paths;
use cloudgrayau\pitch\models\Cached;

use Craft;
use craft\helpers\App;
use craft\helpers\FileHelper;
use craft\helpers\UrlHelper;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class PitchTwigExtension extends AbstractExtension {
  
    private array $pitch = [];
    
    // Public Methods
    // =========================================================================
    
    public function getName(): string {
        return 'Pitch';
    }

    public function getFunctions(): array {
        return [
            new TwigFunction('pitch', [$this, 'generatePitch']),
            new TwigFunction('pitch_sri', [$this, 'generateSRI']),
        ];
    }

    public function generatePitch(string $pitch = '', bool $base = true): string {
      if (isset($this->pitch[$pitch])){
        return ($base) ? UrlHelper::siteUrl($this->pitch[$pitch]) : ('/'.$this->pitch[$pitch]);
      } else {
        $path = parse_url(FileHelper::normalizePath($pitch));
        $paths = pathinfo($path['path']);
        if (isset($paths['extension'])){
          switch($paths['extension']){
            case 'css':
            case 'scss':
            case 'js':
              $output = explode('/', $paths['dirname'].'/'.$paths['filename'].'.'.$paths['extension']);
              if (empty($output[0])){
                array_shift($output);
              }
              Paths::$output = $output;
              extract(Paths::getPaths());
              $webroot = Craft::getAlias('@webroot').'/';
              $settings = Pitch::getInstance()->settings;
              $ext = strrchr($files,'.');
              $files = explode(',', substr($files, 0, -strlen($ext)));
              $realfiles = [];
              $filemtime = 0;
              foreach($files as $file){
                $pos = strpos($file,':');
                if (($pos !== false) && (ctype_digit(substr($file, $pos+1)))){
                  $file = substr($file, 0, $pos);
                }
                $asset = FileHelper::normalizePath($webroot.$dir.$file.'.'.$paths['extension']);
                if (file_exists($asset)){
                  $realfiles[] = $file;
                  $mtime = FileHelper::lastModifiedTime($asset);
                  if ($mtime > $filemtime){
                    $filemtime = $mtime;
                  }
                }
              }
              if ($filemtime > 0){
                $url = $paths['extension'].'/'.$dir.implode(',',$realfiles).':'.$filemtime.'.'.$paths['extension'];
                $this->pitch[$pitch] = $url;
                return ($base) ? UrlHelper::siteUrl($url) : ('/'.$url);
              }
              break;
          }
        }
      }
      return '';
    }
    
    public function generateSRI(string $pitch = '', string $hash = 'sha384'): mixed {
      if (getenv('CRAFT_ENVIRONMENT') !== 'dev'){
        if (isset($this->pitch[$pitch])){
          $filename = $this->pitch[$pitch];
          $settings = Pitch::getInstance()->settings;
          $cacheDir = (!empty($settings->cacheDir)) ? $settings->cacheDir : '@storage/pitch';
          $cacheFolderPath = FileHelper::normalizePath(
            App::parseEnv($cacheDir)
          ).'/';
          $c = new Cached($cacheFolderPath, false, $settings->advancedCache);
          if ($tmp_file = $c->generateURL($filename)){
            $cachefile = FileHelper::normalizePath($cacheFolderPath.$tmp_file);
            if (file_exists($cachefile)) {
              return $hash.'-'.base64_encode(hash_file($hash, $cachefile, true));
            }
          }
        }
      }
      return false;
    }
    
}
