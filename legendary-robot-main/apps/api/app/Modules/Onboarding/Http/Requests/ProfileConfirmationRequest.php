<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ProfileConfirmationRequest extends FormRequest
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
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25'],
            'birthday' => ['nullable', 'date'],
            'addressLine1' => ['required', 'string', 'max:255'],
            'addressLine2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'stateCode' => ['required', 'string', 'size:2'],
            'postalCode' => ['required', 'string', 'max:12'],
        ];
    }
}
