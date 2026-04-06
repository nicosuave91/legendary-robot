<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateAccountRequest extends FormRequest
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
            'displayName' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:admin,user'],
            'status' => ['required', 'in:active,deactivated'],
            'firstName' => ['nullable', 'string', 'max:255'],
            'lastName' => ['nullable', 'string', 'max:255'],
        ];
    }
}
