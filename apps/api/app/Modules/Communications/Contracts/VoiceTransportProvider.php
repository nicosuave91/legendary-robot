<?php

declare(strict_types=1);

namespace App\Modules\Communications\Contracts;

use App\Modules\Communications\DTOs\ProviderSubmissionResultData;
use App\Modules\Communications\Models\CallLog;

interface VoiceTransportProvider
{
    public function initiate(CallLog $callLog): ProviderSubmissionResultData;
}
