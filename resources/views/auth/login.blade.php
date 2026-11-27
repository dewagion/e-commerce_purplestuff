@extends('layouts.app')

@section('content')
  {{-- Halaman ini cuma “wrapper” supaya modal auth muncul --}}
  @php($openAuthModal = true)
  @php($authTab = 'login')
  @php($redirect = request('redirect') ?? url()->previous())

  <div class="min-h-[60vh]"></div>
  @include('partials.auth-modal')
@endsection