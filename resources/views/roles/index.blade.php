@extends('adminlte::page')

@section('title', 'User Roles')

@section('content_header')
    <h1>User Roles</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card">
	<div class="card-header">
		<a class="btn btn-primary" href="/roles/create" role="button">Add User Role</a>
	</div>

	<div class="card-body">
                <table class="table table-striped" id="data-table">
                        <thead><tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Permissions</th>
                                <th>Actions</th>
                        </tr></thead>
                        <tbody>
                                @foreach($roles as $role)
                                        <tr>
                                                <td>{{ $role->name }}</td>
                                                <td>{{ $role->description }}</td>
                                                <td>
                                                        @php($permissions = $role->permissions()->get())
                                                        @foreach($permissions as $permission)
                                                        <span class="badge badge-primary">[{{ $permission->object }}] {{ $permission->operation }}</span>&nbsp;&nbsp;

                                                        @endforeach
                                                        @if($role->id == 1)
                                                        <span class="badge badge-primary">All Permissions</span>&nbsp;&nbsp;
                                                        @endif

                                                </td>
                                                <td style="min-width:95px;">
                                                        <a class="btn btn-primary btn-sm" href="/roles/{{ $role->id }}/edit" role="button"><i class="fas fa-user-tag"></i>&nbsp;&nbsp;Manage</a>&nbsp;
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
<script>
	$(document).ready(function () {
		$('#data-table').dataTable({
			ordering: false,
			pagingType: "simple_numbers",
                        iDisplayLength: {{ config('kwartzlabos.results_per_page.default') }},
			"language": {
				"emptyTable": "No roles."
			}
		});
	});
</script>
@stop
