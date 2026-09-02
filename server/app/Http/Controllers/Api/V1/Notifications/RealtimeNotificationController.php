<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RealtimeNotificationController extends Controller
{
    /**
     * Stream real-time notifications for the authenticated user via Server-Sent Events (SSE).
     *
     * This is a one-shot SSE response: it sends any pending notifications and closes.
     * The browser will automatically reconnect after `retry` milliseconds.
     * A 30-second retry interval prevents overwhelming the single-threaded PHP dev server.
     */
    public function stream(Request $request): StreamedResponse
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $userId = $user->id;

        // Determine starting ID: from Last-Event-ID header, query param, or current latest ID
        $lastEventId = $request->header('Last-Event-ID') ?: $request->query('last_id');
        if ($lastEventId !== null && is_numeric($lastEventId)) {
            $lastId = (int) $lastEventId;
        } else {
            // For fresh connection, start after the latest existing notification
            $lastId = (int) (Notification::where('user_id', $userId)->max('id') ?? 0);
        }

        $response = new StreamedResponse(function () use ($userId, $lastId, $request) {
            // CRITICAL: Release PHP session lock immediately so concurrent requests execute without delay
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            // Disable PHP output buffering
            if (function_exists('apache_setenv')) {
                apache_setenv('no-gzip', '1');
            }
            ini_set('zlib.output_compression', '0');
            ini_set('implicit_flush', '1');
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // Send initial connection packet.
            // PERFORMANCE FIX: retry set to 30 seconds (was 3 seconds).
            // On a single-threaded PHP dev server, each SSE reconnection blocks all other
            // HTTP requests. 30s reduces server thread occupation by 10x vs 3s.
            echo ": connected\n\n";
            echo "retry: 30000\n\n";
            flush();

            // Fetch any new notifications for this user created after $lastId
            $newNotifications = Notification::where('user_id', $userId)
                ->where('id', '>', $lastId)
                ->orderBy('id', 'asc')
                ->get();

            if ($newNotifications->isNotEmpty()) {
                foreach ($newNotifications as $notification) {
                    $payload = json_encode((new NotificationResource($notification))->toArray($request));
                    echo "id: {$notification->id}\n";
                    echo "event: notification\n";
                    echo "data: {$payload}\n\n";
                }
                flush();
            }

            // Release DB connection immediately so it is available for other requests
            DB::disconnect();
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache, no-transform');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
}
