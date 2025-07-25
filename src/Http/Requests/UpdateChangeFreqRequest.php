<?php

namespace LaravelPlus\Sitemap\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChangeFreqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add your authorization logic here
    }

    public function rules(): array
    {
        return [
            'changefreq' => 'required|in:always,hourly,daily,weekly,monthly,yearly,never',
        ];
    }

    public function messages(): array
    {
        return [
            'changefreq.required' => 'Change frequency is required',
            'changefreq.in' => 'Change frequency must be one of: always, hourly, daily, weekly, monthly, yearly, never',
        ];
    }
} 