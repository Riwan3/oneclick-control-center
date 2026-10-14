<?php

namespace App\Http\Requests;

use App\Models\Site;
use App\Services\SiteExtensions;
use App\Support\BaselineKeys;
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
        // The baseline is locked. Only a LEAF menu can be switched off (a
        // parent's state is derived from its children), and it must exist
        // either in the baseline module (App\Support\BaselineKeys) or in an
        // extension module that this site itself reported
        // (App\Services\SiteExtensions).
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
            'menu_key' => [
                'required',
                'string',
                'max:150',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $moduleKey = (string) $this->input('module_key');

                    if (in_array($moduleKey, BaselineKeys::MODULE_KEYS, true)) {
                        $known = collect(BaselineKeys::menusForModule($moduleKey))->contains('menu_key', $value);

                        if (! $known) {
                            $fail('This menu is not part of the baseline for the module.');
                        } elseif (! BaselineKeys::isLeaf($moduleKey, $value)) {
                            $fail('A parent menu is derived from its children and cannot be configured directly.');
                        }

                        return;
                    }

                    $site = $this->route('site');
                    $extension = $site instanceof Site ? SiteExtensions::find($site, $moduleKey) : null;

                    if ($extension === null || ! SiteExtensions::hasLeafMenu($extension, $value)) {
                        $fail('This menu was not reported by the site for the module, or it is a parent menu.');
                    }
                },
            ],
            // The label is what a OneClick site matches the menu on (key names differ per system).
            'menu_label' => ['nullable', 'string', 'max:255'],
            'enabled' => ['required', 'boolean'],
        ];
    }
}
