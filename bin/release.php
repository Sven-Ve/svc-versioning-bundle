#!/usr/bin/env php
<?php

$version = "5.0.1";
$message = "ready for symfony 6.4 and 7 - fixed type declarations";

echo("Running phpstan:\n");
system("composer run-script phpstan", $res);
if ($res>0) {
  echo("\nError during execution phpstan. Releasing canceled.\n");
  return 1;
}

file_put_contents("CHANGELOG.md", "\n\n## Version " . $version, FILE_APPEND);
file_put_contents("CHANGELOG.md", "\n*" . date("r") . "*", FILE_APPEND);
file_put_contents("CHANGELOG.md", "\n- " . $message . "\n", FILE_APPEND);

$res = shell_exec('git add .');
$res = shell_exec('git commit -m "' . $message . '"');
$res = shell_exec('git push');

$res = shell_exec('git tag -a ' . $version . ' -m "' . $message . '"');
$res = shell_exec('git push origin ' . $version);

?>
