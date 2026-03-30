<?php

declare(strict_types=1);

$base = require __DIR__ . '/openapi.base.php';
$communications = require __DIR__ . '/openapi.communications.php';

$base['info']['version'] = '6.0.0';
$base['info']['description'] = 'Sprint 6 communications hub contracts layered onto the verified Phase 1-4 baseline and partial Sprint 5 artifact.';
$base['paths'] = array_replace($base['paths'] ?? [], $communications['paths'] ?? []);
$base['components']['schemas'] = array_replace($base['components']['schemas'] ?? [], $communications['components']['schemas'] ?? []);

return $base;
