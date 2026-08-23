<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Wollo University Lost & Found API',
        'status' => 'operational',
        'version' => 'v1',
    ]);
});
