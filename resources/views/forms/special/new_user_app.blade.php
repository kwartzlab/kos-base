{{-- Special Form: New User Application --}}

      <h3 class="form-heading">Contact Info</h3>
      
      <div class="row">
         <div class="form-group col-md-3">
            <label for="first_name">First Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @if($errors->has('first_name')) is-invalid @endif" name="first_name" id="first_name" value="{{ old('first_name') }}" required>
         </div>
         <div class="form-group col-md-3">
            <label for="last_name">Last Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @if($errors->has('last_name')) is-invalid @endif" name="last_name" id="last_name" value="{{ old('last_name') }}" required>
         </div>
      </div>
      <div class="row">
         <div class="form-group col-md-3"  style="margin-bottom:0px">
            <label for="first_preferred">Preferred First Name</label>
            <input type="text" class="form-control @if($errors->has('first_preferred')) is-invalid @endif" name="first_preferred" id="first_preferred" value="{{ old('first_preferred') }}">
         </div>
         <div class="form-group col-md-3"  style="margin-bottom:0px">
            <label for="last_preferred">Preferred Last Name</label>
            <input type="text" class="form-control @if($errors->has('last_preferred')) is-invalid @endif" name="last_preferred" id="last_preferred" value="{{ old('last_preferred') }}">
         </div>
      </div>
      <p><i>Leave Preferred Name blank if same as above</i></p>
      <div class="row">
         <div class="form-group col-md-6"  style="margin-bottom:15px">
            <label for="first_preferred">Preferred Pronouns (leave blank if no preference)</label>
            <input type="text" class="form-control @if($errors->has('pronouns')) is-invalid @endif" name="pronouns" id="pronouns" value="{{ old('pronouns') }}">
         </div>
      </div>      

      <div class="row">
         <div class="form-group col-md-3">
         <label for="email">Email Address <span class="text-danger">*</span></label>
            <div class="input-group">
               <div class="input-group-prepend">
               <div class="input-group-text"><i class="fas fa-envelope"></i></div>
               </div>
               <input type="email" class="form-control @if($errors->has('email')) is-invalid @endif" name="email" id="email" value="{{ old('email') }}" required>
            </div>
         </div>
         <div class="form-group col-md-3">
            <label for="phone">Phone Number <span class="text-danger">*</span></label>
            <div class="input-group">
               <div class="input-group-prepend">
               <div class="input-group-text"><i class="fas fa-phone"></i></div>
               </div>
               <input type="text" class="form-control @if($errors->has('phone')) is-invalid @endif" name="phone" id="phone" value="{{ old('phone') }}" required>
            </div>
      </div>
      </div>

      <div class="row">
         <div class="form-group col-md-6">
            <label for="address">Street Address <span class="text-danger">*</span></label>
            <input type="text" class="form-control @if($errors->has('address')) is-invalid @endif" name="address" id="address" value="{{ old('address') }}" required>
         </div>
      </div>

      <div class="row">
         <div class="form-group col-md-2">
            <label for="city">City <span class="text-danger">*</span></label>
            <input type="text" class="form-control @if($errors->has('city')) is-invalid @endif" name="city" id="city" value="{{ old('city') }}" required>
         </div>
         <div class="form-group col-md-2">
            <label for="province">Province <span class="text-danger">*</span></label>
            <input type="text" class="form-control @if($errors->has('province')) is-invalid @endif" name="province" id="province" value="{{ old('province') }}" required>
         </div>
         <div class="form-group col-md-2">
            <label for="postal">Postal Code <span class="text-danger">*</span></label>
            <input type="text" class="form-control @if($errors->has('postal')) is-invalid @endif" name="postal" id="postal" value="{{ old('postal') }}" required>
         </div>
      </div>

      <div class="row">
         <div class="form-group col-md-5">
            <div @if($errors->has('photo')) class="border rounded border-danger border-medium" @endif>
               <label>Applicant Photo <span class="text-danger">*</span></label><br />
               @if (old('photo') == NULL)
                  <button type="button" class="btn btn-primary" id="photoupload"><i class="fas fa-camera"></i>&nbsp;&nbsp;Upload Photo</button>
               @else
                  <button type="button" class="btn btn-success" id="photoupload"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;Photo Uploaded</button>
               @endif
               <input type="hidden" id="photo" name="photo" value="{{ old('photo') }}">
               <input type="hidden" id="user_id" name="user_id" value="{{ old('user_id') }}">
            </div>
         </div>
      </div>

      @component('shared.modal_yesno', [
         'name' => 'returning_user',
      ])

      @slot('title')
      Email Address already in use
      @endslot
      <p>This email address is already in use by the following user:</p>
      <p style="text-align:center;">
         <img class="profile-image img-responsive img-circle user-photo" src="" onerror="this.onerror=null;this.src='{{ asset('img/no-user-photo.png') }}';">
         <br /><h4 style="text-align:center"><span class="user-name" style="font-weight:bold;"></span>&nbsp;&nbsp;<span class="badge user-status badge-large"></span></h4>
      </p>
      <p>Is this user reapplying? If not, a different email address must be used.</p> 
      @endcomponent

      @component('shared.modal_yesno', [
         'name' => 'active_user',
         'buttons' => [
            'yes' => 'OK',
            'no' => NULL
         ]
      ])

      @slot('title')
      Email Address already in use
      @endslot
      <p>This email address is already in use by the following user:</p>
      <p style="text-align:center;">
         <img class="profile-image img-responsive img-circle user-photo" src="" onerror="this.onerror=null;this.src='{{ asset('img/no-user-photo.png') }}';">
         <br /><h4 style="text-align:center"><span class="user-name" style="font-weight:bold;"></span>&nbsp;&nbsp;<span class="badge user-status badge-large"></span></h4>
      </p>
      <p>You cannot use an email address from an active user.</p> 
      @endcomponent

      @component('shared.modal_yesno', [
         'name' => 'matching_name',
      ])

      @slot('title')
      Name matches another user
      @endslot
      <p>This name matches an existing user:</p>
      <p style="text-align:center;">
         <img class="profile-image img-responsive img-circle user-photo" src="" onerror="this.onerror=null;this.src='{{ asset('img/no-user-photo.png') }}';">
         <br /><h4 style="text-align:center"><span class="user-name" style="font-weight:bold;"></span>&nbsp;&nbsp;<span class="badge user-status badge-large"></span></h4>
      </p>
      <p>Is this a different person applying?</p> 
      @endcomponent


