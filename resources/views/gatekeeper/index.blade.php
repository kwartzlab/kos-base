@extends('adminlte::page')

@section('title', 'Gatekeepers')

@section('content_header')
    <h1>Gatekeepers</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-outline card-success">
	<div class="card-header">
	<a class="btn btn-primary" href="/gatekeepers/create" role="button">Add Gatekeeper</a>
		<div class="card-tools">

	    </div>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-striped" id="data-table">
				<thead><tr>
					<th>Name</th>
					<th>Type</th>
					<th>Last Seen</th>
					<th>Team</th>
					<th>Authorizations</th>
					<th>Actions</th>
				</tr></thead>
				<tbody>
					@foreach($gatekeepers as $gatekeeper)
						<tr>
							<td>{{ $gatekeeper->name }}</td>
							<td>
							@if($gatekeeper->type == 'doorway')<span class="badge badge-primary">Doorway</span>
							@elseif($gatekeeper->type == 'lockout')<span class="badge badge-primary">Tool Lockout</span>
							@elseif($gatekeeper->type == 'training')<span class="badge badge-primary">Training Module</span>
							@endif
							@if($gatekeeper->status == 'enabled')<span class="badge badge-success">Enabled</span>
							@else
							<span class="badge badge-danger">Disabled</span> @endif

							<?php /* @if($gatekeeper->is_default == 1)<span class="badge badge-warning">Default</span>@endif */ ?>

							</td>
							<td>@if ($gatekeeper->last_seen != NULL) {{ $gatekeeper->last_seen->diffForHumans() }} @else Never @endif</td>
							<td>@php($team = $gatekeeper->team()->first()) @if ($team != NULL) <a href="/teams/{{ $team->id }}" title="View Team"><span class="badge badge-warning badge-team">{{ $team->name  }}</span></a> @endif</td>
							<td>
								@if($gatekeeper->is_default == 1)
								All Users
								@else
								{{ $gatekeeper->count_authorizations() }}
								@endif
							</td>

							<td class="col-action">
							<a class="btn btn-primary btn-sm" href="/gatekeepers/{{ $gatekeeper->id }}/dashboard" role="button"><i class="fas fa-cog"></i>Manage</a>
							<a class="btn btn-primary btn-sm" href="/gatekeepers/{{ $gatekeeper->id }}/edit" role="button"><i class="fas fa-edit"></i>&nbsp;&nbsp;Edit</a>
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
                        iDisplayLength: {{ config('kwartzlabos.results_per_page.default') }},
			"language": {
				"emptyTable": "No gatekeepers."
			}
		});
	});
</script>
@stop
