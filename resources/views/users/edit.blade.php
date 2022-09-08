@extends('adminlte::page')

@section('title', 'Membership Register - ' . $user->get_name())

@section('content_header')
    <h1>Membership Register</h1>
@stop

@section('content')
@include('shared.alerts')

@include('users.profile')
{{-- Contact Information --}}
<div class="row">
  <section class="col-md-8">
  <div class="card card-outline card-primary">
      <form method="POST" action="/users/{{ $user->id }}" enctype="multipart/form-data" autocomplete="false">
        <div class="card-body">
          {{ method_field('PATCH') }}
          {{ csrf_field() }}

          <div class="row">
            <div class="form-group col-md-6">
              <label for="first_name">Legal First Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="first_name" id="first_name" value="@if(!old('first_name')){{$user->first_name}}@endif{{ old('first_name') }}">
            </div>
            <div class="form-group col-md-6">
              <label for="last_name">Legal Last Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="last_name" id="last_name" value="@if(!old('last_name')){{$user->last_name}}@endif{{ old('last_name') }}">
            </div>
            <div class="form-group col-md-6">
              <label for="first_preferred">Preferred First Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="first_preferred" id="first_preferred" value="@if(!old('first_preferred')){{$user->first_preferred}}@endif{{ old('first_preferred') }}">
            </div>
            <div class="form-group col-md-6">
              <label for="last_preferred">Preferred Last Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="last_preferred" id="last_preferred" value="@if(!old('last_preferred')){{$user->last_preferred}}@endif{{ old('last_preferred') }}">
            </div>
            <div class="form-group col-md-6">
              <label for="pronouns">Preferred Pronouns (leave blank if no preference)</label><input type="text" class="form-control" name="pronouns" id="pronouns" value="@if(!old('pronouns')){{$user->pronouns}}@endif{{ old('pronouns') }}">
            </div>
          </div>

        <h3 class="form-heading">Contact Info</h3>

          <div class="row">
            <div class="form-group col-md-5">
              <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text"><i class="fas fa-envelope"></i></div>
                </div>
                <input type="email" class="form-control" name="email" id="email" value="@if(!old('email')){{$user->email}}@endif{{ old('email') }}">
              </div>
            </div>
            <div class="form-group col-md-5">
              <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text"><i class="fas fa-phone"></i></div>
                </div>
                <input type="text" class="form-control" name="phone" id="phone" value="@if(!old('phone')){{$user->phone}}@endif{{ old('phone') }}">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-8">
              <label for="address">Street Address</label>
              <input type="text" class="form-control" name="address" id="address" value="@if(!old('address')){{$user->address}}@endif{{ old('address') }}">

            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-4">
              <label for="city">City</label>
              <input type="text" class="form-control" name="city" id="city" value="@if(!old('city')){{$user->city}}@endif{{ old('city') }}">
            </div>
            <div class="form-group col-md-4">
              <label for="province">Province</label>
              <input type="text" class="form-control" name="province" id="province" value="@if(!old('province')){{$user->province}}@endif{{ old('province') }}">
            </div>
            <div class="form-group col-md-4">
              <label for="postal">Postal Code</label>
              <input type="text" class="form-control" name="postal" id="postal" value="@if(!old('postal')){{$user->postal}}@endif{{ old('postal') }}">
            </div>
          </div>

        <h3 class="form-heading">Change Password</h3>
        <p><em>Complete both fields below to change the user's password. The user will be immediately logged out of kOS and will need to log back in using their new password.</em></p>
          <div class="row">
            <div class="form-group col-md-6">
            <label for="password">New Password</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <div class="input-group-text"><i class="fas fa-key"></i></div>
              </div>
              <input type="password" class="form-control" name="password" id="password" autocomplete="new-password">

              </div>
            </div>
            <div class="form-group col-md-6">
              <label for="password_confirmation">Confirm Password</label>
              <div class="input-group">
              <div class="input-group-prepend">
                <div class="input-group-text"><i class="fas fa-key"></i></div>
              </div>
              <input type="password" class="form-control" name="password_confirmation" id="password_confirmation">
              </div>
            </div>
          </div>
          <h3 class="form-heading" style="margin-bottom:10px;">User Note</h3>
          <p><i>This note is only visible in the membership register.</i></p>
          
          <div class="row">
            <div class="form-group col-md-12">
              <textarea class="form-control" rows="5" name="notes" id="notes">@if(!old('notes')){{$user->notes}}@endif{{ old('notes') }}</textarea>
            </div>
          </div>

          </div>

          <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>

      </form>
    </div>


    {{-- Keys --}}
    <div class="card card-success card-outline" style="margin-top:25px;">
      <div class="card-header">
        <h3 class="card-title">Manage Keys</h3>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead><tr>
              <th>Key ID</th>
              <th>Description</th>
              <th>Added</th>
              <th>Actions</th>
            </tr></thead>
            <tbody>
              @foreach($user->keys as $key)
                <tr>
                  <td>{{ substr($key->rfid,-8) }}</td>
                  <td>{{ $key->description }}</td>
                  <td>{{ $key->created_at->diffForHumans() }}</td>
                  <td>
                  <a class="btn btn-danger btn-sm" href="/users/{{ $user->id }}/destroy_key/{{ $key->id }}" role="button">Delete</a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

  {{-- Form Submissions --}}
  <div class="card card-warning card-outline" style="margin-top:25px;">
      <div class="card-header">
        <h3 class="card-title">Form Submissions</h3>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped" id="forms-table">
            <thead><tr>
              <th>Form Name</th>
              <th>Submitted</th>
              <th>Actions</th>
            </tr></thead>
            <tbody>
              @forelse ($user->submitted_forms as $form_submission)
              <tr>
                  <td>{{ $form_submission->form_name }}</td>
                  <td>{{ $form_submission->created_at }}</td>
                  <td class="col-action">
                    <a href="/forms/submission/{{ $form_submission->id }}" class="btn btn-success btn-sm" role="button"><i class="far fa-file-alt"></i>View</a>
                  </td>

                </tr>
              @empty
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
</section>
  
  {{-- Status Timeline --}}
  <section class="col-md-4">
    <div class="card card-outline card-primary">

      <div class="card-header">
        <h3 class="card-title" style="font-size:1.2em">Current Status</h3>
        <div class="card-tools">
          <span class="badge badge-{{ $user_status[$user->status]['colour'] }}" style="font-size:1em;">{{ $user_status[$user->status]['name'] }}</span>
        </div>
      </div>

      <div class="card-body" style="text-align:center;">
          <button class="btn btn-primary" id="btn-update-status" data-toggle="modal" data-target="#update-status"><i class="fas fa-user-tag"></i>&nbsp;&nbsp;Update Member Status</button>
      </div>
    </div>

      <div class="timeline">

      <?php 
        $first_active = TRUE; 
        $current_date = NULL;
        $today_watermark = FALSE;
      ?>
      @forelse($user->status_history()->get() as $status)

        @if(($status->created_at >= \Carbon\Carbon::now()) && (!$today_watermark))
          <div>
            <div class="timeline-item bg-warning today-watermark">
              <h3 class="timeline-header"><i class="fas fa-hand-point-right"></i>&nbsp;&nbsp;You are here</h3>
            </div>
          </div>
          <?php $today_watermark = TRUE; ?>
        @endif

        @if($current_date != $status->created_at->format('Y-m-d'))
          <div class="time-label">
            <span class="bg-primary" style="padding:2px 5px;font-weight:normal;">{{ $status->created_at->format('M jS Y') }}</span>
          </div>
          <?php $current_date = $status->created_at->format('Y-m-d'); ?>
        @endif

        <div>
          <i class="fas {{ $user_status[$status->status]['icon'] }} bg-{{ $user_status[$status->status]['colour'] }}"></i>
          <div class="timeline-item">
            @if ($status->updated_by > 0)
              <span class="time invisible">
                <?php /* <a href="#" class="edit-status" title="Edit Status Update"><i class="fas fa-edit"></i></a>&nbsp;&nbsp; */ ?>
                <a href="#" class="delete-status" title="Delete Status Update" data-record-id="{{ $status->id }}" data-toggle="modal" data-target="#confirm-delete-update"><i class="fas fa-times-circle"></i></a>
              </span>
            @endif

            <h3 class="timeline-header" style="font-size:0.9em">
              @switch($status->status)
                @case('active')
                  @if ($first_active)
                    Admitted
                    <?php $first_active = FALSE; ?>
                  @else
                    Reactivated
                  @endif
                  @break
                @case('applicant')
                  Applied
                  @break
                @case('applicant-abandoned')
                  Application Abandoned
                  @break
                @case('applicant-denied')
                  Application Denied
                  @break
                @default
                  {{ $user_status[$status->status]['name'] }}
              @endswitch
              @if ($status->note != NULL)
                <br /><span style="font-style:italic;">{{ $status->note }}</span>
              @endif
            </h3>
          </div>
        </div>
      @empty
      <div>
        <i class="fas fa-times-circle bg-danger"></i>
        <div class="timeline-item">
          <h3 class="timeline-header" style="font-size:0.9em">No Status Updates Found</h3>
        </div>
      </div>
      @endforelse
      @if(!$today_watermark)
        <div>
          <div class="timeline-item bg-warning today-watermark">
            <h3 class="timeline-header"><i class="fas fa-hand-point-right"></i>&nbsp;&nbsp;You are here</h3>
          </div>
        </div>
      @endif
        <div>
          <i class="fas fa-clock bg-gray"></i>
        </div>
      </div>

      {{-- User Flags --}}

      <div class="card card-outline card-primary" style="clear:both;margin-top:65px;">
          <div class="card-header">
            <h3 class="card-title small">User Flags</h3>
          </div>
          <!-- /.card-header -->
          <div class="card-body p-0">
            <ul class="users-list clearfix">
            <table class="table table-striped">
              <tbody>
                @forelse(config('kwartzlabos.user_flags') as $key => $value)
                  <tr>
                    <td><strong>{{ $value }}</strong></td>
                    <td>
                      <label class="switch">
                      <input type="checkbox" class="primary user-flag" data-record-id="{{ $key }}" @if ($user->flags->contains('flag', $key)) checked @endif>
                        <span class="slider round"></span>
                      </label>
                    </td>
                  </tr>
                @empty
                  <tr><td>No user flags.</td></tr>
                @endforelse


              </tbody> 
            </table>
          </div>
      </div>

    </section>
