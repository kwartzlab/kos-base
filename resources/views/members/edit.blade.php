@extends('adminlte::page')

@section('title', $page_title)

@section('content_header')
    <h1>
        {{ $page_title }}
        @if(\Gate::allows('manage-users'))
            &nbsp;&nbsp;<a class="btn btn-primary" style="float:right" href="/users/{{ $user->id }}/edit" role="button"><i class="fas fa-users"></i>&nbsp;&nbsp;View on Membership Register</a>
        @endif
    </h1> 
@stop

@section('content')
@include('shared.alerts')
@include('users.profile')

@if($user->id == \Auth::user()->id)

<div class="card card-outline card-primary">
  <form method="POST" action="/members/{{ $user->id }}" enctype="multipart/form-data" id="profile-form" autocomplete="false">
    <div class="card-body">

        {{ method_field('PATCH') }}
        {{ csrf_field() }}

        <h3 class="form-heading">Edit Contact Info</h3>
        <p><em>Up to date contact information is required for the Membership Register.</em></p>

        <div class="row">
            <div class="form-group col-md-3">
            <div class="input-group">
                <div class="input-group-prepend">
                <div class="input-group-text"><i class="fas fa-envelope"></i></div>
                </div>
                <input type="email" class="form-control" name="email" id="email" value="@if(!old('email')){{$user->email}}@endif{{ old('email') }}">
            </div>
            </div>
            <div class="form-group col-md-3">
            <div class="input-group">
                <div class="input-group-prepend">
                <div class="input-group-text"><i class="fas fa-phone"></i></div>
                </div>
                <input type="text" class="form-control" name="phone" id="phone" value="@if(!old('phone')){{$user->phone}}@endif{{ old('phone') }}">
            </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-6">
            <label for="address">Street Address</label>
            <input type="text" class="form-control" name="address" id="address" value="@if(!old('address')){{$user->address}}@endif{{ old('address') }}">

            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-2">
            <label for="city">City</label>
            <input type="text" class="form-control" name="city" id="city" value="@if(!old('city')){{$user->city}}@endif{{ old('city') }}">
            </div>
            <div class="form-group col-md-2">
            <label for="province">Province</label>
            <input type="text" class="form-control" name="province" id="province" value="@if(!old('province')){{$user->province}}@endif{{ old('province') }}">
            </div>
            <div class="form-group col-md-2">
            <label for="postal">Postal Code</label>
            <input type="text" class="form-control" name="postal" id="postal" value="@if(!old('postal')){{$user->postal}}@endif{{ old('postal') }}">
            </div>
        </div>

        <h3 class="form-heading">Social Media</h3>
        <p><em>Social Media accounts you'd like members to know about.</em></p>

        <div class="row" id="socials">
            <div class="form-group col-md-8">
                <button type="button" class="btn btn-primary btn-sm btn-add-social-account"><i class="fas fa-plus-circle"></i> Add Account</button>&nbsp;
                <div class="input-options" style="margin-top:10px">
                    @if(count($user_socials)>0)
                        @foreach($user_socials as $key => $social)
                            <div class="card card-default card-outline">
                                <div class="card-header">
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool remove-item"><i class="far fa-window-close fa-2x" style="padding-top:12px"></i></button>
                                    </div>
                                    <div class="row">
                                        <select name="socials[{{ $key }}][service]" class="col-md-2 service-type">
                                            @foreach(config('kwartzlabos.socials') as $service => $service_name)
                                            @if($social['service'] == $service)
                                                <option value="{{ $service }}" selected>{{ $service_name }}</option>
                                            @else
                                                <option value="{{ $service }}">{{ $service_name }}</option>
                                            @endif
                                            @endforeach
                                        </select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <input type="text" class="form-control col-md-6 service-profile" name="socials[{{ $key }}][profile]" value="{{ $social['profile'] }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <h3 class="form-heading">Skills & Expertise</h3>
        <p><em>General skills (eg. 3D printing) you'd like members to know about. Add new skills by typing them in and hitting Enter</em></p>

        <div class="row">
            <div class="form-group col-md-8">
            <select class="form-control" multiple="multiple" name="skills[]" id="skills">
                @foreach($existing_skills as $key => $skill)
                    @if(array_search($skill, $user_skills))
                    <option selected>{{ $skill }}</option>
                    @else
                    <option>{{ $skill }}</option>
                    @endif
                @endforeach
                @foreach($user_skills as $key => $skill)
                    @if(!array_search($skill, $existing_skills))
                        <option selected>{{ $skill }}</option>
                    @endif;
                @endforeach
            </select>
            </div>
         </div>

        <h3 class="form-heading">Certifications</h3>
        <p><em>Relevant Certifications (eg. First Aid) you'd like the board to know about. These do not appear in your regular member profile. You may be asked about these if you become a team lead or trainer.</em></p>

        <div class="row" id="certifications">
            <div class="form-group col-md-8">
                <button type="button" class="btn btn-primary btn-sm btn-add-certification"><i class="fas fa-plus-circle"></i> Add Certification</button>&nbsp;
                <div class="row" style="margin-top:10px"><div class="form-group col-md-3"><strong>Type</strong></div><div class="form-group col-md-4"><strong>Name</strong></div><div class="form-group col-md-4"><strong>Expiry Date (if any)</strong></div></div>
                <div class="input-options" style="margin-top:10px">
                    @if(count($user_certs)>0)
                        @foreach($user_certs as $key => $user_cert)
                            <div class="card card-default card-outline">
                                <div class="card-header">
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool remove-item"><i class="far fa-window-close fa-2x" style="padding-top:12px"></i></button>
                                    </div>
                                    <div class="row">
                                        <select name="certs[{{ $key }}][type]" class="col-md-3 cert-type">
                                            @foreach(config('kwartzlabos.certifications') as $cert => $cert_name)
                                                @if($user_cert['type'] == $cert)
                                                    <option value="{{ $cert }}" selected>{{ $cert_name }}</option>
                                                @else
                                                    <option value="{{ $cert }}">{{ $cert_name }}</option>
                                                @endif
                                            @endforeach
                                        </select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <input type="text" class="form-control col-md-4 cert-name" name="certs[{{ $key }}][name]" value="{{ $user_cert['name'] }}">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <input type="text" class="form-control col-md-3 datepicker cert-date" name="certs[{{ $key }}][expiry_date]" value="{{ $user_cert['expiry_date'] }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach?
                    @endif

                </div>
            </div>
        </div>

        <h3 class="form-heading">Change Password</h3>
        <p><em>If you wish to change your password, fill out both fields below. You will be logged out of kOS and will need to log back in using your new password.</em></p>
        <div class="row">
            <div class="form-group col-md-4">
            <label for="password">Password (leave blank if unchanged)</label>
            <div class="input-group">
            <div class="input-group-prepend">
                <div class="input-group-text"><i class="fas fa-key"></i></div>
            </div>
            <input type="password" class="form-control" name="password" id="password" autocomplete="new-password">

            </div>
            </div>
            <div class="form-group col-md-4">
            <label for="password_confirmation">Confirm Password</label>
            <div class="input-group">
            <div class="input-group-prepend">
                <div class="input-group-text"><i class="fas fa-key"></i></div>
            </div>
            <input type="password" class="form-control" name="password_confirmation" id="password_confirmation">
            </div>
            </div>
        </div>

      </div>

      <div class="card-footer">
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>

  </form>
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
                <tr>
                  <td colspan="5">No submitted forms found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>


