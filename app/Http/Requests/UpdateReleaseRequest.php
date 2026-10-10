<?php

namespace App\Http\Requests;

use App\Models\Release;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReleaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'version' => ['required', 'string', 'max:50', Rule::unique('releases', 'version')->ignore($this->route('release'))],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(Release::STATUSES)],
            'release_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ];
    }
}
