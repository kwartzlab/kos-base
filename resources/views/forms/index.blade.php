@extends('layout')


@section('content')

<div class="box">
<div class="box-header">
<a class="btn btn-primary" href="/forms/create" role="button">Add Form</a>
</div>

<div class="box-body no-padding">
<table class="table table-striped">
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
@endsection

@section('extra_js')
	<script src="/js/datatables.min.js"></script>
	<script>
        $(document).ready(function () {
            $('#data-table').dataTable({
				ordering: false,
				pagingType: "simple_numbers",
				iDisplayLength: 25,
				"language": {
					"emptyTable": "No results."
				}				
			});
        });
    </script>
@stop