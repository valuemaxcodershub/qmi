<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompetitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'competition_name' => $this->has('competition_name') ? 'required|string' : 'nullable',
            'competition_description' => $this->has('competition_description') ? 'required|string' : 'nullable',
            'minimum_sales_amount' => $this->has('minimum_sales_amount') ? 'required|numeric|min:0' : 'nullable',
            'competition_start_date' => $this->has('competition_start_date') ? 'required|date' : 'nullable',
            'competition_end_date' => $this->has('competition_end_date') ? 'required|date' : 'nullable',
            'competition_status' => $this->has('competition_status') ? 'required|string' : 'nullable',
            'email_address' => $this->has('email_address') ? 'required|email' : 'nullable',
            'amount' => $this->has('amount') ?'required|numeric|min:1' : 'nullable',
            'competition_id' => $this->has('competition_id') ? 'required|numeric' : 'nullable'
        ];
    }
}
