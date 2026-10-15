@extends('layouts.owner')

@section('title', 'Nuevo Pedido')

@section('content')
    <livewire:owner.orders :start-create="true" />
@endsection
