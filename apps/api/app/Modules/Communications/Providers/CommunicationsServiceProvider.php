<?php

declare(strict_types=1);

namespace App\Modules\Communications\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Modules\Clients\Models\Client;
use App\Modules\Communications\Contracts\EmailTransportProvider;
use App\Modules\Communications\Contracts\SmsTransportProvider;
use App\Modules\Communications\Contracts\VoiceTransportProvider;
use App\Modules\Communications\Services\ClientCommunicationAccessService;
use App\Modules\Communications\Services\Providers\SendGridEmailAdapter;
use App\Modules\Communications\Services\Providers\TwilioMessagingAdapter;
use App\Modules\Communications\Services\Providers\TwilioVoiceAdapter;
use App\Modules\IdentityAccess\Models\User;

final class CommunicationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SmsTransportProvider::class, TwilioMessagingAdapter::class);
        $this->app->bind(VoiceTransportProvider::class, TwilioVoiceAdapter::class);
        $this->app->bind(EmailTransportProvider::class, SendGridEmailAdapter::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Gate::define('clients.communications.read', fn (?User $user, ?Client $client = null): bool => $user !== null && $client !== null
            ? app(ClientCommunicationAccessService::class)->canRead($user, $client)
            : false);

        Gate::define('clients.communications.sms.send', fn (?User $user, ?Client $client = null): bool => $user !== null && $client !== null
            ? app(ClientCommunicationAccessService::class)->canSendSms($user, $client)
            : false);

        Gate::define('clients.communications.email.send', fn (?User $user, ?Client $client = null): bool => $user !== null && $client !== null
            ? app(ClientCommunicationAccessService::class)->canSendEmail($user, $client)
            : false);

        Gate::define('clients.communications.call.create', fn (?User $user, ?Client $client = null): bool => $user !== null && $client !== null
            ? app(ClientCommunicationAccessService::class)->canInitiateCall($user, $client)
            : false);
    }
}
