<?php

namespace App\Http\Requests;

use App\Support\BaselineKeys;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        // The baseline is locked: only a leaf menu that exists in the
        // Master Baseline Registry (App\Support\BaselineKeys) can be
        // switched off. A parent menu's state is derived from its children,
        // so it is not configurable on its own.
        return [
            'module_key' => ['required', 'string', Rule::in(BaselineKeys::MODULE_KEYS)],
            'menu_key' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $moduleKey = (string) $this->input('module_key');
                    $known = collect(BaselineKeys::menusForModule($moduleKey))->contains('menu_key', $value);

                    if (! $known) {
                        $fail('This menu is not part of the baseline for the module.');
                    } elseif (! BaselineKeys::isLeaf($moduleKey, $value)) {
                        $fail('A parent menu is derived from its children and cannot be configured directly.');
                    }
                },
            ],
            // The label is what a OneClick site matches the menu on (key names differ per system).
            'menu_label' => ['nullable', 'string', 'max:255'],
            'enabled' => ['required', 'boolean'],
        ];
    }
}
