<?php

declare(strict_types=1);

$source = __DIR__ . '/../contracts/openapi.php';
$destination = __DIR__ . '/../../../packages/contracts/openapi.json';

if (!file_exists($source)) {
    fwrite(STDERR, "OpenAPI contract source file not found: {$source}\n");
    exit(1);
}

$spec = require $source;

if (!is_array($spec)) {
    fwrite(STDERR, "OpenAPI contract source did not return an array.\n");
    exit(1);
}

$dir = dirname($destination);
if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
    fwrite(STDERR, "Failed to create OpenAPI destination directory: {$dir}\n");
    exit(1);
}

try {
    $json = json_encode(
        $spec,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
    );
} catch (JsonException $exception) {
    fwrite(STDERR, "Failed to encode OpenAPI contract: {$exception->getMessage()}\n");
    exit(1);
}

$result = file_put_contents($destination, $json . PHP_EOL);

if ($result === false) {
    fwrite(STDERR, "Failed to write OpenAPI contract to {$destination}\n");
    exit(1);
}

fwrite(STDOUT, "Published OpenAPI contract to {$destination}\n");
