<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Events\OrderCreated;
use App\Http\Controllers\WooCommerceWebhookController;
// Webhook route
Route::post('/webhook/wordpress-form', [WebhookController::class, 'handleWordPressForm']);
Route::post('/webhook/woocommerce-order', [WooCommerceWebhookController::class, 'handleWooCommerceOrder']);