</div>

{{-- Modals --}}


<div class="modal fade" id="update-status" tabindex="-1" role="dialog" aria-labelledby="modal-update-status" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="overlay d-flex justify-content-center align-items-center invisible">
         <i class="fas fa-4x fa-sync fa-spin"></i>
      </div>
      <div class="modal-header">
        <h4 class="modal-title" id="modal-update-status">Update Member Status</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
        <form id="status-form">
        {{ csrf_field() }}
        <div class="modal-body">
            <p>Select the type of status update and it's effective date(s). <ul><li>If date is in the future, the member's current status will automatically change on that date.</li><li>If the date is in the past, it will be added to their timeline.</li><li>If it's the most recent past change on their timeline, their current status will update immediately to match.</li></ul></p>
              <label for="effective_date">Status</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text"><i class="fas fa-user-tag"></i></div>
                </div>
                <select class="form-control" name="status_type" id="status-type">
                  @foreach(config('kwartzlabos.user_status') as $status_type => $row)
                    @if ($status_type != 'applicant')
                      <option value="{{ $status_type }}">{{$row['name']}}</option>
                    @endif
                  @endforeach
                </select>
              </div>
            <div id="status_options" class="row" style="margin-top:15px;">
              <div class="col-md-6" id="effective-date-group">
                <label for="effective_date">Effective Date</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                  </div>
                  <input type="text" class="form-control" name="effective_date" id="effective-date" value="{{ date('Y-m-d') }}">
                </div>
               </div>
               <div class="col-md-6 invisible hiatus-date-field" id="effective-date-ending-group">
                <label for="effective_date_ending">Hiatus End Date</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                  </div>
                  <input type="text" class="form-control" name="effective_date_ending" id="effective-date-ending" value="{{ date('Y-m-d', strtotime("+4 months")) }}">
                </div>
               </div>
            </div>
      </div>
      </form>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary btn-update-status">Save Update</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="confirm-delete-update" tabindex="-1" role="dialog" aria-labelledby="modal-delete-update" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="overlay d-flex justify-content-center align-items-center invisible">
         <i class="fas fa-4x fa-sync fa-spin"></i>
      </div>
      <div class="modal-header">
        <h4 class="modal-title" id="modal-delete-update">Confirm Status Update Removal</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">
        <p>Removing a status update will alter the member's timeline. If this status update was their most recent, their current status may change to reflect the previous status update.</p>
        <p>Do you want to proceed?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger btn-ok">Remove</button>
      </div>
    </div>
  </div>
