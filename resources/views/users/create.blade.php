@extends('layout')

@section('content')

<div class="box box-primary">

  <div class="box-body" style="font-size:1.2em;">

  <p>This form is intended for use by Kwartzlab members when interviewing new applicants.</p>

  <p>Once the form is submitted, it will be sent to the member’s list so Kwartzlabbers can weigh in on the applicant. If you are interviewing, please leave your feedback on the mailing list as well. There is a minimum 5-day waiting period when applications are held for feedback.</p>
  <p>The applicant will need 5 votes from active Kwartzlab Members to be approved.</p>

    <form method="POST" action="/users" enctype="multipart/form-data">
      {{ csrf_field() }}


      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_members">Interviewing Members (three are required)</label>
          <input type="text" class="form-control" name="int_members" id="int_members" value="{{ old('int_members') }}">

        </div>
      </div>
    

      <h3 class="form-heading">Interview Questions</h3>

      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_q1">How did you hear about Kwartzlab? Were you referred by a member?</label>
          <input type="text" class="form-control" name="int_q1" id="int_q1" value="{{ old('int_q1') }}">
        </div>
      </div>
    
      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_q2">Have you visited Kwartzlab before? If so, any particular events or just Tuesday Open Nights in general?</label>
          <input type="text" class="form-control" name="int_q2" id="int_q2" value="{{ old('int_q2') }}">
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_q3">Can you tell us a bit about yourself?</label>
          <textarea class="form-control" name="int_q3" id="int_q3">{{ old('int_q3') }}</textarea>
        </div>
      </div>


      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_q4">Have you been a member of a volunteer-based organization before?</label>
          <input type="text" class="form-control" name="int_q4" id="int_q4" value="{{ old('int_q4') }}">
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_q5">What sort of projects are you looking to work on at Kwartzlab?</label>
          <input type="text" class="form-control" name="int_q5" id="int_q5" value="{{ old('int_q5') }}">
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_q6">Can you tell us about a project you've worked on in the past?</label>
          <input type="text" class="form-control" name="int_q6" id="int_q6" value="{{ old('int_q6') }}">
        </div>
      </div>


      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_q7">Are you looking to develop any particular skills or tool experience as a Kwartzlab member?</label>
          <input type="text" class="form-control" name="int_q7" id="int_q7" value="{{ old('int_q7') }}">
        </div>
      </div>


      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_q8">Kwartzlab is a member-run organization. As such, we all share some duties, such as clean up and general equipment maintenance. Are you OK with volunteering some of your time (generally 1-2 hours a month) to help keep Kwartzlab up and running?</label>
            <div class="radio">
              <label><input type="radio" name="int_q8" id="int_q8_1" value="yes" checked="">Yes</label>
            </div>
            <div class="radio">
              <label><input type="radio" name="int_q8" id="int_q8_2" value="no" checked="">No</label>
            </div>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_q9">Do you have any special skills in this regard to help out? i.e tool/equipment experience, etc. </label>
          <input type="text" class="form-control" name="int_q9" id="int_q9" value="{{ old('int_q9') }}">
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_q10">There is an annual general meeting you would be expected to attend, where we vote on anything that concerns our space as a whole, like changing rules or electing board members. Are you OK with attending such a meeting?</label>
          <div class="radio">
              <label><input type="radio" name="int_q10" id="int_q10_1" value="yes" checked="">Yes</label>
            </div>
            <div class="radio">
              <label><input type="radio" name="int_q10" id="int_q10_2" value="no" checked="">No</label>
            </div>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_q11">Have you read and agreed to the Kwartzlab Code of Conduct? <a href="" target="_blank">(link)</a></label>
          <div class="radio">
              <label><input type="radio" name="int_q11" id="int_q11_1" value="yes" checked="">Yes</label>
            </div>
            <div class="radio">
              <label><input type="radio" name="int_q11" id="int_q11_2" value="no" checked="">No</label>
            </div>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_q12">Is there anything else you'd like us or the membership to know?</label>
          <input type="text" class="form-control" name="int_q12" id="int_q12" value="{{ old('int_q12') }}">
        </div>
      </div>


      <h3 class="form-heading">Contact Info</h3>
      <div class="row">
        <div class="form-group col-md-3">
          <label for="first_name">First Name</label><input type="text" class="form-control" name="first_name" id="first_name" value="{{ old('first_name') }}">
        </div>
        <div class="form-group col-md-3">
          <label for="last_name">Last Name</label><input type="text" class="form-control" name="last_name" id="last_name" value="{{ old('last_name') }}">
        </div>
      </div>


      <div class="row">
        <div class="form-group col-md-3">
          <label for="email">Email Address</label>
          <div class="input-group">
              <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
            <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}">
          </div>
        </div>
        <div class="form-group col-md-3">
          <label for="phone">Phone Number</label>
          <div class="input-group">
            <div class="input-group-addon"><i class="fa fa-phone"></i></div>
              <input type="text" class="form-control" name="phone" id="phone" value="{{ old('phone') }}">
          </div>
        </div>
      </div>


      <div class="row">
        <div class="form-group col-md-6">
          <label for="address">Street Address</label>
          <input type="text" class="form-control" name="address" id="address" value="{{ old('address') }}">

        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-2">
          <label for="city">City</label>
          <input type="text" class="form-control" name="city" id="city" value="{{ old('city') }}">
        </div>
        <div class="form-group col-md-2">
          <label for="province">Province</label>
          <input type="text" class="form-control" name="province" id="province" value="{{ old('province') }}">
        </div>
        <div class="form-group col-md-2">
          <label for="postal">Postal Code</label>
          <input type="text" class="form-control" name="postal" id="postal" value="{{ old('postal') }}">
        </div>
      </div>


      <div class="row">
        <div class="form-group col-md-6">
          <label for="photo">Applicant Photo (6 MB max size)</label>
          <input type="file" class="form-control" name="photo" id="photo" value="{{ old('photo') }}" />

        </div>
      </div>

      <div class="form-group">
        <button type="submit" class="btn btn-primary">Submit Application</button>
      </div>

   </form>
  </div>
</div>


@endsection