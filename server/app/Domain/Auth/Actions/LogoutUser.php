<?php

namespace App\Domain\Auth\Actions;

use App\Models\User;

class LogoutUser
{
    public function execute(User $user): void
    {
        \Illuminate\Support\Facades\Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
