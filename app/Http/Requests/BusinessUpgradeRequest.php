<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessUpgradeRequest extends FormRequest
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
            "individual_contact_address" => "sometimes|string|required",
            "individual_city" => "sometimes|string|required",
            "individual_lga" => "sometimes|string|required",
            "individual_identity" => "sometimes|mimes:jpg,png,jpeg|required|max:10240",
            "individual_passport" => "sometimes|mimes:jpg,png,jpeg|required|max:5120",
            "ninSlip" => "sometimes|mimes:jpg, png, jpeg",

            /* Organization starts */
            
            "company_name" => "sometimes|string|required",
            "company_email" => "sometimes|string|required",
            "business_year" => "sometimes|numeric|required",
            "company_phone" => "sometimes|string|required",
            "company_address" => "sometimes|string|required",
            "partner_companies" => "sometimes|string|required",
            "business_manager_name" => "sometimes|string|required",
            "business_manager_phone" => "sometimes|string|required",
            "business_manager_contact_address" => "sometimes|string|required",
            "cac_certificate" => "sometimes|mimes:jpg, png, jpeg|required|max:10240",
            "professional_body_certificate" => "sometimes|mimes:jpg, png, jpeg|required|max:10240",
            "product_reg_number" => "sometimes|mimes:jpg, png, jpeg|required|max:10240",
            "tax_paper" => "sometimes|mimes:jpg, png, jpeg|max:10240",
            "manager_identity" => "sometimes|mimes:jpg, png, jpeg|required|max:10240",
            "manager_passport" => "sometimes|mimes:jpg, png, jpeg|required|max:5120",
        ];
    }

    // public function message() {
    //     return [
    //         "product_reg_number.required" => "Product registration number attachment is required",
    //         "product_reg_number.mimes" => "Product registration number attachment must be either jpeg, png and jpg",
    //         "product_reg_number.max" => "Product registration number maximum allowed file upload size is 10MB",
    //     ];
    // }

}
