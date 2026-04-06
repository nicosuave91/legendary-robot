<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services;

use Illuminate\Support\Facades\URL;
use App\Modules\Communications\Models\CommunicationAttachment;

final class CommunicationAttachmentUrlService
{
    public function temporaryPublicUrl(CommunicationAttachment $attachment, int $minutes = 30): string
    {
        return URL::temporarySignedRoute(
            'communications.attachments.public',
            now()->addMinutes($minutes),
            ['attachmentId' => (string) $attachment->id],
        );
    }
}
