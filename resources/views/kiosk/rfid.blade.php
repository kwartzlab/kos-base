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

<div id="scanButtonRow" class="row" style="margin-top:50px; display:none;">
  <div class="col-xs-10 col-xs-offset-1">
     <button id="scanButton" name="scan" class="btn btn-success btn-lg btn-block">Press to scan</button>
  </div>
</div>

<form id="form-rfid" method="POST" action="{{ $page['form_url'] }}" style="height:1px">
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
    $(document)
        .click(function() { $("#rfid").focus() })
        .mousedown(function() { $("#rfid").focus() })
        .mouseup(function() { $("#rfid").focus() })
        ;

    // For developer reference, the following conditions must be met for the NDEFReader API to function:
    //    - The user must be accessing the API through a supported browser. This generally only includes Android browsers like Chrome and WebView.
    //    - The page must be served over HTTPS.
    //    - The reader function must be initated by a user action -- usually a button press.
    //    - Once the reader is initated, a modal asking for permission to access to NFC appears. This permission must be allowed.
    // If any of these conditions are not met, the API will transparently fail and the functionality will be unavailable.

    if ('NDEFReader' in window) { /* Scan and write NFC tags */
        $('#scanButtonRow').show(); // Only show a scan button where the API is supported.

        // For the Web NFC API to work, the reader must be triggered from a user action like this button.
        // It does not work if you initiate the reader through a programatic function. :/
        $('#scanButton').click(async () => {
            $('#scanButton')
              .removeClass('btn-success')
              .addClass('btn-warning')
              .text('Scanning...')
              ;

            console.log("User clicked scan button");

            try {
                const ndef = new NDEFReader();
                await ndef.scan();
                console.log("> Scan started");

                ndef.addEventListener("readingerror", () => {
                    console.log("Argh! Cannot read data from the NFC tag. Try another one?");
                });

                ndef.addEventListener("reading", ({ message, serialNumber }) => {
                    serialNumber = serialNumber.replaceAll(':', '');

                    console.log(`> Serial Number: ${serialNumber}`);
                    console.log(`> Records: (${message.records.length})`);

                    $('#rfid').val(serialNumber);
                    $('#form-rfid').trigger('submit');
                });
            }
            catch(error) {
              console.log("Error with web nfc: " + error);
            }
        });
    }
});
</script>
@endsection
