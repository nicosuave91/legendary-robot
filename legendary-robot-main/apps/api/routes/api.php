<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

foreach (glob(app_path('Modules/*/Routes/api.php')) as $routeFile) {
    require $routeFile;
}
