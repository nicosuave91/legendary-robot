<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Http\Controllers\Controller;
use App\Modules\Communications\Models\CommunicationAttachment;

final class PublicCommunicationAttachmentController extends Controller
{
    public function __invoke(Request $request, string $attachmentId): BinaryFileResponse
    {
        abort_unless($request->hasValidSignature(), 401);

        $attachment = CommunicationAttachment::query()
            ->withoutGlobalScopes()
            ->where('id', $attachmentId)
            ->firstOrFail();

        abort_if(in_array((string) $attachment->scan_status, ['rejected', 'quarantined'], true), 404);
        abort_if(($attachment->storage_disk ?? '') === '' || ($attachment->storage_path ?? '') === '', 404);

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
