@extends('adminlte::page')

@section('title', 'Manage Teams')

@section('content_header')
    <h1>Manage Teams</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-outline card-success">
	<div class="card-header">
	<a class="btn btn-primary" href="/teams/create" role="button">New Team</a>
		<div class="card-tools">

	    </div>
	</div>
	
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-striped" id="data-table">
				<thead><tr>
					<th>Name</th>
					@foreach(config('kwartzlabos.team_roles') as $team_role => $team_data)
						<th>{{ $team_data['name'] }}s</th>
					@endforeach
					<th>Actions</th>
				</tr></thead>
				<tbody>
					@foreach($teams as $team)
						<tr>
							<td>{{ $team->name }}</td>
							@foreach(config('kwartzlabos.team_roles') as $team_role => $team_data)
								<td>@php($role_members = $team->get_role_members($team_role))@if ($role_members != false) @foreach ($role_members as $role_member)<span class="badge badge-primary">{{ $role_member->user->get_name() }}</span> @endforeach @endif &nbsp;</td>
							@endforeach
							<td>
								<a class="btn btn-default btn-sm" href="/teams/{{ $team->id }}/edit" role="button">Manage</a>
							</td>
						</tr>
			
					@endforeach
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
<script>
	$(document).ready(function () {
		$('#data-table').dataTable({
			ordering: false,
			pagingType: "simple_numbers",
			iDisplayLength: 25,
			"language": {
				"emptyTable": "No teams defined."
			}				
		});
	});
</script>
@stop