<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ListClientEventsRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'startDate' => ['nullable', 'date_format:Y-m-d'],
            'endDate' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:startDate'],
        ];
    }
}
