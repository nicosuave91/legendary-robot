<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;
use App\Modules\Communications\Models\CommunicationAttachment;
use App\Modules\Communications\Services\CommunicationAttachmentGovernanceService;

final class PublicCommunicationAttachmentController extends Controller
{
    public function __invoke(Request $request, string $attachmentId, CommunicationAttachmentGovernanceService $communicationAttachmentGovernanceService): Response
    {
        abort_unless($request->hasValidSignature(), 401);

        $attachment = CommunicationAttachment::query()
            ->withoutGlobalScopes()
            ->where('id', $attachmentId)
            ->firstOrFail();

        abort_unless($communicationAttachmentGovernanceService->canServePublicly($attachment), 404);

        return Storage::disk((string) $attachment->storage_disk)->response(
            (string) $attachment->storage_path,
            (string) $attachment->original_filename,
            [
                'Content-Type' => (string) $attachment->mime_type,
                'Cache-Control' => 'private, max-age=300',
            ],
        );
    }
}
