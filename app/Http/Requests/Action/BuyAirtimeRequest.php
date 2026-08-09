<?php

namespace App\Http\Requests\Action;

use Illuminate\Foundation\Http\FormRequest;

class BuyAirtimeRequest extends FormRequest
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
            'network'  => ['required', 'string', 'in:mtn,airtel,glo,etisalat'],
            'mobileno' => ['required', 'string', 'digits:11'],
            'amount'   => ['required', 'numeric', 'min:50', 'max:5000'],
            'pin'      => ['required', 'string', 'digits:4'],
        ];
    }

    public function messages(): array
    {
        return [
            'network.required'  => 'Please select a network operator.',
            'network.in'        => 'Invalid network selected. Choose MTN, Airtel, Glo, or 9Mobile.',
            'mobileno.required' => 'Please enter the recipient phone number.',
            'mobileno.digits'   => 'Phone number must be exactly 11 digits.',
            'amount.required'   => 'Please enter the recharge amount.',
            'amount.min'        => 'Minimum airtime amount is ₦50.',
            'amount.max'        => 'Maximum airtime amount per transaction is ₦5,000.',
            'pin.required'      => 'Transaction PIN is required.',
            'pin.digits'        => 'PIN must be exactly 4 digits.',
        ];
    }
}
