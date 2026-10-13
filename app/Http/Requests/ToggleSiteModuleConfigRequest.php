<?php

namespace App\Http\Requests;

use App\Support\BaselineKeys;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        // The baseline is locked: a site can only switch off a module that
        // exists in the Master Baseline Registry (App\Support\BaselineKeys).
        return [
            'module_key' => ['required', 'string', Rule::in(BaselineKeys::MODULE_KEYS)],
            'enabled' => ['required', 'boolean'],
        ];
    }
}
