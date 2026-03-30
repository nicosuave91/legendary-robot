<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class IndustrySelectionRequest extends FormRequest
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
            'industry' => ['required', 'in:Legal,Medical,Mortgage'],
        ];
    }
}
