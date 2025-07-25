<?php

namespace LaravelPlus\Sitemap\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePriorityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add your authorization logic here
    }

    public function rules(): array
    {
        return [
            'priority' => 'required|numeric|min:0|max:1',
        ];
    }

    public function messages(): array
    {
        return [
            'priority.required' => 'Priority is required',
            'priority.numeric' => 'Priority must be a number',
            'priority.min' => 'Priority must be at least 0',
            'priority.max' => 'Priority must be at most 1',
        ];
    }
} 