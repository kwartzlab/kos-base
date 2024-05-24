@extends('adminlte::page')

@section('title', 'Member Directory')

@section('content_header')
    <h1>{{ $page_title }}</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-primary card-outline">

	<div class="card-body ">
      <div class="table-responsive">
         <table class="table table-striped" id="data-table">
            <thead><tr>
               <th>Name</th>
               <th>Joined</th>
               <th>Status</th>
               <th>Team(s)</th>
               <th>Profile</th>
            </tr></thead>
            <tbody>
               @foreach($users as $user)
                  <tr>
                     <td>{{ $user->get_name() }}</td>
                     <td nowrap>@isset($user->date_admitted) {{ $user->date_admitted->toDateString() }} @endisset</td>
                     <td>@include('users.status')
                     @php ($roles = $user->roles()->get())
                     @if(count($roles) > 0)
                        @foreach($roles as $role)
                           @php($role_name = $role->role()->first())
                           &nbsp;<span class="badge badge-primary">{{ $role_name->name }}</span>
                        @endforeach
                     @endif
                     <td>
                     @php ($teams = $user->teams()->get())
                     @if(count($teams) > 0)
                        @foreach($teams->unique() as $team)
                           &nbsp;<a href="/teams/{{ $team->id }}" title="View Team Profile"><span class="badge badge-warning badge-team">{{ $team->name }}</span></a>
                        @endforeach
                     @endif

                     </td>

                     <td>
                     <a class="btn btn-primary btn-sm" href="/members/{{ $user->id }}/profile" role="button"><i class="fas fa-user"></i>&nbsp;&nbsp;Profile</a>

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
