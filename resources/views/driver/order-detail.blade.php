@extends('layouts.driver')

@section('title', 'Detalle de entrega')

@section('content')
    <livewire:driver.order-detail :order-id="(int) request()->route('order')" />
@endsection
