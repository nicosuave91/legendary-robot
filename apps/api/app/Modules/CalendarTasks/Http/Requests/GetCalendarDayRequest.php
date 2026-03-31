<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class GetCalendarDayRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['date' => ['required', 'date_format:Y-m-d']]; }
}
