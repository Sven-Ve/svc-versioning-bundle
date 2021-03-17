<?php
declare(strict_types=1);

namespace Svc\VersioningBundle\Service;

class VersionHandling
{
  private $versionString;
  private $versionFile;

  public function __construct()
  {
    $path = ".";
    $this->versionString= new VersionString();
    $this->versionFile = new VersionFile();
    $this->versionFile->setPath($path);
  }

  public function getNewVersion($version, $majorVer, $minorVer, $patchVer, $init = false): string 
  {
    if ($init) {
      $newVersion=$this->versionString->getInitial();
    } else {

      if (!$version) {
        $version = $this->getCurrentVersion();
      }

      $versionArray=$this->versionString->versionStringToVersionArray($version);

      if (!$majorVer and !$minorVer) {
        $patchVer=true;
      }

      if ($majorVer) {
        $versionArray['major']++;
        $versionArray['minor']=0;
        $versionArray['patch']=0;
      }
      if ($minorVer) {
        $versionArray['minor']++;
        $versionArray['patch']=0;
      }
      if ($patchVer) {
        $versionArray['patch']++;
      }

      $newVersion = $this->versionString->versionArrayToVersionString($versionArray);
    }

    $fileName= $this->versionFile->getFilename();
    $this->versionFile->write($fileName, $newVersion);

    return $newVersion;
  }

  public function getCurrentVersion(): string {

    $fileName= $this->versionFile->getFilename();
    if ($this->versionFile->isValid($fileName)) {
      $version=$this->versionFile->read($fileName);
    } else {
      $version=$this->versionString->getInitial();
      $this->versionFile->write($fileName, $version);
    }
    return $version;
  }

  public function writeTwigFile($fileName, $version) :bool {
    $text="<span title='Release " . date("d.m.Y H:i:s T") . "'>";
    $text.="Version: $version"; 
    $text.="</span>";
    return $this->versionFile->write($fileName, $text);
  }

  public function appendREADME($fileName, $version, $message) :bool {
    $text="* Version $version: $message" . PHP_EOL;
    return $this->versionFile->write($fileName, $text, true);
  }

}