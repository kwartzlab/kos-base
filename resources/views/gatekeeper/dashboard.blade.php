@extends('adminlte::page')

@section('title', 'Managing ', $gatekeeper->name)

@section('content_header')
@stop

@section('content')
@include('shared.alerts')

@include('gatekeeper.profile')

<div class="card card-outline card-primary">
	<div class="card-header">
		<h3 class="card-title">Authorizations</h3>
		<div class="card-tools">
	    </div>
	</div>
	
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-striped" id="data-table">
				<thead><tr>
					<th>Name</th>
					<th>Date Authorized</th>
					<th>Actions</th>
				</tr></thead>
				<tbody>
					@foreach($authorizations as $gkauth)
						<tr class="request_row" id="{{ $gkauth->id }}">
							<td>{{ $gkauth->username() }}</td>
							<td>{{ $gkauth->created_at->diffForHumans() }}</td>
							<td>
								<button class="btn btn-danger btn-sm revoke_button" id="btnrevoke{{ $gkauth->id }}" role="button"><i class="fas fa-ban"></i> Revoke</button>
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
				"emptyTable": "No authorizations."
			}				
		});


		$('.revoke_button').click(function(e){
         e.preventDefault();

         // get gatekeeper id for this item
         request_id = $(event.target).closest('.request_row').attr('id');
         row_id = '#' + request_id
         button_id = '#btnrevoke' + request_id

         jQuery.ajax({
            url: "{{ url('/gatekeepers/revoke/') }}" + '/' + request_id,
            method: 'get',
            success: function(result){
               if (result.status == 'success') {
                  $(button_id).replaceWith('<span class="btn btn-success btn-sm"><i class="far fa-check-circle"></i> Removed</span>');
               } else if (result.status == 'error') {
                  $(button_id).replaceWith('<span class="btn btn-danger btn-sm"><i class="fas fa-ban"></i> Error</span>');
               }
               $(row_id).find('td').delay(600).fadeOut('slow', function(here){ 
                  $(row_id).remove();                    
               });    
            }}); 
         });

	});
</script>
@stop