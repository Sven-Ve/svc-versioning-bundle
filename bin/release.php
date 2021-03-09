#!/usr/bin/env php
<?php

$version = "v0.2.0";
$message = "sign commits";

$res = shell_exec('git add .');
$res = shell_exec('git commit -S -m "' . $message . '"');
$res = shell_exec('git push');
$res = shell_exec('git tag -a ' . $version . ' -m "' . $message . '"');
$res = shell_exec('git push origin ' . $version);

$res = shell_exec('ssh svenvett@svenvett.myhostpoint.ch  "cd /home/svenvett/www/satis; bin/satis build satis.json"');
?>
