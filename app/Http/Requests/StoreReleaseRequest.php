<?php

namespace App\Http\Requests;

use App\Models\Release;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReleaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route-level 'role:super_admin' middleware already gates this.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Free-text version label — deliberately NOT parsed/validated
            // as semantic versioning; the business may label releases
            // however it wants. Unique because this is a formal catalog:
            // two entries claiming the same version would make it
            // ambiguous which one is authoritative.
            'version' => ['required', 'string', 'max:50', Rule::unique('releases', 'version')],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(Release::STATUSES)],
            'release_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ];
    }
}
