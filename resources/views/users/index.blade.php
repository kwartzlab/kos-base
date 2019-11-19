@extends('adminlte::page')

@section('title', 'Membership Register')

@section('content_header')
    <h1>{{ $page_title }}</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="box">
	<div class="box-header">
	<?php /* <a class="btn btn-primary" href="/users/create" role="button">Add Member</a> */ ?>
	<h5>Filter by:&nbsp;&nbsp;&nbsp;<a href="/users/index/applicant" title="Applicants">Applicants</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/users/index/active" title="Active Members">Active Members</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/users/index/hiatus" title="On Hiatus">On Hiatus</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/users/index/inactive" title="Withdrawn Members">Withdrawn Members&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/users/index/all" title="All Members">All</a></a></h5>
	</div>
	
	<div class="box-body ">
	<table class="table table-striped" id="data-table">
		<thead><tr>
			<th>First Name</th>
			<th>Last Name</th>
			<th>Status / Role</th>
			<th>Email</th>
			<th># Keys</th>
			<th>Actions</th>
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
					@foreach($user->roles()->get() as $role)
						@if($role->id>1)
						<span class="label label-primary">{{ $role->name }}</span>&nbsp;
						@endif
					@endforeach
					@if($user->is_superuser())<span class="label label-primary">Superuser</span>@endif
					<td>{{ $user->email }}</td>
					<td>{{ count($user->keys) }}</td>
					<td>
					<a class="btn btn-default btn-sm" href="/users/{{ $user->id }}/edit" role="button">Edit</a>
	
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
					"emptyTable": "No results."
				}				
			});
        });
    </script>
@stop