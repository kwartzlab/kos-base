@extends('adminlte::page')

@section('title', 'Tools')

@section('content_header')
    <h1>Tools</h1>
@stop

@section('content')
@include('shared.alerts')

@php(dd($gatekeepers))

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop

@section('js')
<script>
   $(document).ready(function () {
      $('#data-table').dataTable({
         ordering: false,
         pagingType: "simple_numbers",
         iDisplayLength: 25,
         "language": {
            "emptyTable": "No gatekeepers."
         }				
      });
   });
</script>
@stop