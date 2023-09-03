<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrawlingRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'method' => 'required|in:GET,POST,PUT,DELETE',
            'url' => 'required|url',
            'payload' => 'nullable|array',
            'options' => 'nullable|array',
        ];
    }
}
