<?php

declare(strict_types=1);

namespace App\Modules\HomepageAnalytics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class DashboardProductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'window' => ['nullable', 'in:7d,30d,90d'],
        ];
    }
}
