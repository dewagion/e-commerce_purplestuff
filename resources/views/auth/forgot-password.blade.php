@extends('layouts.app')

@section('content')
  {{-- Wrapper halaman supaya Auth Modal bisa dibuka pada tab "forgot" --}}
  @php($openAuthModal = true)
  @php($authTab = 'forgot')
  @php($redirect = request('redirect') ?? url()->previous())

  <div class="min-h-[60vh]"></div>
@endsection
