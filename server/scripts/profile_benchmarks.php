<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Notification;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Api\V1\AuthUserResource;
use Illuminate\Http\Request;

echo "=== BENCHMARK SUITE: DATABASE & ENDPOINT PROFILING ===\n\n";

// 1. Password Verification Benchmark
$hashTimeStart = microtime(true);
$password = 'admin@Wollo2026!';
$adminUser = User::where('email', 'admin@wu.edu.et')->first();
if (!$adminUser) {
    echo "Admin user not found!\n";
    exit(1);
}
$dummyHash = Hash::make('testpassword');
$hashTimeEnd = microtime(true);
$hashDurationMs = ($hashTimeEnd - $hashTimeStart) * 1000;

$verifyTimeStart = microtime(true);
$valid = Hash::check($password, $adminUser->password);
$verifyTimeEnd = microtime(true);
$verifyDurationMs = ($verifyTimeEnd - $verifyTimeStart) * 1000;

echo "1. PASSWORD HASHING (BCRYPT_ROUNDS=" . config('hashing.bcrypt.rounds', 12) . "):\n";
echo "   - Hash generation time: " . round($hashDurationMs, 2) . " ms\n";
echo "   - Hash verification time: " . round($verifyDurationMs, 2) . " ms (Valid: " . ($valid ? 'true' : 'false') . ")\n\n";

// 2. Login Flow Query Profiling
DB::flushQueryLog();
DB::enableQueryLog();

$loginStart = microtime(true);

// Replicate LoginController::login logic
$maxAttempts = (int) SystemSetting::get('login_lockout_attempts', 5);
$lockoutMinutes = (int) SystemSetting::get('login_lockout_minutes', 30);

$user = User::with(['profile', 'organizationalUnits', 'role.permissions', 'directPermissions'])
    ->where('email', 'admin@wu.edu.et')
    ->first();

$authResource = new AuthUserResource($user);
$serializedUser = $authResource->toArray(Request::create('/api/v1/auth/login', 'POST'));

$loginEnd = microtime(true);
$loginQueries = DB::getQueryLog();
DB::disableQueryLog();

echo "2. LOGIN FLOW PROFILING (Admin):\n";
echo "   - Total execution time (excluding HTTP transport): " . round(($loginEnd - $loginStart) * 1000, 2) . " ms\n";
echo "   - Total queries executed: " . count($loginQueries) . "\n";
foreach ($loginQueries as $i => $q) {
    echo "     [" . ($i + 1) . "] " . round($q['time'], 2) . "ms : " . $q['query'] . " [" . json_encode($q['bindings']) . "]\n";
}
echo "\n";

// 3. /me Endpoint Query Profiling
DB::flushQueryLog();
DB::enableQueryLog();

$meStart = microtime(true);
$meUser = User::find($adminUser->id)->load(['profile', 'organizationalUnits', 'role.permissions', 'directPermissions']);
$meResource = new AuthUserResource($meUser);
$serializedMe = $meResource->toArray(Request::create('/api/v1/auth/me', 'GET'));
$meEnd = microtime(true);

$meQueries = DB::getQueryLog();
DB::disableQueryLog();

echo "3. /ME ENDPOINT PROFILING:\n";
echo "   - Total execution time: " . round(($meEnd - $meStart) * 1000, 2) . " ms\n";
echo "   - Total queries executed: " . count($meQueries) . "\n";
foreach ($meQueries as $i => $q) {
    echo "     [" . ($i + 1) . "] " . round($q['time'], 2) . "ms : " . $q['query'] . " [" . json_encode($q['bindings']) . "]\n";
}
echo "\n";

// 4. Notification Queries Profiling
DB::flushQueryLog();
DB::enableQueryLog();

$notifStart = microtime(true);
$userId = $adminUser->id;

// Unread count query
$unreadCount = Notification::where('user_id', $userId)->where('is_read', false)->count();

// Notification index query
$notifications = Notification::where('user_id', $userId)
    ->orderByDesc('id')
    ->paginate(20);

// SSE incremental query
$latestId = Notification::where('user_id', $userId)->max('id') ?? 0;
$newNotifs = Notification::where('user_id', $userId)
    ->where('id', '>', $latestId)
    ->orderBy('id', 'asc')
    ->get();

$notifEnd = microtime(true);
$notifQueries = DB::getQueryLog();
DB::disableQueryLog();

echo "4. NOTIFICATION QUERIES PROFILING:\n";
echo "   - Total execution time: " . round(($notifEnd - $notifStart) * 1000, 2) . " ms\n";
echo "   - Total queries executed: " . count($notifQueries) . "\n";
foreach ($notifQueries as $i => $q) {
    echo "     [" . ($i + 1) . "] " . round($q['time'], 2) . "ms : " . $q['query'] . " [" . json_encode($q['bindings']) . "]\n";
}
echo "\n";

// 5. Admin Dashboard Statistics Query Profiling
DB::flushQueryLog();
DB::enableQueryLog();

$dashStart = microtime(true);
$dashController = app(\App\Http\Controllers\Api\V1\Admin\DashboardController::class);
$dashResponse = $dashController->statistics(Request::create('/api/v1/admin/dashboard/statistics?period=90d', 'GET'));
$dashEnd = microtime(true);

$dashQueries = DB::getQueryLog();
DB::disableQueryLog();

echo "5. ADMIN DASHBOARD STATISTICS PROFILING:\n";
echo "   - Total execution time: " . round(($dashEnd - $dashStart) * 1000, 2) . " ms\n";
echo "   - Total queries executed: " . count($dashQueries) . "\n";
$slowQueries = array_filter($dashQueries, fn($q) => $q['time'] > 5.0);
echo "   - Queries > 5ms: " . count($slowQueries) . "\n";
foreach (array_slice($dashQueries, 0, 10) as $i => $q) {
    echo "     [" . ($i + 1) . "] " . round($q['time'], 2) . "ms : " . substr($q['query'], 0, 120) . "...\n";
}
if (count($dashQueries) > 10) {
    echo "     ... (" . (count($dashQueries) - 10) . " more queries)\n";
}
echo "\n";

// 6. Check Indexes on notifications table
echo "6. NOTIFICATIONS TABLE INDEXES:\n";
$indexes = DB::select("SHOW INDEX FROM notifications");
foreach ($indexes as $idx) {
    echo "   - Key_name: " . $idx->Key_name . " | Column: " . $idx->Column_name . " | Seq: " . $idx->Seq_in_index . " | Non_unique: " . $idx->Non_unique . "\n";
}
echo "\n";
