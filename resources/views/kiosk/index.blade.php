@extends('kiosk')


@section ('customhead')
<meta http-equiv="refresh" content="300; url=/kiosk/logout">
@endsection

@section('content')

<div style="text-align:center;">

<img src="/img/kiosk/rfid.png" style="width:50%;"><br />
	      <h1><strong>Scan card to begin</strong></h1>

<form method="POST" action="/kiosk/authenticate">

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