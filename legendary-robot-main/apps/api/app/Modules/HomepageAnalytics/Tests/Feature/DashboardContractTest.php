<?php

declare(strict_types=1);

namespace App\Modules\HomepageAnalytics\Tests\Feature;

use Tests\TestCase;

class DashboardContractTest extends TestCase
{
    public function test_dashboard_endpoints_are_registered(): void
    {
        $this->assertNotNull(app('router')->getRoutes()->getByName('api.v1.dashboard.summary'));
        $this->assertNotNull(app('router')->getRoutes()->getByName('api.v1.dashboard.production'));
    }
}