</div>


{{-- Delete User (applicant only) --}}
@if ($user->status == 'applicant')
<div class="card card-danger card-outline">
    <div class="card-header">
      <h3 class="card-title">Delete User</h3>
    </div>

  <div class="card-body">

    @if($user->id != \Auth::user()->id)

    <form method="POST" action="/users/{{ $user->id }}">

    {{ method_field('DELETE') }}
    {{ csrf_field() }}

    <div class="form-group">
      <p><strong>Warning: this action cannot be undone! Applicant will have to re-register.</strong></p>

      <div class="row">
         <div class="col-md-0.5">
         <label class="switch">
            <input type="checkbox" class="danger" name="confirm">
            <span class="slider round"></span>
         </label>
        </div>
        <div class="col-md-1">
        <strong>Confirm</strong>
        </div>
      </div>

    </div>
    <div class="form-group">
    <button type="submit" class="btn btn-danger" id="delete_user">Delete Permanently</button>
    </div>

    </form>

    @else

    <p>You cannot delete yourself.</p>

    @endif
  </div>

</div>

@endif

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.min.css">
@stop

@section('plugins.Sweetalert2', true)

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
  <script src="/js/jquery.inputmask.bundle.min.js"></script>
   <script>

    $(document).ready(function(){

      $('#forms-table').dataTable({
				ordering: true,
				pagingType: "simple_numbers",
        order: [ 1, "desc" ],
        iDisplayLength: 10,
				"language": {
					"emptyTable": "No submitted forms."
				}				
      });
      
      // allows user flags to be toggled
      $(".user-flag").on("click", function (e) {
        var user_flag = $(this).attr('data-record-id');
        var user_id = "{{ $user->id }}";
        var flag_checkbox = $(this);
        e.preventDefault();

        jQuery.ajax({
          url: "{{ url('/users') }}" + '/' + user_id + '/toggle_flag/' + user_flag,
          method: 'get',
          success: function(result){
            flag_checkbox.prop("checked", !flag_checkbox.prop("checked"));
            return true
          },
        }); 
        return false
      });      

      $( ".timeline-item" ).hover(
        function() {
          $( this ).find( ".time" ).removeClass('invisible');
        }, function() {
          $( this ).find( ".time" ).addClass('invisible');
        }
      );

      $('#effective-date').datepicker({
            format: 'yyyy-mm-dd',
            orientation: 'bottom',
            container: '#effective-date-group',
            autoclose: true
        }).on('changeDate', function (selected) {
          var minDate = new Date(selected.date.valueOf())
          endDateString = minDate.getFullYear() + '-' + ('0' + (minDate.getMonth()+5)).slice(-2) + '-' + ('0' + minDate.getDate()).slice(-2);
          $("#effective-date-ending").val(endDateString);
          $('#effective-date-ending').datepicker('setStartDate', minDate);
          $('#effective-date-ending').datepicker('defaultViewDate', endDateString);
        });

        $('#effective-date-ending').datepicker({
            format: 'yyyy-mm-dd',
            orientation: 'bottom',
            container: '#effective-date-ending-group',
            autoclose: true
        });

      $(document).on('change', '#status-type', function() {
        var status_type = $("#status-type").val()
        if(status_type == 'hiatus') {
          $('.hiatus-date-field').removeClass('invisible')
        } else {
          $('.hiatus-date-field').addClass('invisible')
        }
      });

      $('#update-status').on('click', '.btn-update-status', function(e) {
            var $modalDiv = $(e.delegateTarget);
            var $overlayDiv = $modalDiv.find('.overlay')

            event.preventDefault();
            $overlayDiv.removeClass('invisible')

            $.ajax({
                type:"POST",
                url:"/users/{{ $user->id }}/status",
                data:$("#status-form").serialize(),
                success: function (data, textStatus, oHTTP) {
                  $overlayDiv.addClass('overlay-change').fadeOut(0).fadeIn('fast')
                  $overlayDiv.html('<i class="fas fa-8x fa-check-circle text-success"></i>')
                  window.setTimeout(function(){ 
                      $modalDiv.modal('hide')
                      location.reload(true);
                  }, 1500);
                }
            });
         });

         $('#confirm-delete-update').on('show.bs.modal', function(e) {
            var data = $(e.relatedTarget).data();
            $('.title', this).text(data.recordTitle);
            $('.btn-ok', this).data('recordId', data.recordId);
         });         

         $('#confirm-delete-update').on('click', '.btn-ok', function(e) {
            var $modalDiv = $(e.delegateTarget);
            var $overlayDiv = $modalDiv.find('.overlay')
            var id = $(this).data('recordId');

            event.preventDefault();
            $overlayDiv.removeClass('invisible')

            $.ajax({
                type:"POST",
                url:"/users/{{ $user->id }}/status",
                data:{ status_id: id },
                method: 'DELETE',
                success: function (data, textStatus, oHTTP) {
                  $overlayDiv.addClass('overlay-change').fadeOut(0).fadeIn('fast')
                  $overlayDiv.html('<i class="fas fa-8x fa-check-circle text-success"></i>')
                  window.setTimeout(function(){ 
                      $modalDiv.modal('hide')
                      location.reload(true);
                  }, 1500);
                }
            });
         });

         $.ajaxSetup({
            headers: {
               'Accept': 'application/json',
               'X-CSRF-TOKEN': '{{ csrf_token() }}',
            }
         });
    });

  </script>
@stop

