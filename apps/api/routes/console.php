<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

Artisan::command('app:ping', function (): void {
    $this->info('pong');
});
