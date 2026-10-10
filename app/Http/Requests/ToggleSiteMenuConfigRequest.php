<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ToggleSiteMenuConfigRequest extends FormRequest
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
        // See ToggleSiteModuleConfigRequest and SiteConfigurationController
        // for why module_key/menu_key are format-validated only, not
        // existence-checked against the baseline registry (which lives
        // only in frontend JS) — including why this also safely covers
        // a parent (non-leaf) menu_key without duplicating the
        // baseline's hierarchy.
        return [
            'module_key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/'],
            'menu_key' => ['required', 'string', 'max:150', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/'],
            'enabled' => ['required', 'boolean'],
        ];
    }
}
