<?php

declare(strict_types=1);

namespace App\Modules\TenantGovernance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateThemeRequest extends FormRequest
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
            'primary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'tertiary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}
