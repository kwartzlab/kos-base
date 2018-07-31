
@extends('layout')


@section('content')

<div class="box">
<div class="box-header">
</div>

<div class="box-body no-padding">
<table class="table table-striped">
	<thead><tr>
		<th>Name</th>
		<th>Status</th>
		<th>Authorizations</th>
		<th>Actions</th>
	</tr></thead>
	<tbody>
		
        @if(count($gatekeepers)>0)
            @foreach($gatekeepers as $gatekeeper)
                <tr>
                    <td>{{ $gatekeeper->name }}</td>
                    <td>@if($gatekeeper->status == 'enabled')<span class="label label-success">Enabled</span>
                    @else
                    <span class="label label-danger">Disabled</span>@endif</td>
                    <td>{{ $gatekeeper->count_authorizations() }}</td>
                    <td>
                    <a class="btn btn-default btn-sm" href="/training/{{ $gatekeeper->id }}/edit" role="button">Manage</a>

                    </td>
                </tr>

            @endforeach
        @else
            <tr><td colspan="4" style="text-align:center">You are not a designated trainer for any gatekeeper.</td></tr>
        @endif
	</tbody>
</table>
</div>
</div>
@endsection
