<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student2 = App\Models\User::where('email', 'student2@wu.edu.et')->first();
$student = App\Models\User::where('email', 'student@wu.edu.et')->first();

App\Models\Claim::where('item_id', 2)->delete();
App\Models\Notification::whereIn('user_id', [$student2->id, $student->id])->delete();

$item = App\Models\Item::find(2);
if ($item) {
    $item->reporter_id = $student2->id; // User B is the found item reporter
    $item->type = 'found';
    $item->status = 'found_unclaimed'; // Valid status for claim submission
    $item->save();
}

echo "CLEANUP_OK\n";
