<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalProducts = Product::count();
        $totalCustomers = User::where('is_admin', false)->count();
        $totalOrders = Order::count();

        $totalRevenue = Order::where('status', '!=', 'Dibatalkan')->sum('grand_total');

        $paidRevenue = Order::where('payment_status', 'paid')
            ->where('status', '!=', 'Dibatalkan')
            ->sum('grand_total');

        $monthRevenue = Order::where('status', '!=', 'Dibatalkan')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('grand_total');

        $pendingPaymentCount = Order::where('payment_status', 'pending')->count();
        $waitingVerificationCount = Order::where('payment_status', 'waiting_verification')->count();
        $processingCount = Order::where('status', 'Diproses')->count();
        $shippingCount = Order::where('status', 'Dikirim')->count();

        $latestOrders = Order::latest()->take(8)->get();

        $salesByDayRaw = Order::selectRaw('DATE(created_at) as sale_date, COUNT(*) as total_orders, SUM(grand_total) as total_revenue')
            ->where('status', '!=', 'Dibatalkan')
            ->whereDate('created_at', '>=', now()->subDays(6)->toDateString())
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $salesByDay = collect(range(0, 6))->map(function ($offset) use ($salesByDayRaw) {
            $date = Carbon::today()->subDays(6 - $offset)->toDateString();
            $row = $salesByDayRaw->get($date);

            return [
                'date' => Carbon::parse($date),
                'orders' => (int) ($row->total_orders ?? 0),
                'revenue' => (int) ($row->total_revenue ?? 0),
            ];
        });

        $maxRevenue = max(1, (int) $salesByDay->max('revenue'));

        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'Dibatalkan')
            ->selectRaw('order_items.product_name, SUM(order_items.qty) as sold_qty, SUM(order_items.line_total) as total_revenue')
            ->groupBy('order_items.product_name')
            ->orderByDesc('sold_qty')
            ->limit(8)
            ->get();

        $salesRecap = $this->buildSalesRecap($request);

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalCustomers',
            'totalOrders',
            'totalRevenue',
            'paidRevenue',
            'monthRevenue',
            'pendingPaymentCount',
            'waitingVerificationCount',
            'processingCount',
            'shippingCount',
            'latestOrders',
            'salesByDay',
            'maxRevenue',
            'topProducts',
            'salesRecap'
        ));
    }

    public function exportRecapPdf(Request $request)
    {
        $salesRecap = $this->buildSalesRecap($request);

        $pdf = Pdf::loadView('admin.recap.pdf', compact('salesRecap'))
            ->setPaper('a4', 'portrait');

        $datePart = $salesRecap['start']->format('Y-m-d') . '-sampai-' . $salesRecap['end']->format('Y-m-d');
        $filename = 'rekap-penjualan-' . $salesRecap['range'] . '-' . $datePart . '.pdf';

        return $pdf->download($filename);
    }

    protected function buildSalesRecap(Request $request): array
    {
        $range = $request->get('range', 'monthly');
        $allowedRanges = ['weekly', 'monthly', 'yearly', 'custom'];

        if (! in_array($range, $allowedRanges, true)) {
            $range = 'monthly';
        }

        $now = Carbon::now();
        $start = null;
        $end = null;
        $label = 'Bulanan';
        $rangeNote = null;

        if ($range === 'custom') {
            [$start, $end] = $this->resolveCustomRecapDates($request);

            if ($start && $end) {
                $label = 'Rentang Tanggal';
                $rangeNote = 'Rekap berdasarkan tanggal mulai dan tanggal akhir yang dipilih.';
            } else {
                $range = 'monthly';
            }
        }

        if (! $start || ! $end) {
            switch ($range) {
                case 'weekly':
                    $label = 'Mingguan';
                    $rangeNote = 'Rekap otomatis untuk minggu berjalan.';
                    $start = $now->copy()->startOfWeek();
                    $end = $now->copy()->endOfWeek();
                    break;

                case 'yearly':
                    $label = 'Tahunan';
                    $rangeNote = 'Rekap otomatis untuk tahun berjalan.';
                    $start = $now->copy()->startOfYear();
                    $end = $now->copy()->endOfYear();
                    break;

                case 'monthly':
                default:
                    $range = 'monthly';
                    $label = 'Bulanan';
                    $rangeNote = 'Rekap otomatis untuk bulan berjalan.';
                    $start = $now->copy()->startOfMonth();
                    $end = $now->copy()->endOfMonth();
                    break;
            }
        }

        $pdfQuery = ['range' => $range];

        if ($range === 'custom') {
            $pdfQuery['start_date'] = $start->toDateString();
            $pdfQuery['end_date'] = $end->toDateString();
        }

        $ordersBase = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'Dibatalkan');

        $orderCount = (clone $ordersBase)->count();
        $revenueTotal = (clone $ordersBase)->sum('grand_total');
        $subtotalTotal = (clone $ordersBase)->sum('subtotal');
        $shippingTotal = (clone $ordersBase)->sum('shipping_cost');
        $paidRevenue = (clone $ordersBase)->where('payment_status', 'paid')->sum('grand_total');
        $unpaidRevenue = max(0, (int) $revenueTotal - (int) $paidRevenue);

        $paidCount = (clone $ordersBase)->where('payment_status', 'paid')->count();
        $pendingCount = (clone $ordersBase)->where('payment_status', 'pending')->count();
        $verificationCount = (clone $ordersBase)->where('payment_status', 'waiting_verification')->count();
        $processedCount = (clone $ordersBase)->where('status', 'Diproses')->count();
        $shippedCount = (clone $ordersBase)->whereIn('status', ['Dikirim', 'Selesai'])->count();
        $completedCount = (clone $ordersBase)->where('status', 'Selesai')->count();

        $itemsSold = 0;
        $topProducts = collect();
        $productDetails = collect();

        if (Schema::hasTable('order_items')) {
            $itemsBase = OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->where('orders.status', '!=', 'Dibatalkan');

            $itemsSold = (int) (clone $itemsBase)->sum('order_items.qty');

            $topProducts = (clone $itemsBase)
                ->selectRaw('order_items.product_name, SUM(order_items.qty) as sold_qty, SUM(order_items.line_total) as total_revenue')
                ->groupBy('order_items.product_name')
                ->orderByDesc('sold_qty')
                ->limit(10)
                ->get();

            $productDetails = (clone $itemsBase)
                ->selectRaw('orders.created_at as order_created_at, orders.order_number, orders.customer_name, orders.payment_status, orders.status, order_items.product_name, order_items.variant, order_items.size, order_items.price, order_items.qty, order_items.line_total')
                ->orderBy('orders.created_at')
                ->orderBy('orders.id')
                ->orderBy('order_items.id')
                ->get();
        }

        $dailyRecap = (clone $ordersBase)
            ->selectRaw('DATE(created_at) as sale_date, COUNT(*) as total_orders, SUM(total_items) as total_items, SUM(subtotal) as subtotal_total, SUM(shipping_cost) as shipping_total, SUM(grand_total) as revenue_total')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();

        $paymentBreakdown = (clone $ordersBase)
            ->selectRaw('payment_status, COUNT(*) as total_orders, SUM(grand_total) as total_revenue')
            ->groupBy('payment_status')
            ->orderBy('payment_status')
            ->get();

        $statusBreakdown = (clone $ordersBase)
            ->selectRaw('status, COUNT(*) as total_orders, SUM(grand_total) as total_revenue')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        return [
            'range' => $range,
            'label' => $label,
            'range_note' => $rangeNote,
            'start' => $start,
            'end' => $end,
            'start_input' => $start->toDateString(),
            'end_input' => $end->toDateString(),
            'pdf_query' => $pdfQuery,
            'order_count' => (int) $orderCount,
            'paid_count' => (int) $paidCount,
            'pending_count' => (int) $pendingCount,
            'verification_count' => (int) $verificationCount,
            'processed_count' => (int) $processedCount,
            'shipped_count' => (int) $shippedCount,
            'completed_count' => (int) $completedCount,
            'items_sold' => (int) $itemsSold,
            'revenue_total' => (int) $revenueTotal,
            'subtotal_total' => (int) $subtotalTotal,
            'shipping_total' => (int) $shippingTotal,
            'paid_revenue' => (int) $paidRevenue,
            'unpaid_revenue' => (int) $unpaidRevenue,
            'average_order' => $orderCount > 0 ? (int) round($revenueTotal / $orderCount) : 0,
            'top_products' => $topProducts,
            'daily_recap' => $dailyRecap,
            'payment_breakdown' => $paymentBreakdown,
            'status_breakdown' => $statusBreakdown,
            'product_details' => $productDetails,
        ];
    }

    protected function resolveCustomRecapDates(Request $request): array
    {
        $startRaw = $request->input('start_date');
        $endRaw = $request->input('end_date');

        if (! $startRaw && ! $endRaw) {
            return [null, null];
        }

        try {
            $start = $startRaw
                ? Carbon::createFromFormat('Y-m-d', $startRaw)->startOfDay()
                : Carbon::createFromFormat('Y-m-d', $endRaw)->startOfDay();

            $end = $endRaw
                ? Carbon::createFromFormat('Y-m-d', $endRaw)->endOfDay()
                : Carbon::createFromFormat('Y-m-d', $startRaw)->endOfDay();
        } catch (\Throwable $e) {
            return [null, null];
        }

        if ($end->lt($start)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end];
    }
}
