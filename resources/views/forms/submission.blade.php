<?php if (!isset($skip_fields)) { $skip_fields = array(); } ?>
<div class="card card-warning card-outline">
   <div class="card-header">
      <h3 class="card-title small">{{ $submission->form_name }}</h3>
      <div class="card-tools">
      </div>
   </div>
   <!-- /.card-header -->
   <div class="card-body" style="padding:0;">

      <table class="table table-striped">
         <tbody>
            @foreach(json_decode($submission->data) as $key => $row)
               @if((array_search($key, $skip_fields) === FALSE) && ($row->label != NULL))
                  <tr>
                     <th>{{ $row->label }}</th>
                  </tr>
                  <tr>
                     <td style="padding-left:2rem;">
                     @if(is_array($row->value))
                        @foreach ($row->value as $key => $value)
                           {{ $value }}
                        @endforeach
                     @else
                        @if ($row->value == NULL)
                           -----
                        @else
                           {{ $row->value }}
                        @endif
                     @endif
                     
                     </td>
                  </tr>
               @endif
            @endforeach
         </tbody>
      </table>

   </div>

</div>

@if(\Gate::allows('manage-forms'))

<div class="card card-primary card-outline">
   <div class="card-header">
      <h3 class="card-title small">Submission Info</h3>
      <div class="card-tools">
      </div>
   </div>
   <!-- /.card-header -->
   <div class="card-body" style="padding:0;">

      <div class="row">
         <div class="col-md-6">
            <table class="table table-borderless">
               <tbody>
                  <tr><th>Submitted On:</th><td>{{ $submission->created_at }}</td></tr>
                  <tr><th>Submitted By:</th><td>
                     @if ($submission->submitted_by > 0)
                        <a href="/members/{{ $submission->submitter()->first()->id }}/profile">{{ $submission->submitter()->first()->get_name() }}</a>
                     @else
                        Unknown
                     @endif
                  </td></tr>
               </tbody>
            </table>
         </div>
         <div class="col-md-6">
            <table class="table table-borderless">
               <tbody>
                  <tr><th>Submitter IP:</th><td>{{ $submission->submitter_ip }}</td></tr>
               </tbody>
            </table>
         </div>
      </div>

   </div>
</div>

@endif