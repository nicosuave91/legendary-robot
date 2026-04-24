<?php

declare(strict_types=1);

$base = require __DIR__ . '/openapi.base.php';
$identityAccess = require __DIR__ . '/openapi.identity_access.php';
$clients = require __DIR__ . '/openapi.clients.php';
$communications = require __DIR__ . '/openapi.communications.php';
$sprint7 = require __DIR__ . '/openapi.disposition_applications.php';
$sprint8 = require __DIR__ . '/openapi.rules_workflows.php';
$sprint9 = require __DIR__ . '/openapi.phase9.php';
$sprint11 = require __DIR__ . '/openapi.calendar_tasks.php';
$workflowClosure = require __DIR__ . '/openapi.workflow_closure.php';

$base['info']['version'] = '15.4.0';
$base['info']['description'] = 'Post-merge build stabilization, generated-client drift prevention, client status contract alignment, and account onboarding contract compatibility layered onto the verified Sprint 15 platform baseline.';
$base['paths'] = array_replace_recursive(
    $base['paths'] ?? [],
    $clients['paths'] ?? [],
    $communications['paths'] ?? [],
    $sprint7['paths'] ?? [],
    $sprint8['paths'] ?? [],
    $sprint9['paths'] ?? [],
    $sprint11['paths'] ?? [],
    $workflowClosure['paths'] ?? [],
);
$base['components']['schemas'] = array_replace(
    $base['components']['schemas'] ?? [],
    $identityAccess['components']['schemas'] ?? [],
    $clients['components']['schemas'] ?? [],
    $communications['components']['schemas'] ?? [],
    $sprint7['components']['schemas'] ?? [],
    $sprint8['components']['schemas'] ?? [],
    $sprint9['components']['schemas'] ?? [],
    $sprint11['components']['schemas'] ?? [],
    $workflowClosure['components']['schemas'] ?? [],
);

return $base;
