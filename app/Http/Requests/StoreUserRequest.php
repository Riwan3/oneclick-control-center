<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreUserRequest extends FormRequest
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
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(User::ROLES)],
            'status' => ['required', Rule::in(User::STATUSES)],

            'site_access_mode' => ['sometimes', Rule::in(['all', 'selected'])],
            'site_ids' => ['sometimes', 'array'],
            'site_ids.*' => ['integer', Rule::exists('sites', 'id')],

            'module_access_mode' => ['sometimes', Rule::in(['all', 'selected'])],
            'module_ids' => ['sometimes', 'array'],
            'module_ids.*' => ['integer', Rule::exists('modules', 'id')],
        ];
    }

    /**
     * SECURITY CORRECTION: a brand-new non-super-admin user must never
     * get implicit "all" access just because the mode was omitted or a
     * caller passed 'all'. super_admin remains full-access always (role
     * drives that, not these fields — see User::effectiveSiteAccess()).
     * For every other role, site/module access mode must be explicitly
     * 'selected' with at least one id — anything else is a 422, not a
     * silent grant of full access.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('role') === 'super_admin') {
                return;
            }

            if ($this->input('site_access_mode') !== 'selected') {
                $validator->errors()->add(
                    'site_access_mode',
                    'Non-Super-Admin users must have explicit selected site access.'
                );
            } elseif (empty($this->input('site_ids', []))) {
                $validator->errors()->add('site_ids', 'At least one site must be selected for this role.');
            }

            if ($this->input('module_access_mode') !== 'selected') {
                $validator->errors()->add(
                    'module_access_mode',
                    'Non-Super-Admin users must have explicit selected module access.'
                );
            } elseif (empty($this->input('module_ids', []))) {
                $validator->errors()->add('module_ids', 'At least one module must be selected for this role.');
            }
        });
    }
}
