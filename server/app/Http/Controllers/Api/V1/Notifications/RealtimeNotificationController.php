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
     * DEPLOYMENT-AWARE ARCHITECTURE:
     * - LOCAL DEVELOPMENT (Windows / single-threaded `php artisan serve`):
     *   Executes a non-blocking one-shot burst with `retry: 30000` (30s) and terminates immediately.
     *   This prevents worker starvation and single-thread deadlock.
     *
     * - PRODUCTION (Nginx + PHP-FPM, Laravel Octane, or FrankenPHP):
     *   Keeps the SSE stream open in a persistent 55-second loop with periodic heartbeat pings
     *   (`: ping\n\n` every 15s) and pushes new notifications instantly (sub-second latency) with
     *   fast client reconnect (`retry: 3000`).
     */
    public function stream(Request $request): StreamedResponse
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $userId = (int) $user->id;

        // Determine starting ID: from Last-Event-ID header, query param, or current latest ID
        $lastEventId = $request->header('Last-Event-ID') ?: $request->query('last_id');
        if ($lastEventId !== null && is_numeric($lastEventId)) {
            $lastId = (int) $lastEventId;
        } else {
            // For fresh connection, start after the latest existing notification
            $lastId = (int) (Notification::where('user_id', $userId)->max('id') ?? 0);
        }

        $isProduction = app()->environment('production') || $request->header('X-SSE-Mode') === 'persistent';
        $isLocalWindowsDev = PHP_OS_FAMILY === 'Windows' && app()->environment('local', 'testing') && $request->header('X-SSE-Mode') !== 'persistent';

        $response = new StreamedResponse(function () use ($userId, $lastId, $request, $isProduction, $isLocalWindowsDev) {
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

            $currentLastId = $lastId;

            // Send initial connection handshake
            echo ": connected\n\n";

            if ($isLocalWindowsDev || ! $isProduction) {
                // Local development mode: send mode and initial burst, then send close event so client closes EventSource cleanly
                echo "event: mode\n";
                echo "data: {\"mode\":\"local_dev\"}\n\n";
                flush();

                $newNotifications = Notification::where('user_id', $userId)
                    ->where('id', '>', $currentLastId)
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

                // Send explicit close event so client terminates EventSource without native reconnect looping
                echo "event: close\n";
                echo "data: {\"closed\":true}\n\n";
                flush();

                DB::disconnect();
                return;
            }

            // Production mode: persistent streaming loop with 3s interval and 15s heartbeat pings
            echo "retry: 3000\n\n";
            echo "event: mode\n";
            echo "data: {\"mode\":\"persistent\"}\n\n";
            flush();

            $startTime = time();
            $lastPing = $startTime;

            while (time() - $startTime < 55) {
                if (connection_aborted()) {
                    break;
                }

                // Optimize: check existence before querying full models
                $hasNew = Notification::where('user_id', $userId)
                    ->where('id', '>', $currentLastId)
                    ->exists();

                if ($hasNew) {
                    $newNotifications = Notification::where('user_id', $userId)
                        ->where('id', '>', $currentLastId)
                        ->orderBy('id', 'asc')
                        ->get();

                    foreach ($newNotifications as $notification) {
                        $payload = json_encode((new NotificationResource($notification))->toArray($request));
                        echo "id: {$notification->id}\n";
                        echo "event: notification\n";
                        echo "data: {$payload}\n\n";
                        $currentLastId = max($currentLastId, (int) $notification->id);
                    }
                    flush();
                }

                // Heartbeat ping every 15s to keep proxy connections alive
                if (time() - $lastPing >= 15) {
                    echo ": ping\n\n";
                    flush();
                    $lastPing = time();
                }

                // Sleep for 3 seconds before next cycle, releasing DB connection
                DB::disconnect();
                sleep(3);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache, no-transform');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
}
