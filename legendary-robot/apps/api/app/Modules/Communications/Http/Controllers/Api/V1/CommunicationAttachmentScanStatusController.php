<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Modules\Communications\Http\Requests\UpdateCommunicationAttachmentScanStatusRequest;
use App\Modules\Communications\Models\CommunicationAttachment;
use App\Modules\Communications\Services\CommunicationAttachmentGovernanceService;
use App\Modules\Shared\Support\ApiResponse;

final class CommunicationAttachmentScanStatusController extends Controller
{
    public function __construct(
        private readonly CommunicationAttachmentGovernanceService $communicationAttachmentGovernanceService,
    ) {
    }

    public function update(UpdateCommunicationAttachmentScanStatusRequest $request, string $attachmentId): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('settings.accounts.update'), 403);

        $attachment = CommunicationAttachment::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('id', $attachmentId)
            ->firstOrFail();

        $validated = $request->validated();

        $updated = $this->communicationAttachmentGovernanceService->updateScanStatus(
            attachment: $attachment,
            status: (string) $validated['status'],
            engine: $validated['engine'] ?? null,
            detail: $validated['detail'] ?? null,
            quarantineReason: $validated['quarantineReason'] ?? null,
            actorId: (string) $request->user()->id,
        );

        return ApiResponse::success(
            $this->communicationAttachmentGovernanceService->serializeSummary($updated),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}
