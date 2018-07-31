@extends('layout')


@section('content')

<form method="POST" action="/users/{{ $user->id }}/store_key">

{{ csrf_field() }}

<div class="form-group">
    <label for="rfid">RFID UID (raw)</label>
    <input type="text" class="form-control" name="rfid" id="rfid" value="{{ old('rfid') }}">
</div>

<div class="form-group">
    <label for="description">Description</label>
    <input type="text" class="form-control" name="description" id="description" value="{{ old('description') }}">
</div>

<div class="form-group">
  <button type="submit" class="btn btn-primary">Save Key</button>
</div>

</form>

@endsection
