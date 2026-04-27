<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => $this->sanitizePhone($this->input('phone')),
        ]);
    }

    private function sanitizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $clean = preg_replace('/\D/', '', $phone);

        return $clean !== '' ? $clean : null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^\d{10,11}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Telefone deve conter apenas números e ter entre 10 e 11 dígitos.',
        ];
    }
}
