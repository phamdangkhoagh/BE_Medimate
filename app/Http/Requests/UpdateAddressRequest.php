<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'ward' => 'sometimes|string',
            'district' => 'sometimes|string',
            'province' => 'sometimes|string',
            'type' => 'sometimes|string',
            'is_default' => 'sometimes|boolean',
            'specific_address' => 'sometimes|string',
            'status' => 'sometimes|in:0,1',
        ];
    }
}
