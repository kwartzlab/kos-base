@extends('kiosk')

@section('content')

<div class="page-icon-wrapper">
  <img class="page-icon" src="/img/rfid.png">
</div>

@if(isset($page['heading'])) 
  <h1>{{ $page['heading'] }}</h1>
@endif

@if(isset($page['subheading'])) 
  <h3>{{ $page['subheading'] }}</h3>
@endif

@if(isset($page['text'])) 
  <p>{{ $page['text'] }}</p>
@endif

<form method="POST" action="{{ $page['form_url'] }}" style="height:1px">
  {{ csrf_field() }}
  <div class="form-group no-opacity">
    <input type="password" class="form-control" name="rfid" id="rfid" autofocus>
  </div>
  <div class="form-group">
    <button type="submit" class="btn btn-primary" style="display:none">Submit</button>
  </div>
</form>

@endsection

@section('customjs')
<script>
  $(document).ready(function(){
      $(document).click(function() { $("#rfid").focus() });
      $(document).mousedown(function() { $("#rfid").focus() });
      $(document).mouseup(function() { $("#rfid").focus() });
  });
</script>
@endsection