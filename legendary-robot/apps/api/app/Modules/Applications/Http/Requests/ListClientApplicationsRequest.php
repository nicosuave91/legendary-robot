<?php

declare(strict_types=1);

namespace App\Modules\Applications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ListClientApplicationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
