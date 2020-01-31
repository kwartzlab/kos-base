@extends('adminlte::page')

@section('title', 'Tools')

@section('content_header')
    <h1>Managed Tools</h1>
@stop

@section('content')
@include('shared.alerts')

@if(count($gatekeepers)>0)
   @foreach($gatekeepers as $gatekeeper)
      @include('gatekeeper.profile')
   @endforeach
@else
   <div class="card card-outline card-warning">
      <div class="card-body">
         <h4>No active tools configured.</h4>
      </div>
   </div>
@endif

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop

@section('js')
@stop