<?php

namespace Svc\VersioningBundle\Service;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class SentryReleaseHandling
{
  public const FILE_NAME = 'config/packages/sentry.yaml';

  public function WriteNewSentryRelease(string $version, ?string $sentryAppName, SymfonyStyle $io): bool
  {
    try {
      $yamlArray = Yaml::parseFile(self::FILE_NAME);
    } catch (\Exception $e) {
      $io->error('Cannot read and parse sentry configuration ' . self::FILE_NAME . "\n(Error: " . $e->getMessage() . ')');

      return false;
    }

    $release = $version;
    if ($sentryAppName) {
      $sentryAppName = preg_replace('/\s+/', '', $sentryAppName);
      $release = $sentryAppName . '@' . $release;
    }
    if (array_key_exists('when@dev', $yamlArray) and isset($yamlArray['when@dev']['sentry'])) {
      $yamlArray['when@dev']['sentry']['options']['release'] = $release;
    }
    if (array_key_exists('when@prod', $yamlArray) and isset($yamlArray['when@prod']['sentry'])) {
      $yamlArray['when@prod']['sentry']['options']['release'] = $release;
    }
    if (array_key_exists('when@test', $yamlArray) and isset($yamlArray['when@test']['sentry'])) {
      $yamlArray['when@test']['sentry']['options']['release'] = $release;
    }
    if (array_key_exists('sentry', $yamlArray) and isset($yamlArray['sentry']['dsn'])) {
      $yamlArray['sentry']['options']['release'] = $release;
    }

    try {
      file_put_contents(self::FILE_NAME, Yaml::dump($yamlArray, 10));
    } catch (\Exception $e) {
      $io->error('Cannot write new sentry configuration ' . self::FILE_NAME . "\n(Error: " . $e->getMessage() . ')');

      return false;
    }

    return true;
  }
}
