@extends('adminlte::page')

@section('title', 'Reports')

@section('content_header')
    <h1>Reports</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-outline card-success">
	<div class="card-body">
                <table class="table table-striped" id="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                                @forelse($reports as $report)
                                        <tr>
                                                <td>{{ $report['name'] }}</td>
                                                <td>{{ $report['description'] ?? '' }}</td>
                                                <td><a class="btn btn-primary btn-sm" href="{{ $report['route'] }}" role="button">Run report</a></td>
                                        </tr>
                                @empty
                                        <tr>
                                            <td>No active reports configured.</td>
                                        </tr>
                                @endforelse
                        </tbody>
                </table>
	</div>
</div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop
