@extends('kiosk')

@section ('customhead')
<meta http-equiv="refresh" content="20; url=/kiosk">
@endsection

@section('content')

<div style="text-align:center;">

<img src="/img/kiosk/rfid.png" style="width:50%;"><br />
	      <h1><strong>Tap key to be added</strong></h1>

<form method="POST" action="/kiosk/create_key">

{{ csrf_field() }}

<div class="form-group no-opacity">
  <input type="password" class="form-control" name="rfid" id="rfid" autofocus>
</div>

<div class="form-group">
  <button type="submit" class="btn btn-primary" style="display:none">Submit</button>
</div>

</form>

</div>

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