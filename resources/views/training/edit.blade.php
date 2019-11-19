@extends('adminlte::page')

@section('title', 'Managing ' . $gatekeeper->name)

@section('content_header')
    <h1>Managing {{ $gatekeeper->name }}</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="box box-success">
  <div class="box-header">
    <h3 class="form-heading">Authorized Users</h3>
    </div>

        <form method="POST" action="/training">
        <input name="gatekeeper_id" type="hidden" value="{{ $gatekeeper->id }}">
        {{ csrf_field() }}
        

        <div class="row" style="padding-left:10px;">
          <div class="form-group col-md-6">
            <div class="input-group col-xs-8">
              <div class="input-group-addon"><i class="fa fa-user"></i></div>
                <select class="form-control" name="user_id">
                  @foreach($user_ids as $key => $value)
                  <option value="{{ $key }}">{{$value}}</option>
                  @endforeach
                </select>
                <span class="input-group-btn">
                  <button type="submit" class="btn btn-primary">Add User</button>
                </span>
           </div>
         </div>
        </div>

        </form>

    <div class="box-body">
    <table class="table table-striped" id="data-table">
      <thead><tr>
        <th>Name</th>
        <th>Added</th>
        <th>Actions</th>
      </tr></thead>
      <tbody>
        @if(count($authorized_users)>0)
                @foreach($authorized_users as $authorized_user)
                    <tr>
                        <td>{{ $authorized_user->username() }}</td>
                        <td>{{ $authorized_user->created_at->diffForHumans() }}</td>
                        <td>
                            <a class="btn btn-danger" href="/training/{{ $gatekeeper->id }}/destroy/{{ $authorized_user->id }}" role="button">Remove</a>
                        </td>
                    </tr>

                @endforeach
        @else
                <tr><td colspan="4" style="text-align:center">No authorizations exist for this gatekeeper.</td></tr>
        @endif
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
				ordering: true,
				pagingType: "simple_numbers",
				iDisplayLength: 25,
				"language": {
					"emptyTable": "No authorized users."
				}				
			});
        });
    </script>
@stop