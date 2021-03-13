#!/usr/bin/env php
<?php

$version = "v1.0.1";
$message = "request php >7.4.0 or >8.0.0";

$res = shell_exec('git add .');
$res = shell_exec('git commit -S -m "' . $message . '"');
$res = shell_exec('git push');
$res = shell_exec('git tag -a -s ' . $version . ' -m "' . $message . '"');
$res = shell_exec('git push origin ' . $version);

$res = shell_exec('ssh svenvett@svenvett.myhostpoint.ch  "cd /home/svenvett/www/satis; bin/satis build satis.json"');
?>
