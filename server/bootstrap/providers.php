<?php

use App\Providers\AppServiceProvider;
use Barryvdh\DomPDF\ServiceProvider as DompdfServiceProvider;

return [
    AppServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    DompdfServiceProvider::class,
];
