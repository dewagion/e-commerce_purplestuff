@extends('layouts.admin')

@php
  $heading = 'Edit Produk';
  $subheading = 'Perbarui data produk tanpa mengganggu tampilan toko.';
@endphp

@section('content')
  <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('admin.products._form')
  </form>
@endsection