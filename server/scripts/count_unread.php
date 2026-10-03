<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student2 = App\Models\User::where('email', 'student2@wu.edu.et')->first();
$count = App\Models\Notification::where('user_id', $student2->id)->where('is_read', false)->count();
echo "UNREAD_COUNT:" . $count . "\n";
