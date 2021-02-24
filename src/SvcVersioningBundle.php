<?php

namespace Svc\VersioningBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SvcVersioningBundle extends Bundle {

  public function getPath(): string
  {
      return \dirname(__DIR__);
  }
}