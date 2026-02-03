@extends('adminlte::page')

@section('title', 'Viewing ' . $gatekeeper->name)

@section('content_header')
@stop

@section('content')
@include('shared.alerts')

@include('gatekeeper.profile')
@include('gatekeeper.authorizations')

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop

@section('js')
<script>
    $(document).ready(function () {
        $('#data-table').dataTable({
            ordering: true,
            pagingType: "simple_numbers",
            iDisplayLength: 10,
            "language": {
                "emptyTable": "No authorized users."
            }
        });
    });
</script>
@stop
