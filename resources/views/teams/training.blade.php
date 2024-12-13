@extends('adminlte::page')

@section('title', 'Request Training')

@section('content_header')
    <h1>Request Training</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-outline card-primary">
	<div class="card-header">
		<div class="card-tools">
	    </div>
	</div>

	<div class="card-body">
            <table class="table table-striped" id="data-table">
                <thead><tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Managing Team</th>
                    <th>Actions</th>
                </tr></thead>
                <tbody>
                @foreach($gatekeepers as $gatekeeper)
                @if(!$gatekeeper->training_requested())
                   <tr>
                        <td>{{ $gatekeeper->name }}</td>
                        <td>{{ $gatekeeper->description }}</td>
                        <td>@php($team = $gatekeeper->team()->first())@if (!empty($team)){{ $team->name }}@endif</td>
                        <td>
                           <button class="btn btn-primary btn-sm request_button" id="{{ $gatekeeper->id }}" role="button"><i class="fas fa-graduation-cap"></i> Send Request</button>
                        </td>
                     </tr>
                  @endif
					@endforeach
				</tbody>
			</table>
	</div>
</div>

@php($new_requests = Auth::user()->training_requests('new'))
@if (!$new_requests->isEmpty())
   <div class="card card-outline card-success">
      <div class="card-header">
         <h3 class="card-title">Pending Requests</h3>
         <div class="card-tools">
         </div>
      </div>

      <div class="card-body no-padding">
         <table class="table table-striped no-padding">
            <thead><tr>
               <th>Name</th>
               <th>Training Description</th>
               <th>Request Date</th>
               <th>Actions</th>
            </tr></thead>
            <tbody>
               @foreach($new_requests as $new_request)
                  @php($request_gk = $new_request->gatekeeper()->first())
                  <tr>
                     <td>{{ $request_gk->name }}</td>
                     <td>{{ $request_gk->description }}</td>
                     <td>{{ $new_request->created_at->diffForHumans() }}</td>
                     <td>
                        <button class="btn btn-primary btn-sm cancel_button" id="{{ $new_request->id }}" role="button"><i class="fas fa-ban"></i> Cancel Request</button>
                     </td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>
   </div>
@endif

@php($request_history = Auth::user()->training_requests('history'))
@if (!$request_history->isEmpty())
   <div class="card card-outline card-warning">
      <div class="card-header">
         <h3 class="card-title">Request History</h3>
         <div class="card-tools">
         </div>
      </div>

      <div class="card-body no-padding">
         <table class="table table-striped no-padding">
            <thead><tr>
               <th>Name</th>
               <th>Status</th>
               <th>Request Date</th>
               <th>Last Updated</th>
            </tr></thead>
            <tbody>
               @foreach($request_history as $new_request)
                  @php($request_gk = $new_request->gatekeeper()->first())
                  <tr>
                     <td>{{ $request_gk->name }}</td>
                     <td>@switch($new_request->status)
                           @case('cancelled')
                              <span class="badge badge-warning">{{ $request_status[$new_request->status] }}</span>
                              @break
                           @case('completed')
                              <span class="badge badge-success">{{ $request_status[$new_request->status] }}</span>
                              @break
                           @case('failed')
                           <span class="badge badge-danger">{{ $request_status[$new_request->status] }}</span>
                           @break
                        @endswitch
                     </td>
                     <td>{{ $new_request->created_at->diffForHumans() }}</td>
                     <td>{{ $new_request->updated_at->diffForHumans() }}</td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>
   </div>
@endif



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

      $('.request_button').click(function(e){
         e.preventDefault();

         // get gatekeeper id for this item
         gatekeeper_id = $(this).attr('id');
         button_id = '#' + gatekeeper_id

         jQuery.ajax({
            url: "{{ url('/teams/training_request/') }}" + '/' + gatekeeper_id,
            method: 'get',
            success: function(result){
               if (result.status == 'success') {
                  $(button_id).replaceWith('<span class="btn btn-success btn-sm"><i class="far fa-check-circle"></i> Requested</span>');
               } else if (result.status == 'error') {
                  $(button_id).replaceWith('<span class="btn btn-danger btn-sm"><i class="fas fa-ban"></i> Error</span>');
               }

               //$(this).html('<b>Success!</b>');

            }});
         });

         $('.cancel_button').click(function(e){
         e.preventDefault();

         // get gatekeeper id for this item
         request_id = $(this).attr('id');
         button_id = '#' + request_id

         jQuery.ajax({
            url: "{{ url('/teams/training_cancel/') }}" + '/' + request_id,
            method: 'get',
            success: function(result){
               if (result.status == 'success') {
                  $(button_id).replaceWith('<span class="btn btn-success btn-sm"><i class="far fa-check-circle"></i> Cancelled</span>');
               } else if (result.status == 'error') {
                  $(button_id).replaceWith('<span class="btn btn-danger btn-sm"><i class="fas fa-ban"></i> Error</span>');
               }

               //$(this).html('<b>Success!</b>');

            }});
         });


   });
</script>
@stop
