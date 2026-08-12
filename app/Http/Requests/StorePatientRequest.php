<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required',
            'relationship_title' => 'required',
            'father_husband_name' => 'required',
            'mobile' => 'required|regex:/^03\d{2}-\d{7}$/',
            'phone' => 'nullable|string|max:15',

            'age' => 'required|integer|min:0',
            'years_months' => 'required_if:age,!=,null|in:Year(s),Month(s),Day(s)',


            'government_non_gov' => 'required',
            'government_department_id' => 'required_with:government_card_no,designation,sehat_sahulat_visit_no,sehat_sahulat_patient_id',
            'government_card_no' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($this->government_department_id && $this->government_department_id != 95 && empty($value)) {
                        $fail('The government card no field is required.');
                    }
                },
            ],
            'designation' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($this->government_department_id && $this->government_department_id != 95 && empty($value)) {
                        $fail('The designation field is required.');
                    }
                },
            ],
            'sehat_sahulat_visit_no' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($this->government_department_id == 95 && empty($value)) {
                        $fail('The Visit ID is required for Sehat Sahulat Program.');
                    }
                },
            ],
            'sehat_sahulat_patient_id' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($this->government_department_id == 95 && empty($value)) {
                        $fail('The Patient ID is required for Sehat Sahulat Program.');
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'mobile.required' => 'The mobile number is required.',
            'mobile.regex' => 'The mobile number must be in the format 0300-1234567.',
            'department_id.required' => 'Please select the OPD Department.',

            'age.required' => 'The Age field is required.',
            'age.integer' => 'The Age must be an integer.',
            'age.min' => 'The Age must be at least 0.',
            'years_months.required_if' => 'The Years/Months field is required when Age is provided.',
        ];
    }

}