@endif

@if($user->status == 'applicant')
    @forelse($user->memberapp()->latest()->get() as $submission)
        <?php $skip_fields = ['first_name','last_name','first_preferred','last_preferred','email','phone','address','city','province','postal','photo']; ?>
        @include('forms.submission')
    @empty
    @endforelse
@endif


@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.min.css">
@stop

@section('plugins.Select2', true)
@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="/js/jquery.inputmask.bundle.min.js"></script>
<script>

  $(document).ready(function () {

    $("#phone").inputmask("(999) 999-9999");
    $("#postal").inputmask("A9A 9A9");

    $('#skills').select2({
      placeholder: 'Select skills',
      tags: true,
      allowClear: true,
      multiple: true
    });

    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd'
      });

    $('body').on('focus',".datepicker", function(){
        $(this).datepicker({
            format: 'yyyy-mm-dd',
            orientation: 'bottom',
            autoclose: true
        });
    });    

    $('#forms-table').dataTable({
        ordering: true,
        pagingType: "simple_numbers",
        order: [ 1, "desc" ],
        iDisplayLength: 10,
        "language": {
        "emptyTable": "No items."
        }				
    });

    $("#profile-form").on('click', 'button.btn-add-social-account', function() {
         option_id = create_UUID();
         $(event.target).closest('.row').find('.input-options').append('<div class="card card-default card-outline">\
            <div class="card-header">\
            <div class="card-tools">\
                  <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="far fa-window-close fa-2x" style="padding-top:12px"></i></button>\
            </div>\
            <div class="row">\
              <select name="socials[' + option_id + '][service]" class="col-md-2 service-type">\
              @foreach(config('kwartzlabos.socials') as $service => $service_name)
                <option value="{{ $service }}">{{ $service_name }}</option>\
              @endforeach
              </select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\
              <input type="text" class="form-control col-md-6 service-profile" name="socials[' + option_id + '][profile]" placeholder="Enter your Twitter username">\
            </div>\
            </div>\
         </div>');
      });

      $("#socials").on('change', '.service-type', function() {
          
        if (this.value == 'twitter') {
            placeholder_text = 'Enter your Twitter username'
        } else if (this.value == 'instagram') {
            placeholder_text = 'Enter your Instagram username'
        } else if (this.value == 'facebook') {
            placeholder_text = 'Enter the full link to your Facebook profile'
        } else if (this.value == 'snapchat') {
            placeholder_text = 'Enter your snapchat username'
        } else if (this.value == 'linkedin') {
            placeholder_text = 'Enter your LinkedIn profile ID'
        }
          
          $(event.target).closest('.row').find('.service-profile').attr('placeholder', placeholder_text);  
      });

      $("#profile-form").on('click', 'button.btn-add-certification', function() {
         option_id = create_UUID();
         $(event.target).closest('.row').find('.input-options').append('<div class="card card-default card-outline">\
            <div class="card-header">\
            <div class="card-tools">\
                  <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="far fa-window-close fa-2x" style="padding-top:12px"></i></button>\
            </div>\
            <div class="row">\
              <select name="certs[' + option_id + '][type]" class="col-md-3 cert-type">\
              @foreach(config('kwartzlabos.certifications') as $cert => $cert_name)
                <option value="{{ $cert }}">{{ $cert_name }}</option>\
              @endforeach
              </select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\
              <input type="text" class="form-control col-md-4 cert-name" name="certs[' + option_id + '][name]">\
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\
              <input type="text" class="form-control col-md-3 datepicker cert-date" name="certs[' + option_id + '][expiry_date]">\
            </div>\
            </div>\
         </div>');
      });

  });

    $('body').on('click','.remove-item',function (e) {
        $(this).closest(".card").fadeOut('slow', function(here){ 
            $(this).closest(".card").remove();                    
        });
    });

    function create_UUID(){
        var dt = new Date().getTime();
        var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = (dt + Math.random()*16)%16 | 0;
            dt = Math.floor(dt/16);
            return (c=='x' ? r :(r&0x3|0x8)).toString(16);
        });
        return uuid;
    }

</script>
@stop