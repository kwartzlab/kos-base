<div class="card card-primary card-outline">
  <form method="POST" action="/forms">
    <div class="card-body">

      {{ csrf_field() }}

       <div class="row">
          <div class="form-group col-md-3">
            <label for="name">Name</label>
            <input type="text" class="form-control @if($errors->has('name')) is-invalid @endif" name="name" id="name" value="{{ old('name') }}" required>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-3">
            <label for="team_id">Special Assignment</label>
            <select class="form-control" name="special_form">
              <option value="0" @if ($selected_assignment == '0') selected="selected" @endif>None</option>
              @foreach(config('kwartzlabos.special_forms') as $special_form_id => $special_form_data)
                <option value="{{ $special_form_id }}" @if($selected_assignment === $special_form_id) selected="selected" @endif >{{ $special_form_data['name'] }}</option>
              @endforeach
            </select>
          </div>
          </div>

        <div class="row">
          <div class="form-group col-md-3">
              <label for="status">Status</label>
              <select class="form-control" name="status">
                <option value="enabled" @if (old('status') == 'enabled') selected="selected" @endif>Enabled</option>
                <option value="disabled" @if (old('status') == 'disabled') selected="selected" @endif>Disabled</option>
              </select>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-8">
            <label for="description">Description (displayed above form)</label>
            <textarea class="form-control" rows="5" role="textbox" name="description" id="description" aria-multiline="true">{{ old('description') }}</textarea>
          </div>
        </div>

        <h3>Form Elements</h3>

         <div class="row">
            <div class="form-group col-md-12">
               <button type="button" class="btn btn-primary btn-add-single"><i class="fas fa-plus-circle"></i> Single Line</button>&nbsp;
               <button type="button" class="btn btn-primary btn-add-multi"><i class="fas fa-plus-circle"></i> Paragraph</button>&nbsp;
               <button type="button" class="btn btn-primary btn-add-dropdown"><i class="fas fa-plus-circle"></i> Dropdown</button>&nbsp;
               <button type="button" class="btn btn-primary btn-add-radio"><i class="fas fa-plus-circle"></i> Single Choice</button>&nbsp;
               <button type="button" class="btn btn-primary btn-add-checkbox"><i class="fas fa-plus-circle"></i> Multiple Choice</button>&nbsp;
               <button type="button" class="btn btn-primary btn-add-switch"><i class="fas fa-plus-circle"></i> Switch</button>&nbsp;
               <button type="button" class="btn btn-primary btn-add-upload"><i class="fas fa-plus-circle"></i> File Upload</button>
            </div>
         </div>

         <div class="row">
            <div class="col-md-8">
               <div id="form-elements">

               </div>
            </div>
         </div>
   
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">Save Form</button>
      </div>
      </div>
    </form>

</div>

@section('css')
    <link rel="stylesheet" href="/css/kos.css">
    <link rel="stylesheet" href="/css/summernote-bs4.css">
@stop

