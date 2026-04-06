<?php

declare(strict_types=1);

namespace App\Modules\Shared\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Modules\Shared\Tenancy\TenantContext;

final class EstablishTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->user()?->tenant_id;

        app(TenantContext::class)->set($tenantId ? (string) $tenantId : null);

        return $next($request);
    }
}