@push('js')

<script>
$(document).ready(function(){

   $("#phone").inputmask("(999) 999-9999");
   $("#postal").inputmask("A9A 9A9");

   document.querySelector('#photoupload').onclick = function () {
      var popup = window.open('/image-crop/users/new', '', "width=640, height=790");

      var popupTick = setInterval(function() {
      if (popup.closed) {
      clearInterval(popupTick);
         $.ajax({
            url: "/image/lastupload",
            type: "GET",
            data: "",
            success: function (data, textStatus, oHTTP) {
               if (data.filename != null) {
                  $("#photo").val(data.filename);
                  $("#photoupload").removeClass("btn-primary").addClass("btn-success");
                  $("#photoupload").html('<i class="fas fa-check-circle"></i>&nbsp;&nbsp;Photo Uploaded');
               }
            }
         });

      }
      }, 500);

      return false;
   };         
});

$(document).on('change', '#email', function(e) {
   var email = $(this).val();

   $.ajax({
         type:"POST",
         url:"/users/check_attributes",
         data:{ email: email },
         success: function (data, textStatus, oHTTP) {
            if (data.user_id>0) {
               $('.btn-yes','#modal-returning_user').data('recordId', data.user_id);
               $('.btn-no','#modal-returning_user').data('recordId', data.user_id);
               build_user_profile(data)
               if (data.status === 'active') {
                  $('#modal-active_user').modal('show');
               } else {
                  $('#modal-returning_user').modal('show');
               }
            }
         }
   });

});

function modal_returning_user_yes(data) {
   $('#user_id').val(data.recordId)
}      
   

function modal_returning_user_no(data) {
   $('#email').val(null)
   $('#user_id').val('0')
}      

function modal_active_user_yes(data) {
   $('#email').val(null)
   $('#user_id').val('0')
}      

$(document).on('change', '#first_name', function(e) {
   check_user_name()
});

$(document).on('change', '#last_name', function(e) {
   check_user_name()
});

function check_user_name() {
   var first_name = $('#first_name').val();
   var last_name = $('#last_name').val();

   $.ajax({
         type:"POST",
         url:"/users/check_attributes",
         data:{ first_name: first_name, last_name: last_name },
         success: function (data, textStatus, oHTTP) {
            if (data.user_id>0) {
               $('.btn-yes','#modal-matching_name').data('recordId', data.user_id);
               $('.btn-no','#modal-matching_name').data('recordId', data.user_id);
               build_user_profile(data)
               $('#modal-matching_name').modal('show');
            }
         }
   });

}

function build_user_profile(data) {
   $('.user-name').html(data.name)
   $('.user-photo').attr('src','/storage/images/users/' + data.photo + '-256px.jpeg');
   switch(data.status){
      case 'active':
         $('.user-status').addClass('badge-success')
         $('.user-status').html('{{ config('kwartzlabos.user_status.active.name' )}}')
      break;
      case 'inactive':
         $('.user-status').addClass('badge-danger')
         $('.user-status').html('{{ config('kwartzlabos.user_status.inactive.name' )}}')
      break;
      case 'inactive-abandoned':
         $('.user-status').addClass('badge-danger')
         $('.user-status').html('{{ config('kwartzlabos.user_status.inactive-abandoned.name' )}}')
      break;
      case 'terminated':
         $('.user-status').addClass('badge-danger')
         $('.user-status').html('{{ config('kwartzlabos.user_status.terminated.name' )}}')
      break;
      case 'hiatus':
         $('.user-status').addClass('badge-warning')
         $('.user-status').html('{{ config('kwartzlabos.user_status.hiatus.name' )}}')
      break;
      case 'applicant':
         $('.user-status').addClass('badge-warning')
         $('.user-status').html('{{ config('kwartzlabos.user_status.applicant.name' )}}')
      break;
      case 'applicant-denied':
         $('.user-status').addClass('badge-danger')
         $('.user-status').html('{{ config('kwartzlabos.user_status.applicant-denied.name' )}}')
      break;
      case 'applicant-abandoned':
         $('.user-status').addClass('badge-warning')
         $('.user-status').html('{{ config('kwartzlabos.user_status.applicant-abandoned.name' )}}')
      break;
   }
}

</script>

@endpush