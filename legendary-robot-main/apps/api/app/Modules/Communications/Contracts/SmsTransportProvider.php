<?php

declare(strict_types=1);

namespace App\Modules\Communications\Contracts;

use App\Modules\Communications\DTOs\ProviderSubmissionResultData;
use App\Modules\Communications\Models\CommunicationMessage;

interface SmsTransportProvider
{
    public function send(CommunicationMessage $message): ProviderSubmissionResultData;
}
