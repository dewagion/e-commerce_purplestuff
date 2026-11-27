<?php

namespace App\Http\Controllers;

use App\Services\OrderPaymentExpiryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request, OrderPaymentExpiryService $expiryService): View
    {
        $expiryService->expireDuePendingOrders(50);

        $orders = $request->user()
            ->orders()
            ->with(['items', 'trackingHistories'])
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }
}
