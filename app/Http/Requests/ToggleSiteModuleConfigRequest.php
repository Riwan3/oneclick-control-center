<?php

namespace App\Http\Requests;

use App\Models\Site;
use App\Services\SiteExtensions;
use App\Support\BaselineKeys;
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
        // The baseline is locked: a site can switch off a baseline module
        // (App\Support\BaselineKeys) or an extension — a module outside the
        // baseline that THIS site itself reported (App\Services\SiteExtensions).
        return [
            'module_key' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (in_array($value, BaselineKeys::MODULE_KEYS, true)) {
                        return;
                    }

                    $site = $this->route('site');
                    if (! $site instanceof Site || SiteExtensions::find($site, $value) === null) {
                        $fail('This module is not part of the baseline and was not reported by the site.');
                    }
                },
            ],
            'enabled' => ['required', 'boolean'],
        ];
    }
}