@section('js')
<script src="/js/summernote-bs4.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
   $(document).ready(function(){

      $('#description').summernote({
         height:200,
      });

      // Allows input fields to be moved around
      $("#form-elements").sortable();
      $(".input-options").sortable();

      // Update input field card title when label changes
      $("#form-elements").on('change', '.field-label', function() {
          title = $(event.target).closest('.card').find('.title-label');
          $(title).replaceWith('<span class="title-label">' + $(event.target).closest('.card').find('.field-label').val() + '</span>');
      });

      $(".btn-add-single").click(function () {
         current_id = create_UUID();

         $("#form-elements").append('<div class="card card-info card-outline" id="element[' + current_id + ']">\
            <div class="card-header">\
              <h4 class="card-title smaller">Single Line [<span class="title-label"></span>]</h4>\
              <div class="card-tools">\
                  <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>\
                  <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-trash-alt"></i></button>\
              </div>\
            </div>\
            <div class="card-body">\
              <input type="hidden" name="element[' + current_id + '][type]" value="input">\
              <label for="element[' + current_id + '][label]">Field Label</label>\
              <input type="text" class="field-label form-control" name="element[' + current_id + '][label]">\
              <label for="element[' + current_id + '][length]">Field Length (up to 250 characters, leave blank for maximum)</label>\
              <input type="text" name="element[' + current_id + '][length]" class="form-control">\
              <label for="element[' + current_id + '][name]">Custom field name (used for special forms, leave blank if none)</label>\
              <input type="text" name="element[' + current_id + '][name]" class="form-control">\
              <label for="element[' + current_id + '][mask]">Input Mask</label>\
              <select class="form-control" name="element[' + current_id + '][mask]">\
                <option value="none">None</option>\
                <option value="date">Date (yyyy-mm-dd)</option>\
                <option value="phone">Phone Number (xxx-xxx-xxxx)</option>\
                <option value="postal">Postal Code (X1X 1X1)</option>\
              </select>\
              <input type="checkbox" class="primary" name="element[' + current_id + '][required]">\
              <label for="element[' + current_id + '][required]" style="margin-top:15px;margin-left:10px;">Required</label>\
            </div>\
          </div>');
      });  

      $(".btn-add-multi").click(function () {
         current_id = create_UUID();
         $("#form-elements").append('<div class="card card-info card-outline" id="element[' + current_id + ']">\
            <div class="card-header">\
              <h4 class="card-title smaller">Paragraph [<span class="title-label"></span>]</h4>\
              <div class="card-tools">\
                  <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>\
                  <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-trash-alt"></i></button>\
              </div>\
            </div>\
            <div class="card-body">\
              <input type="hidden" name="element[' + current_id + '][type]" value="textarea">\
              <label for="element[' + current_id + '][label]">Field Label</label>\
              <input type="text" class="field-label form-control" name="element[' + current_id + '][label]">\
              <label for="element[' + current_id + '][length]">Maximum character length (leave blank for maximum)</label>\
              <input type="text" name="element[' + current_id + '][length]" class="form-control">\
              <label for="element[' + current_id + '][name]">Custom field name (used for special forms, leave blank if none)</label>\
              <input type="text" name="element[' + current_id + '][name]" class="form-control">\
              <input type="checkbox" class="primary" name="element[' + current_id + '][usehtml]">\
              <label for="element[' + current_id + '][usehtml]" style="margin: 15px 10px 0px">Enable HTML editor (otherwise will be plain text)</label>\
              <br /><input type="checkbox" class="primary" name="element[' + current_id + '][required]">\
              <label for="element[' + current_id + '][required]" style="margin: 15px 10px 0px">Required</label>\
            </div>\
         </div>');
      });  

      $(".btn-add-switch").click(function () {
          current_id = create_UUID();
          $("#form-elements").append('<div class="card card-info card-outline" id="element[' + current_id + ']">\
            <div class="card-header">\
              <h4 class="card-title smaller">Switch [<span class="title-label"></span>]</h4>\
              <div class="card-tools">\
                  <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>\
                  <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-trash-alt"></i></button>\
              </div>\
            </div>\
            <div class="card-body">\
              <input type="hidden" name="element[' + current_id + '][type]" value="switch">\
              <label for="element[' + current_id + '][label]">Field Label</label>\
              <input type="text" class="field-label form-control" name="element[' + current_id + '][label]">\
              <label for="element[' + current_id + '][on]">Text returned when activated</label>\
              <input type="text" name="element[' + current_id + '][on]" class="form-control">\
              <label for="element[' + current_id + '][off]">Text returned when deactivated (default)</label>\
              <input type="text" name="element[' + current_id + '][off]" class="form-control">\
              <label for="element[' + current_id + '][name]">Custom field name (used for special forms, leave blank if none)</label>\
              <input type="text" name="element[' + current_id + '][name]" class="form-control">\
              <input type="checkbox" class="primary" name="element[' + current_id + '][required]">\
              <label for="element[' + current_id + '][required]" style="margin: 15px 10px 0px">Required</label>\
            </div>\
         </div>');
      });  

      $(".btn-add-dropdown").click(function () {
         current_id = create_UUID();
         $("#form-elements").append('<div class="card card-info card-outline" id="element[' + current_id + ']">\
            <div class="card-header">\
              <h4 class="card-title smaller">Dropdown [<span class="title-label"></span>]</h4>\
              <div class="card-tools">\
                  <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>\
                  <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-trash-alt"></i></button>\
              </div>\
            </div>\
            <div class="card-body">\
              <input type="hidden" name="element[' + current_id + '][type]" value="dropdown">\
              <label for="element[' + current_id + '][label]">Field Label</label>\
              <input type="text" class="field-label form-control" name="element[' + current_id + '][label]">\
              <label for="element[' + current_id + '][name]">Custom field name (used for special forms, leave blank if none)</label>\
              <input type="text" name="element[' + current_id + '][name]" class="form-control">\
              <input type="checkbox" class="primary" name="element[' + current_id + '][required]">\
              <label for="element[' + current_id + '][required]" style="margin: 15px 10px 0px">Required</label><br />\
              <label style="margin:10px 0px;font-size:1.2rem;">Field Choices</label><br />\
              <button type="button" class="btn btn-primary btn-sm btn-add-input-option"><i class="fas fa-plus-circle"></i> Add Option</button>&nbsp;\
              <div class="row" style="margin-top:10px"><div class="form-group col-md-5"><strong>Label</strong></div><div class="form-group col-md-4"><strong>Value (for special forms)</strong></div></div>\
              <div class="input-options">\
              </div>\
            </div>\
         </div>');
      });  

      $(".btn-add-radio").click(function () {
         current_id = create_UUID();
         $("#form-elements").append('<div class="card card-info card-outline" id="element[' + current_id + ']">\
            <div class="card-header">\
              <h4 class="card-title smaller">Single Choice [<span class="title-label"></span>]</h4>\
              <div class="card-tools">\
                  <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>\
                  <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-trash-alt"></i></button>\
              </div>\
            </div>\
            <div class="card-body">\
              <input type="hidden" name="element[' + current_id + '][type]" value="radio">\
              <label for="element[' + current_id + '][label]">Field Label</label>\
              <input type="text" class="field-label form-control" name="element[' + current_id + '][label]">\
              <label for="element[' + current_id + '][name]">Custom field name (used for special forms, leave blank if none)</label>\
              <input type="text" name="element[' + current_id + '][name]" class="form-control">\
              <input type="checkbox" class="primary" name="element[' + current_id + '][required]">\
              <label for="element[' + current_id + '][required]" style="margin: 15px 10px 0px">Required</label><br />\
              <label style="margin:10px 0px;font-size:1.2rem;">Field Choices</label><br />\
              <button type="button" class="btn btn-primary btn-sm btn-add-input-option"><i class="fas fa-plus-circle"></i> Add Option</button>&nbsp;\
              <div class="row" style="margin-top:10px"><div class="form-group col-md-5"><strong>Label</strong></div><div class="form-group col-md-4"><strong>Value (for special forms)</strong></div></div>\
              <div class="input-options">\
              </div>\
            </div>\
         </div>');
      });  

      $(".btn-add-checkbox").click(function () {
         current_id = create_UUID();
         $("#form-elements").append('<div class="card card-info card-outline" id="element[' + current_id + ']">\
            <div class="card-header">\
              <h4 class="card-title smaller">Multiple Choice [<span class="title-label"></span>]</h4>\
              <div class="card-tools">\
                  <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>\
                  <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-trash-alt"></i></button>\
              </div>\
            </div>\
            <div class="card-body">\
              <input type="hidden" name="element[' + current_id + '][type]" value="checkbox">\
              <label for="element[' + current_id + '][label]">Field Label</label>\
              <input type="text" class="field-label form-control" name="element[' + current_id + '][label]">\
              <label for="element[' + current_id + '][name]">Custom field name (used for special forms, leave blank if none)</label>\
              <input type="text" name="element[' + current_id + '][name]" class="form-control">\
              <input type="checkbox" class="primary" name="element[' + current_id + '][required]">\
              <label for="element[' + current_id + '][required]" style="margin: 15px 10px 0px">Required</label><br />\
              <label style="margin:10px 0px;font-size:1.2rem;">Field Choices</label><br />\
              <button type="button" class="btn btn-primary btn-sm btn-add-input-option"><i class="fas fa-plus-circle"></i> Add Option</button>&nbsp;\
              <div class="row" style="margin-top:10px"><div class="form-group col-md-5"><strong>Label</strong></div><div class="form-group col-md-4"><strong>Value (for special forms)</strong></div></div>\
              <div class="input-options">\
              </div>\
            </div>\
         </div>');
      });  

      $(".btn-add-upload").click(function () {
         current_id = create_UUID();

         $("#form-elements").append('<div class="card card-info card-outline" id="element[' + current_id + ']">\
            <div class="card-header">\
              <h4 class="card-title smaller">File Upload [<span class="title-label"></span>]</h4>\
              <div class="card-tools">\
                  <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>\
                  <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-trash-alt"></i></button>\
              </div>\
            </div>\
            <div class="card-body">\
              <input type="hidden" name="element[' + current_id + '][type]" value="upload">\
              <label for="element[' + current_id + '][label]">Field Label</label>\
              <input type="text" class="field-label form-control" name="element[' + current_id + '][label]">\
              <label for="element[' + current_id + '][name]">Custom field name (used for special forms, leave blank if none)</label>\
              <input type="text" name="element[' + current_id + '][name]" class="form-control">\
              <input type="checkbox" class="primary" name="element[' + current_id + '][multiupload]">\
              <label for="element[' + current_id + '][multiupload]" style="margin-top:15px;margin-left:10px;">Multi Upload</label><br />\
              <input type="checkbox" class="primary" name="element[' + current_id + '][required]">\
              <label for="element[' + current_id + '][required]" style="margin-top:15px;margin-left:10px;">Required</label>\
            </div>\
          </div>');
      });  

      $("#form-elements").on('click', 'button.btn-add-input-option', function() {
          // get id of target element
          element_id = $(event.target).closest('.card').attr('id');
          option_id = create_UUID();
         $(event.target).closest('.card-body').find('.input-options').append('<div class="card card-default card-outline">\
            <div class="card-header">\
            <div class="card-tools">\
                  <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-trash-alt"></i></button>\
            </div>\
            <div class="row">\
              <input type="text" class="col-md-5 form-control" name="' + element_id + '[options][' + option_id + '][name]">&nbsp;&nbsp;&nbsp;\
              <input type="text" class="col-md-4 form-control" name="' + element_id + '[options][' + option_id + '][value]">\
            </div>\
            </div>\
         </div>');
      });

   });

   function create_UUID(){
    var dt = new Date().getTime();
    var uuid = 'xxxxxxxx-xxxx-4xxx'.replace(/[xy]/g, function(c) {
        var r = (dt + Math.random()*16)%16 | 0;
        dt = Math.floor(dt/16);
        return (c=='x' ? r :(r&0x3|0x8)).toString(16);
    });
    return uuid;
}

</script>
@stop