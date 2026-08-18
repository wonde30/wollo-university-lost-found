<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Schedule Registration
|--------------------------------------------------------------------------
|
| Scheduled commands running periodically via the institutional queue/scheduler.
|
*/

Schedule::command('auth:cleanup-expired')->daily()->at('01:00');
Schedule::command('items:expire-inactive')->daily()->at('02:00');
Schedule::command('items:send-expiry-warnings')->daily()->at('08:00');
Schedule::command('reports:cleanup-expired')->weeklyOn(1, '03:00');
Schedule::command('reports:generate-system')->weeklyOn(1, '04:00');
