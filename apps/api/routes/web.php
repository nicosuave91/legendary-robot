<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['status' => 'ok']));

foreach (glob(app_path('Modules/*/Routes/webhooks.php')) as $routeFile) {
    require $routeFile;
}
