<?php

namespace App\Domain\Auth\Services;

use App\Domain\Auth\DTOs\LoginData;
use App\Domain\Auth\Exceptions\AccountInactiveException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    public function authenticate(LoginData $data): array
    {
        $user = User::with(['profile', 'departments'])->where('email', $data->email)->first();

        if (!$user || !Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (!$user->is_active) {
            throw new AccountInactiveException();
        }

        return [
            'user' => $user,
        ];
    }
}
