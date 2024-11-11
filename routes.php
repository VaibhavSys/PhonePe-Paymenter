<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\Gateways\PhonePe\PhonePe;

Route::post('/phonepe/webhook', [PhonePe::class, 'webhook'])->name('phonepe.webhook');