@extends('adminlte::page')

@section('title', $team->name . ' - Training')

@section('content_header')
    <h1>{{ $team->name }} - Training</h1>
@stop

@section('content')
@include('shared.alerts')

   @foreach ($gatekeepers as $gatekeeper)
      @php($new_requests = $gatekeeper->training_requests()->where('status', 'new')->get())
      @if (!$new_requests->isEmpty())
      <div class="card card-outline card-primary">
         <div class="card-header">
            <h3 class="card-title">Pending Requests - {{ $gatekeeper->name }}</h3>
            <div class="card-tools">
            </div>
         </div>
         
         <div class="card-body no-padding">
            <div class="table-responsive">
               <table class="table table-striped no-padding">
                  <thead><tr>
                     <th>Name</th>
                     <th>Request Date</th>
                     <th>Training Result</th>
                  </tr></thead>
                  <tbody>
                     @foreach($new_requests as $new_request)
                        @php($request_user = $new_request->user()->first())
                        <tr class="request_row" id="{{ $new_request->id }}">
                           <td>{{ $request_user->get_name() }}</td>
                           <td>{{ $new_request->created_at->diffForHumans() }}</td>
                           <td>
                              <button class="btn btn-success btn-sm pass_button" id="btnpass{{ $new_request->id }}" role="button"><i class="far fa-check-circle"></i> Pass & Authorize</button>&nbsp;&nbsp;
                              <button class="btn btn-danger btn-sm fail_button" id="btnfail{{ $new_request->id }}" role="button"><i class="far fa-frown"></i> Did Not Finish</button>&nbsp;&nbsp;
                              <button class="btn btn-primary btn-sm cancel_button" id="btncancel{{ $new_request->id }}" role="button"><i class="fas fa-ban"></i> Cancel Request</button>
                           </td>
                        </tr>
                     @endforeach
                  </tbody>
               </table>
            </div>
         </div>
      </div>
      @endif
   @endforeach

@php($request_history = Auth::user()->training_requests('history'))
@if (!$request_history->isEmpty())
   <div class="card card-outline card-warning">
      <div class="card-header">
         <h3 class="card-title">Request History</h3>
         <div class="card-tools">
         </div>
      </div>
      
      <div class="card-body no-padding">
         <div class="table-responsive">
            <table class="table table-striped" id="data-table">
               <thead><tr>
                  <th>Name</th>
                  <th>Gatekeeper</th>
                  <th>Status</th>
                  <th>Request Date</th>
                  <th>Last Updated</th>
               </tr></thead>
               <tbody>
                  @foreach($request_history as $new_request)
                  @php($request_user = $new_request->user()->first())
                  @php($request_gk = $new_request->gatekeeper()->first())
                     <tr>
                        <td>{{ $request_user->get_name() }}</td>
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
			ordering: true,
			pagingType: "simple_numbers",
			iDisplayLength: 25,
			"language": {
				"emptyTable": "No gatekeepers."
			}				
		});

         $('.cancel_button').click(function(e){
         e.preventDefault();

         // get gatekeeper id for this item
         request_id = $(event.target).closest('.request_row').attr('id');
         row_id = '#' + request_id
         button_id = '#btncancel' + request_id

         jQuery.ajax({
            url: "{{ url('/teams/training_cancel/') }}" + '/' + request_id,
            method: 'get',
            success: function(result){
               if (result.status == 'success') {
                  $(button_id).replaceWith('<span class="btn btn-success btn-sm"><i class="far fa-check-circle"></i> Cancelled</span>');
               } else if (result.status == 'error') {
                  $(button_id).replaceWith('<span class="btn btn-danger btn-sm"><i class="fas fa-ban"></i> Error</span>');
               }
               $(row_id).find('td').delay(600).fadeOut('slow', function(here){ 
                  $(row_id).remove();                    
               });    
            }}); 
         });

         $('.pass_button').click(function(e){
         e.preventDefault();

         // get gatekeeper id for this item
         request_id = $(event.target).closest('.request_row').attr('id');
         row_id = '#' + request_id
         button_id = '#btnpass' + request_id

         jQuery.ajax({
            url: "{{ url('/teams/training_pass/') }}" + '/' + request_id,
            method: 'get',
            success: function(result){
               if (result.status == 'success') {
                  $(button_id).replaceWith('<span class="btn btn-success btn-sm"><i class="far fa-check-circle"></i> Request Complete</span>');
               } else if (result.status == 'error') {
                  $(button_id).replaceWith('<span class="btn btn-danger btn-sm"><i class="fas fa-ban"></i> Error</span>');
               }
               $(row_id).find('td').delay(600).fadeOut('slow', function(here){ 
                  $(row_id).remove();                    
               });    
            }}); 
         });

         $('.fail_button').click(function(e){
         e.preventDefault();

         // get gatekeeper id for this item
         request_id = $(event.target).closest('.request_row').attr('id');
         row_id = '#' + request_id
         button_id = '#btnfail' + request_id

         jQuery.ajax({
            url: "{{ url('/teams/training_fail/') }}" + '/' + request_id,
            method: 'get',
            success: function(result){
               if (result.status == 'success') {
                  $(button_id).replaceWith('<span class="btn btn-success btn-sm"><i class="far fa-check-circle"></i> Request Complete</span>');
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