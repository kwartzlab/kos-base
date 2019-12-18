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
                     <td nowrap>@if($user->status == 'active')<span class="badge badge-success">Active</span>
                     @elseif($user->status == 'hiatus')<span class="badge badge-warning">On Hiatus</span>
                     @elseif($user->status == 'applicant')<span class="badge badge-warning">Applicant</span>
                     @else
                     <span class="badge badge-danger">Withdrawn</span></td>@endif
                     @if($user->is_admin())<span class="badge badge-primary">Admin</span>@endif
                     @if($user->is_keyadmin())<span class="badge badge-primary">Key Admin</span>@endif
                     <td nowrap>{{ $user->date_admitted }}</td>
                     <td>{{ $user->email }}</td>
                     <td>
                     <?php $trainer_tools = $user->trainer_for(); ?>
                     @if ($trainer_tools != NULL)
                     @foreach($trainer_tools as $tool)
                     <span class="badge badge-warning"> {{ \App\Gatekeeper::where('id',$tool->gatekeeper_id)->value('name') }}</span>
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