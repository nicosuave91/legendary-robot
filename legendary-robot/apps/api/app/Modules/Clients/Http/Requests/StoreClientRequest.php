<?php

declare(strict_types=1);

namespace App\Modules\Clients\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'displayName' => ['required', 'string', 'max:255'],
            'firstName' => ['nullable', 'string', 'max:255'],
            'lastName' => ['nullable', 'string', 'max:255'],
            'companyName' => ['nullable', 'string', 'max:255'],
            'primaryEmail' => ['nullable', 'email', 'max:255'],
            'primaryPhone' => ['nullable', 'string', 'max:50'],
            'preferredContactChannel' => ['nullable', 'in:email,sms,phone'],
            'dateOfBirth' => ['nullable', 'date'],
            'ownerUserId' => ['nullable', 'string', 'max:255'],
            'addressLine1' => ['nullable', 'string', 'max:255'],
            'addressLine2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'stateCode' => ['nullable', 'string', 'max:2'],
            'postalCode' => ['nullable', 'string', 'max:20'],
        ];
    }
}
