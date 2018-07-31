@extends('layout')


@section('content')

<div class="box">
<div class="box-header">
<a class="btn btn-primary" href="/gatekeepers/create" role="button">Add Gatekeeper</a>
</div>

<div class="box-body no-padding">
<table class="table table-striped">
	<thead><tr>
		<th>Name</th>
		<th>Type</th>
		<th>Status</th>
		<th>IP Address</th>
		<th>Description</th>
		<th>Actions</th>
	</tr></thead>
	<tbody>
		@foreach($gatekeepers as $gatekeeper)
			<tr>
				<td>{{ $gatekeeper->name }}</td>
				<td>@if($gatekeeper->type == 'doorway')<span class="label label-primary">Doorway</span>
				@elseif($gatekeeper->type == 'lockout')<span class="label label-primary">Machine Lockout</span>
				@endif&nbsp;&nbsp;&nbsp;
				@if($gatekeeper->is_default == 1)<span class="label label-warning">Default</span>@endif
				
				</td>
				<td>@if($gatekeeper->status == 'enabled')<span class="label label-success">Enabled</span>
				@else
				<span class="label label-danger">Disabled</span>@endif</td>
				<td>{{ $gatekeeper->ip_address }}</td>

				<td>{{ $gatekeeper->description }}</td>
				<td>
				<a class="btn btn-default btn-sm" href="/gatekeepers/{{ $gatekeeper->id }}/edit" role="button">Edit</a>

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