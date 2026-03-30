<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Modules\Shared\Http\Middleware\EstablishTenantContext;
use App\Modules\Shared\Http\Middleware\SetCorrelationId;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withProviders(require __DIR__ . '/providers.php')
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->alias([
            'correlation' => SetCorrelationId::class,
            'tenant.context' => EstablishTenantContext::class,
        ]);

        $middleware->api(prepend: [
            SetCorrelationId::class,
            EstablishTenantContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Use framework defaults for now.
    })
    ->create();
