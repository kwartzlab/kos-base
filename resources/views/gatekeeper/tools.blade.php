@extends('adminlte::page')

@section('title', 'Tools')

@section('content_header')
    <h1>Managed Tools</h1>
@stop

@section('content')
@include('shared.alerts')


@forelse($gatekeepers as $gatekeeper)
   @php($link_gatekeeper_name = true)
   @include('gatekeeper.profile')
@empty
   <div class="card card-outline card-warning">
      <div class="card-body">
         <h4>No active tools configured.</h4>
      </div>
   </div>
@endforelse

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop

@section('js')
@stop
