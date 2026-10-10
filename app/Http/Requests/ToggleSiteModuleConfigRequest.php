<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ToggleSiteModuleConfigRequest extends FormRequest
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
        // module_key is validated as a well-formed kebab-case string
        // only, NOT against a known set of valid baseline keys — the
        // Master Baseline Registry deliberately lives only in frontend
        // JS (see the migration's doc comment), so there is nothing on
        // the backend to validate the key's existence against without
        // duplicating that registry. This was revisited (not just
        // carried forward) during the Site Configuration hardening
        // pass — see SiteConfigurationController's class doc comment
        // for the full "why this is still safe" reasoning (an unknown
        // key's override row is inert by construction, since the
        // frontend resolver only ever iterates real baseline keys).
        return [
            'module_key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/'],
            'enabled' => ['required', 'boolean'],
        ];
    }
}
