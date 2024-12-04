<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone_no' => ['required', 'string', 'size:10', 'regex:/^[0-9]{10}$/', 'unique:customers,phone_no'],
            'vehicle_registration_no' => ['required', 'string', 'max:255', 'unique:customers,vehicle_registration_no'],
        ];
    }
}
