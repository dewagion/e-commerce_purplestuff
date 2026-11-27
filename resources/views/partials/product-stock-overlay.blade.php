@php
  $stockValue = (int) ($product['available_stock'] ?? $product['stock'] ?? 0);
  $isUpcoming = !empty($product['is_upcoming']) || (($product['product_status'] ?? null) === 'upcoming');
  $isSoldOut = $isUpcoming || empty($product['in_stock']) || $stockValue <= 0;
@endphp

@if($isSoldOut)
  <div class="absolute inset-0 z-10 bg-black/45 pointer-events-none"></div>
@endif
