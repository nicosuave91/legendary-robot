<?php

declare(strict_types=1);

$source = __DIR__ . '/../contracts/openapi.php';
$destination = __DIR__ . '/../../../packages/contracts/openapi.json';

$spec = require $source;

if (!is_array($spec)) {
    fwrite(STDERR, "OpenAPI contract source did not return an array.\n");
    exit(1);
}

$dir = dirname($destination);
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

file_put_contents(
    $destination,
    json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
);

fwrite(STDOUT, "Published OpenAPI contract to {$destination}\n");
