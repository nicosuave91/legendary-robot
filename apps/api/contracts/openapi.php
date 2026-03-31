<?php

declare(strict_types=1);

$base = require __DIR__ . '/openapi.base.php';
$communications = require __DIR__ . '/openapi.communications.php';
$sprint7 = require __DIR__ . '/openapi.disposition_applications.php';
$sprint8 = require __DIR__ . '/openapi.rules_workflows.php';
$sprint9 = require __DIR__ . '/openapi.phase9.php';

$base['info']['version'] = '9.0.0';
$base['info']['description'] = 'Sprint 9 imports ledger, notifications, audit search, and release-hardening contracts layered onto the verified Sprint 8 rules/workflow foundation.';
$base['paths'] = array_replace_recursive($base['paths'] ?? [], $communications['paths'] ?? [], $sprint7['paths'] ?? [], $sprint8['paths'] ?? [], $sprint9['paths'] ?? []);
$base['components']['schemas'] = array_replace($base['components']['schemas'] ?? [], $communications['components']['schemas'] ?? [], $sprint7['components']['schemas'] ?? [], $sprint8['components']['schemas'] ?? [], $sprint9['components']['schemas'] ?? []);

return $base;
