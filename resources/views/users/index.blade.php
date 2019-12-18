@extends('adminlte::page')

@section('title', 'Membership Register')

@section('content_header')
    <h3>{{ $page_title }}</h3>
@stop

@section('content')
@include('shared.alerts')


<div class="card card-outline card-success">
	<div class="card-header">
		<h3 class="card-title text-md">Filter by:&nbsp;&nbsp;&nbsp;<a href="/users/index/applicant" title="Applicants">Applicants</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/users/index/active" title="Active Members">Active Members</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/users/index/hiatus" title="On Hiatus">On Hiatus</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/users/index/inactive" title="Withdrawn Members">Withdrawn Members&nbsp;&nbsp;|&nbsp;&nbsp;<a href="/users/index/all" title="All Members">All</a></a></h3>
		<div class="card-tools">


	    </div>
	</div>
	
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-striped" id="data-table">
				<thead><tr>
					<th scope="col">First Name</th>
					<th scope="col">Last Name</th>
					<th scope="col">Status / Role</th>
					<th scope="col">Email</th>
					<th scope="col"># Keys</th>
					<th scope="col">Actions</th>
				</tr></thead>
				<tbody>
					@foreach($users as $user)
						<tr>
							<td>{{ $user->first_name }}</td>
							<td>{{ $user->last_name }}</td>
							<td>@if($user->status == 'active')<span class="badge badge-success">Active</span>
							@elseif($user->status == 'hiatus')<span class="badge badge-warning">On Hiatus</span>
							@elseif($user->status == 'applicant')<span class="badge badge-warning">Applicant</span>
							@else
							<span class="badge badge-danger">Withdrawn</span></td>@endif
							@foreach($user->roles()->get() as $role)
								@if($role->id>1)
								<span class="badge badge-primary">{{ $role->name }}</span>&nbsp;
								@endif
							@endforeach
							@if($user->is_superuser())<span class="badge badge-primary">Superuser</span>@endif
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
					"emptyTable": "No results."
				}				
			});
        });
    </script>
@stop