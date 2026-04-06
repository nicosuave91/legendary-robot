<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ListEventsRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'startDate' => ['required', 'date_format:Y-m-d'],
            'endDate' => ['required', 'date_format:Y-m-d', 'after_or_equal:startDate'],
            'clientId' => ['nullable', 'string', 'max:255'],
            'ownerUserId' => ['nullable', 'string', 'max:255'],
        ];
    }
}
