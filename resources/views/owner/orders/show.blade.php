@extends('layouts.owner')

@section('title', 'Detalle del Pedido')

@section('content')
    <livewire:owner.orders :order-id="(int) request()->route('order') ?: null" />
@endsection
