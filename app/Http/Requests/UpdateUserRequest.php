<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
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
        // username / NRP is immutable once created — not editable here.
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['nullable', 'string', 'min:8'],
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
     * SECURITY CORRECTION: this only rejects an EXPLICITLY submitted bad
     * value (e.g. site_access_mode: 'all' for a management user, or
     * 'selected' with an empty id list) — omitting these fields entirely
     * is a valid partial update that preserves the user's existing
     * access unchanged, per the "existing partial-update semantics must
     * remain safe" requirement. The controller separately guarantees
     * the same non-super-admin invariant holds for whatever the
     * EFFECTIVE (request-or-preserved) value ends up being, since a
     * role change away from super_admin has no "existing" non-admin
     * access to fall back to.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('role') === 'super_admin') {
                return;
            }

            if ($this->has('site_access_mode')) {
                if ($this->input('site_access_mode') !== 'selected') {
                    $validator->errors()->add(
                        'site_access_mode',
                        'Non-Super-Admin users must use explicit selected site access.'
                    );
                } elseif (empty($this->input('site_ids', []))) {
                    $validator->errors()->add('site_ids', 'At least one site must be selected for this role.');
                }
            }

            if ($this->has('module_access_mode')) {
                if ($this->input('module_access_mode') !== 'selected') {
                    $validator->errors()->add(
                        'module_access_mode',
                        'Non-Super-Admin users must use explicit selected module access.'
                    );
                } elseif (empty($this->input('module_ids', []))) {
                    $validator->errors()->add('module_ids', 'At least one module must be selected for this role.');
                }
            }
        });
    }
}
