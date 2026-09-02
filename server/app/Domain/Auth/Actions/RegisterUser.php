<?php

declare(strict_types=1);

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\RegisterUserData;
use App\Domain\Auth\Services\OtpService;
use App\Jobs\SendRegistrationOtp;
use App\Models\NotificationPreference;
use App\Models\Role;
use App\Models\User;
use App\Models\UserOrganizationalUnit;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterUser
{
    public function __construct(protected OtpService $otpService)
    {}

    public function execute(RegisterUserData $data): array
    {
        return DB::transaction(function () use ($data) {
            $studentRole = Role::where('name', 'student')->first();

            $user = User::create([
                'full_name'     => $data->fullName,
                'university_id' => $data->universityId,
                'email'         => $data->email,
                'password'      => Hash::make($data->password),
                'phone'         => $data->phone,
                'role_id'       => $studentRole?->id ?? Role::firstOrCreate(
                    ['name' => 'student'],
                    ['display_name' => 'Student', 'is_system' => true, 'is_active' => true]
                )->id,
                'language'      => 'en',
                'is_active'     => true,
            ]);

            UserProfile::create([
                'user_id' => $user->id,
            ]);

            NotificationPreference::create([
                'user_id' => $user->id,
            ]);

            if ($data->organizationalUnitId) {
                UserOrganizationalUnit::create([
                    'user_id'                => $user->id,
                    'organizational_unit_id' => $data->organizationalUnitId,
                    'is_primary'             => true,
                ]);
            }

            $verification = $this->otpService->generateOtp($user, 'email_verification');

            SendRegistrationOtp::dispatch($user, $verification->code);

            return [
                'user'              => $user->load(['profile', 'organizationalUnits']),
                'verification_code' => $verification->code,
            ];
        });
    }
}
