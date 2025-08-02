@extends('layouts.client')

@section('title', 'Laporan Hari Ini')

@section('content')
<div class="p-4 md:p-6">
    {{-- Memuat komponen Livewire Harian yang canggih --}}
    @livewire('laporan.harian')
</div>
@endsection
