@extends('layouts.admin')

@php
  $heading = 'Tambah Produk';
  $subheading = 'Buat produk baru beserta gambar, varian, dan ukuran.';
@endphp

@section('content')
  <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
    @csrf
    @include('admin.products._form')
  </form>
@endsection