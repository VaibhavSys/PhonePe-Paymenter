<?php

use Illuminate\Support\Facades\Route;
use Paymenter\Extensions\Gateways\PhonePe\PhonePe;

Route::post('/phonepe/webhook', [PhonePe::class, 'webhook'])->name('extensions.gateways.phonepe.webhook');
