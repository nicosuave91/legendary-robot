<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateTaskStatusRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'targetStatus' => ['required', 'in:open,completed,skipped,blocked'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'blockedReason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
