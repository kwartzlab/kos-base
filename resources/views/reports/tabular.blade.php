@extends('adminlte::page')

@section('title', 'Report - ' . $report_name)

@section('content_header')
    <h1>Report - {{ $report_name }}</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-outline card-success">
	<div class="card-body">
                <table class="table table-striped" id="data-table">
                        <thead>
                            <tr>
                                @foreach($fields as $field)
                                <th>{{ $field['name'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                                @forelse($data as $datum)
                                    <tr>
                                    @foreach($fields as $field)
                                        <td>{{ $field['callback']($datum) }}</td>
                                    @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td>No data to report.</td>
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

@section('js')
    <script>
        $(document).ready(function () {
            $('#data-table').dataTable({
                ordering: true,
                pagingType: "simple_numbers",
                iDisplayLength: {{ config('kwartzlabos.results_per_page.default') }},
                "language": {
                    "emptyTable": "No results???"
                },
                layout: {
                    top: {
                        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                    }
                },
            });
        });
    </script>
@stop
