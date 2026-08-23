<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\RegisterUserData;
use App\Domain\Auth\Services\OtpService;
use App\Jobs\SendRegistrationOtp;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserDepartment;
use App\Support\Enums\AuthVerificationType;
use App\Support\Enums\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterUser
{
    public function __construct(protected OtpService $otpService)
    {}

    public function execute(RegisterUserData $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'full_name' => $data->fullName,
                'university_id' => $data->universityId,
                'email' => $data->email,
                'password' => Hash::make($data->password),
                'phone' => $data->phone,
                'role' => UserRole::STUDENT,
                'language' => 'en',
                'is_active' => true,
            ]);

            UserProfile::create([
                'user_id' => $user->id,
            ]);

            if ($data->departmentId) {
                UserDepartment::create([
                    'user_id' => $user->id,
                    'department_id' => $data->departmentId,
                    'is_primary' => true,
                ]);
            }

            $verification = $this->otpService->generateOtp($user, AuthVerificationType::EMAIL_VERIFICATION->value);

            SendRegistrationOtp::dispatch($user, $verification->code);

            return [
                'user' => $user->load(['profile', 'departments']),
                'verification_code' => $verification->code,
            ];
        });
    }
}
