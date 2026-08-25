<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        ];
    }
}
