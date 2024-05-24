
@extends('adminlte::page')

@section('title', 'Forms')

@section('content_header')
    <h1>Forms</h1>
@stop

@section('content')
@include('shared.alerts')


<div class="card card-outline card-success">
	<div class="card-header">
	<a class="btn btn-primary" href="/forms/create" role="button">New Form</a>
		<div class="card-tools">

	    </div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-striped" id="data-table">
				<thead><tr>
					<th>Name</th>
					<th>Description</th>
					<th>Status</th>
					<th>Actions</th>
				</tr></thead>
				<tbody>
					@foreach($forms as $form)
						<tr>
							<td>{{ $form->name }}</td>
							<td>{{ $form->description }}</td>
							<td>@if($form->status == 'enabled')<span class="label label-success">Enabled</span>
							@else
							<span class="label label-danger">Disabled</span>@endif</td>
							<td>
							<a class="btn btn-default btn-sm" href="/form/{{ $form->id }}/edit" role="button">Edit</a>

							</td>
						</tr>

					@endforeach
				</tbody>
			</table>
		</div>
	</div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop

@section('js')
	<script src="/js/datatables.min.js"></script>
	<script>
        $(document).ready(function () {
            $('#data-table').dataTable({
				ordering: false,
				pagingType: "simple_numbers",
				iDisplayLength: 25,
				"language": {
					"emptyTable": "No forms."
				}
			});
        });
    </script>
@stop
