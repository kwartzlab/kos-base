@extends('adminlte::page')

@section('title', 'Managing ' . $team->name)

@section('content_header')
    <h1>Managing {{ $team->name }}</h1>
@stop

@section('content')
@include('shared.alerts')

<div class="card card-primary card-outline">
  <form method="POST" action="/teams/{{ $team->id }}">
     <div class="card-body">

      {{ method_field('PATCH') }}
      {{ csrf_field() }}

       <div class="row">
          <div class="form-group col-md-3">
            <label for="name">Name</label>
            <input type="text" maxlength="50" class="form-control @if($errors->has('name')) is-invalid @endif" name="name" id="name" value="@if(!old('name')){{$team->name}}@endif{{ old('name') }}">
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label for="description">Description</label>
            <input type="text" class="form-control" name="description" id="description" value="@if(!old('description')){{$team->description}}@endif{{ old('description') }}">
          </div>
        </div>

        @foreach(config('kwartzlabos.team_roles') as $team_role => $team_data)
          @if(($team_data['is_admin']) && (\Auth::user()->can('manage-teams')) || (!$team_data['is_admin']))
            @if((!$team_data['is_trainer']) && (!$team_data['is_maintainer']))
              <div class="row">
                <div class="form-group col-md-6">
                  <label for="{{ $team_role }}[]">{{ $team_data['plural_name'] }}</label>
                  <select multiple class="form-control" name="{{ $team_role }}[]" id="{{ $team_role }}">
                    @foreach($user_list as $key => $value)
                        <option value="{{ $key }}"
                        <option value="{{ $key }}" @if (array_key_exists($team_role, $team_assignments)) @if (in_array($key, $team_assignments[$team_role])) selected="selected" @endif @endif>{{ $value }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            @endif
          @endif
        @endforeach

      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn btn-danger remove_button" style="float:right;" data-record-id="{{ $team->id }}" data-toggle="modal" data-target="#confirm-delete-team"><i class="fas fa-ban"></i> Delete Team</button>
      </div>
      </form>

</div>

<div class="modal fade" id="confirm-delete-team" tabindex="-1" role="dialog" aria-labelledby="modal-remove-team" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="overlay d-flex justify-content-center align-items-center invisible" id="delete-overlay">
        <i class="fas fa-4x fa-sync fa-spin"></i>
      </div>
      <div class="modal-header">
        <h4 class="modal-title" id="modal-remove-team">Confirm Team Deletion</h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      </div>
      <div class="modal-body">
        <p>Deleting a team will also remove it's user assignments. Team leads will also lose access to gatekeepers that were managed by the team (trainers and maintainers will still have access).</p>
        <p>Are you sure you want to do this?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger btn-ok">Delete Permanently</button>
      </div>
    </div>

  </div>
</div>


@stop

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
@stop

@section('plugins.Select2', true)
@section('js')
<script>
   $(document).ready(function () {

      @foreach(config('kwartzlabos.team_roles') as $team_role => $team_data)

      $('#{{ $team_role }}').select2({
      placeholder: 'Select members to assign role',
      allowClear: true,
      multiple: true,
      createTag: function (params) {
        // Don't offset to create a tag if there is no @ symbol
        if (params.term.indexOf('@') === -1) {
        // Return null to disable tag creation
        return null;
        }

        return {
          id: params.term,
          text: params.term
        }
      },
      });
      @endforeach

      $('#confirm-delete-team').on('click', '.btn-ok', function(e) {
            var $modalDiv = $(e.delegateTarget);
            var id = $(this).data('recordId');

            $('#delete-overlay').removeClass('invisible')

            jQuery.ajax({
              url: "{{ url('/teams') }}/" + id,
              method: 'DELETE',
              success: function(result){
                if (result.status == 'success') {
                    $('#delete-overlay').addClass('overlay-change').fadeOut(0).fadeIn('fast')
                    $('#delete-overlay').html('<i class="fas fa-8x fa-check-circle text-success"></i>')
                    window.setTimeout(function(){
                      $modalDiv.modal('hide')
                      window.location.href = '{{ url('/teams/manage') }}'
                    }, 1500);
                } else {
                  $('#delete-overlay').addClass('overlay-change').fadeOut(0).fadeIn('fast')
                  $('#delete-overlay').html('<i class="fas fa-8x fa-times-circle text-danger"></i>')
                  window.setTimeout(function(){
                      $modalDiv.modal('hide')
                    }, 1500);
                }
             }});

      });

      $('#confirm-delete-team').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('.title', this).text(data.recordTitle);
        $('.btn-ok', this).data('recordId', data.recordId);
      });

      $.ajaxSetup({
          headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
          }
      });


   });

</script>

@endsection
