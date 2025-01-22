@extends('adminlte::page')

@section('title', 'Membership Register')

@section('content_header')
    <h3>Membership Register -  {{ $user_status[$filter]['name'] }} ({{ count($users) }})</h3>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-outline card-success">
   <div class="card-header">
      <h3 class="card-title text-md">
         Filter by:&nbsp;&nbsp;&nbsp;
         @foreach($user_status as $key => $row)
         <a href="/users/index/{{ $key }}" title="{{ $row['name'] }}">{{ $row['name'] }}</a>&nbsp;&nbsp;|&nbsp;&nbsp;
         @endforeach
         <a href="/users/index/all" title="All">All</a>
   </div>

   <div class="card-body">
      <table class="table table-striped" id="data-table">
         <thead><tr>
            <th scope="col">First Name</th>
            <th scope="col">Last Name</th>
            <th scope="col">Email</th>
            <th scope="col">Status / Role</th>
            <th scope="col">Team(s)</th>
            <th scope="col"># Keys</th>
            <th scope="col">Actions</th>
         </tr></thead>
         <tbody>
            @foreach($users as $user)
               <tr>
                  <td>{{ $user->get_name('first') }}</td>
                  <td>{{ $user->get_name('last') }}</td>
                  <td>{{ $user->email }}</td>
                  <td>@include('users.status')
                  @foreach($user->roles()->get() as $role)
                     @if($role->id>1)
                     <span class="badge badge-primary">{{ $role->name }}</span>&nbsp;
                     @endif
                  @endforeach
                  @if($user->is_superuser())<span class="badge badge-primary">Superuser</span>@endif
                  <td>
                     <?php $teams = $user->teams()->get(); ?>
                     @if(count($teams) > 0)
                        @foreach($teams->unique() as $team)
                           &nbsp;<a href="/teams/{{ $team->id }}" title="View Team Profile"><span class="badge badge-warning badge-team">{{ $team->name }}</span></a>
                        @endforeach
                     @endif
                  </td>
                  <td>{{ count($user->keys) }}</td>
                  <td>
                  <a class="btn btn-primary btn-sm" href="/users/{{ $user->id }}/edit" role="button"><i class="fas fa-edit"></i>&nbsp;&nbsp;Edit</a>

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
              "emptyTable": "No results."
            }
          });
        });

        function modal_do_stuff_success(data) {

        }


    </script>
@stop
