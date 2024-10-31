@extends('adminlte::page')

@section('title', 'Report - ' . $report_name)

@section('content_header')
    <h1>Report - {{ $report_name }}</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-outline card-success">
	<div class="card-body">
		<div class="table-responsive">
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
</div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop

@section('js')
    <link href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.1.8/b-3.1.2/b-html5-3.1.2/b-print-3.1.2/datatables.min.css" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.1.8/b-3.1.2/b-html5-3.1.2/b-print-3.1.2/datatables.min.js"></script>

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
                    top2: {
                        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                    }
                },
            });
        });
    </script>
@stop
