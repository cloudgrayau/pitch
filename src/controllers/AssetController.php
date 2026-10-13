<?php
namespace cloudgrayau\pitch\controllers;

use cloudgrayau\pitch\services\AssetService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AssetController {

  public function __construct(private AssetService $assets) {}

  public function css(Request $request): Response {
    return $this->assets->serve($request, 'css');
  }

  public function scss(Request $request): Response {
    return $this->assets->serve($request, 'scss');
  }

  public function js(Request $request): Response {
    return $this->assets->serve($request, 'js');
  }

}