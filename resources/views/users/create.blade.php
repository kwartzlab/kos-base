@extends('adminlte::page')

@section('title', 'Membership Application')

@section('content_header')
    <h1>Membership Application</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-primary">

  <div class="card-body" style="font-size:1.2em;">

  <p>This form is intended for use by Kwartzlab members when interviewing new applicants.</p>
  <p>Once the form is submitted, it will be sent to the member’s list so Kwartzlabbers can weigh in on the applicant. <strong>If you are interviewing, please leave your feedback on the mailing list as well.</strong></p>

  <p><strong>Let the applicant know:</strong>
  <ul>
  <li>Following the interview, the completed form will be sent to the membership to review. Personal information like the mailing address and phone number are only visible to the Board of Directors, but their email address and answers will be sent to the member's list.</li>
  <li>There is a minimum 5-day waiting period when applications are held for feedback. Typically, the membership coordinator will get back to you by Monday to let you know if your application has been approved, or present options in case it hasn't (such as attend another TON, etc) </li>
  <li>Members are able to give endorsements, and may also voice any concerns regarding applicants. Avoid mentioning the five-endorsement threshold.</li>
  <li>Applicants must provide payment within 4 weeks of acceptance, otherwise their acceptance expires, and they must re-interview if they wish to pursue membership.</li>
  </ul>
  </p>
  <p>&nbsp;</p>

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
          <label for="int_q3">Can you tell us a bit about yourself? (Most applicants talk about where they work, what they study, if they grew up elsewhere etc.)</label>
          <textarea class="form-control" name="int_q3" id="int_q3">{{ old('int_q3') }}</textarea>
        </div>
      </div>


      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_q4">Have you been a member of an organization, club or association like Kwartzlab before?</label>
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
          <input type="text" class="form-control" name="int_q8" id="int_q8" value="{{ old('int_q8') }}">
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
          <input type="text" class="form-control" name="int_q10" id="int_q10" value="{{ old('int_q10') }}">
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_q13">Kwartzlab members are responsible for their health and safety at all times - including observing all safety and training requirements for tools and other equipment. It is the responsibility of all Members and Guests to maintain and promote this culture in their own usage, as well as addressing inappropriate or unsafe use of tools by others. If you see another person operating tools in an unsafe or inappropriate manner, you are expected to address the issue. If you are not comfortable speaking with the person directly, you need to raise the issue immediately with the Team for that tool/area or the Board of Directors. Do you agree?</label>
          <input type="text" class="form-control" name="int_q13" id="int_q13" value="{{ old('int_q13') }}">
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_q11">Have you read and agreed to the Kwartzlab Code of Conduct?</label>
          <input type="text" class="form-control" name="int_q11" id="int_q11" value="{{ old('int_q11') }}">
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label for="int_q12">Is there anything else you would like us or the membership to know?</label>
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

@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop

@section('js')

<script src="/js/jquery.inputmask.bundle.min.js"></script>

 <script>
  $(document).ready(function(){
    $("#phone").inputmask("(999) 999-9999");
    $("#postal").inputmask("A9A 9A9");

  });
</script>
@stop