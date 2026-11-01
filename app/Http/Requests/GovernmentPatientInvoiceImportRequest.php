<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GovernmentPatientInvoiceImportRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->exists('dry_run')) {
            $this->merge(['dry_run' => true]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->hasRole('Super-Admin') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'import_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'target_min' => ['required', 'numeric', 'min:0', 'lt:target_max'],
            'target_max' => ['required', 'numeric', 'gt:target_min'],
            'min_invoice_amount' => ['required', 'numeric', 'min:0'],
            'min_user_total' => ['required', 'numeric', 'min:0'],
            'include_driving' => ['nullable', 'boolean'],
            'reuse_excel_profiles' => ['nullable', 'boolean'],
            'include_admission' => ['nullable', 'boolean'],
            'include_excluded_fee_types' => ['nullable', 'boolean'],
            'driving_pick_count' => ['required', 'integer', 'min:0', 'max:1000'],
            'ignore_users' => ['nullable', 'boolean'],
            'ignore_user_ids' => ['nullable', 'string', 'regex:/\A\d+(?:\s*,\s*\d+)*\z/'],
            'ignore_patients' => ['nullable', 'boolean'],
            'ignore_patient_ids' => ['nullable', 'string', 'regex:/\A\d+(?:\s*,\s*\d+)*\z/'],
            'ignore_fee_types' => ['nullable', 'boolean'],
            'ignore_fee_type_ids' => ['nullable', 'string', 'regex:/\A\d+(?:\s*,\s*\d+)*\z/'],
            'dry_run' => ['nullable', 'boolean'],
            'confirm_execute' => ['exclude_unless:dry_run,0', 'required_if:dry_run,0', 'accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'import_file.required' => 'Please select the patient Excel workbook.',
            'import_file.mimes' => 'The workbook must be an Excel file (.xlsx, .xls) or CSV.',
            'import_file.max' => 'The workbook must not be larger than 5MB.',
            'target_min.lt' => 'The minimum target must be less than the maximum target.',
            'target_max.gt' => 'The maximum target must be greater than the minimum target.',
            'confirm_execute.accepted' => 'Confirm the permanent patient, invoice, and test updates before execution.',
        ];
    }
}
