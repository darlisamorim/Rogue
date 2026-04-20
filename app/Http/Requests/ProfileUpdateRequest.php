<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\ValidCpf;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:255'],
            'email'              => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'professional_title' => ['nullable', 'string', 'max:120'],
            'phone'              => ['nullable', 'string', 'max:30'],
            'location'           => ['nullable', 'string', 'max:120'],
            'linkedin_url'       => ['nullable', 'string', 'max:255'],
            'website_url'        => ['nullable', 'string', 'max:255'],
            'bio'                => ['nullable', 'string', 'max:600'],
            'date_of_birth'      => ['nullable', 'date'],
            'nationality'        => ['nullable', 'string', 'max:80'],
            'cpf'                => $this->user()->cpf
                ? ['sometimes'] // CPF já cadastrado — ignorado (bloqueado no controller)
                : ['nullable', 'string', 'regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', new ValidCpf(), Rule::unique(User::class)],
        ];
    }
}
