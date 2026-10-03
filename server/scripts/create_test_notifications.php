<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student2 = App\Models\User::where('email', 'student2@wu.edu.et')->first();
$service = $app->make(App\Domain\Notifications\Services\NotificationService::class);

for ($i = 1; $i <= 3; $i++) {
    $service->send(new App\Domain\Notifications\DTOs\NotificationData(
        userId: $student2->id,
        type: 'item_matched',
        payload: [
            'title' => 'Batch Test Notification ' . $i,
            'message' => 'Test message ' . $i,
            'reference_code' => 'WU-BATCH-' . $i
        ]
    ));
}
echo "CREATED_3_NOTIFICATIONS_FOR_" . $student2->id . "\n";
