<?php

declare(strict_types=1);

namespace Svc\VersioningBundle\Service;

class VersionFile
{
  private const VERSION_FILE = '.version';

  private string $path = '.';

  /**
   * set the path of the config file.
   */
  public function setPath(string $path): void
  {
    $this->path = $path;
  }

  /**
   * get the config file name.
   */
  public function getFilename(): string
  {
    return $this->path . DIRECTORY_SEPARATOR . self::VERSION_FILE;
  }

  public function isValid(string $filename): bool
  {
    return is_readable($filename);
  }

  public function read(string $filename): string
  {
    $buffer = file_get_contents($filename);

    return trim($buffer);
  }

  /**
   * write/append text in a file.
   *
   * @param string $buffer the message text
   */
  public function write(string $filename, string $buffer, bool $append = false): bool
  {
    $flag = 0;
    if ($append) {
      $flag = FILE_APPEND;
    }

    if (@file_put_contents($filename, $buffer, $flag) === false) {
      return false;
    }

    return true;
  }
}
