<?php

declare(strict_types=1);

namespace App\Modules\TenantGovernance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreIndustryConfigurationRequest extends FormRequest
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
            'status' => ['required', 'in:draft,published'],
            'activate' => ['nullable', 'boolean'],
            'capabilities' => ['required', 'array', 'min:1'],
            'capabilities.*' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
