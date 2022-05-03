<?php
declare(strict_types=1);

namespace Svc\VersioningBundle\Service;

class VersionString
{
    private const VERSION_SEPARATOR = '.';

    public function versionStringToVersionArray(string $versionString): array
    {
      $versionArray = $this->parser($versionString);

        return [
            'major' => intval($versionArray[0]),
            'minor' => intval($versionArray[1]),
            'patch' => intval($versionArray[2]),
        ];
    }


    public function getInitial(): string
    {
      $format = '0%s0%s1';
      $ret    = sprintf($format, self::VERSION_SEPARATOR, self::VERSION_SEPARATOR);

      return $ret;
    }

    public function versionArrayToVersionString(array $versionArray): string
    {
      return implode(self::VERSION_SEPARATOR, $versionArray);
    }

    /**
     * convert version string (1.2.3) in an array
     *
     * @param string $version
     * @return array
     */
    private function parser(string $version): array
      $parts = explode('.', $version);
      return $parts;
    }
}
