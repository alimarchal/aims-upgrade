<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AdmissionReportRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'period' => ['nullable', 'in:custom,current_month,last_month,current_quarter,last_quarter,current_year,last_year,current_tax_year,last_tax_year'],
            'download_token' => ['nullable', 'alpha_dash', 'max:40'],
            'output' => ['nullable', 'in:screen,pdf,excel'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
            'output.in' => 'Output must be screen, pdf or excel.',
        ];
    }
}
