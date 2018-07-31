@extends('layout')

@section('content')

<div class="box">
<div class="box-header">
</div>

<div class="box-body ">
<table class="table table-striped" id="data-table">
	<thead><tr>
		<th>First Name</th>
		<th>Last Name</th>
		<th>Status</th>
		<th>Joined</th>
		<th>Email</th>
		<th>Trainer For</th>
		<th>Profile</th>
	</tr></thead>
	<tbody>
		@foreach($users as $user)
			<tr>
				<td>{{ $user->first_name }}</td>
				<td>{{ $user->last_name }}</td>
				<td>@if($user->status == 'active')<span class="label label-success">Active</span>
				@elseif($user->status == 'hiatus')<span class="label label-warning">On Hiatus</span>
				@elseif($user->status == 'applicant')<span class="label label-warning">Applicant</span>
				@else
				<span class="label label-danger">Withdrawn</span></td>@endif
				@if($user->is_admin())<span class="label label-primary">Admin</span>@endif
				@if($user->is_keyadmin())<span class="label label-primary">Key Admin</span>@endif
				<td>{{ $user->date_admitted }}</td>
				<td>{{ $user->email }}</td>
				<td>
				<?php $trainer_tools = $user->trainer_for(); ?>
				@if ($trainer_tools != NULL)
				@foreach($trainer_tools as $tool)
				<span class="label label-warning"> {{ \App\Gatekeeper::where('id',$tool->gatekeeper_id)->value('name') }}</span>
				@endforeach
				@endif

				</td>
		
				<td>
				<a class="btn btn-default btn-sm" href="/members/{{ $user->id }}/profile" role="button">View</a>

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
				ordering: true,
				pagingType: "simple_numbers",
				iDisplayLength: 25,
				"language": {
					"emptyTable": "No results???"
				}				
			});
        });
    </script>
@stop