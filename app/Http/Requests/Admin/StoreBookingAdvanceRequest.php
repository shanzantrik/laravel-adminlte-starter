<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingAdvanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Allow only authenticated users
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'order_booking_number' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'payment_by' => 'required|string|in:cash,cheque,bank_transfer,card,advance_adjustment',
        ];

        // Add conditional validation rules based on payment type
        switch ($this->input('payment_by')) {
            case 'cash':
                $rules['cash_amount'] = 'required|numeric|min:0';
                break;
            case 'cheque':
                $rules['cheque_number'] = 'required|string|max:50';
                $rules['bank_name'] = 'required|string|max:100';
                $rules['cheque_amount'] = 'required|numeric|min:0';
                break;
            case 'bank_transfer':
                $rules['neft_ref_no'] = 'required|string|max:100';
                $rules['bank_transfer_amount'] = 'required|numeric|min:0';
                $rules['bank_name'] = 'required|string|max:100';
                break;
            case 'card':
                $rules['card_transaction_id'] = 'required|string|max:100';
                $rules['card_amount'] = 'required|numeric|min:0';
                break;
            case 'advance_adjustment':
                $rules['advance_adjustment_ref'] = 'required|string|max:100';
                $rules['adjustment_amount'] = 'required|numeric|min:0';
                break;
        }

        return $rules;
    }
}
