<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_type' => ['required', 'string', 'in:item_list,resolution_time,user_activity,claim_summary,audit_export,search_analytics'],
            'format' => ['nullable', 'string', 'in:csv,pdf'],
            'filters' => ['nullable', 'array'],
            'filters.campus_id' => ['nullable', 'integer', 'exists:campuses,id'],
            'filters.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'filters.status' => ['nullable', 'string'],
            'filters.date_from' => ['nullable', 'date'],
            'filters.date_to' => ['nullable', 'date', 'after_or_equal:filters.date_from'],
        ];
    }
}
