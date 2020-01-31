@component('mail::message')

@if ($destination == 'admin')
@component('mail::panel')
<span style="color:#DD0000;font-weight:bold">DO NOT REPLY TO THIS MESSAGE -- This notification is for informational purposes only.</span> Please reply to the membership application sent to the member's list.
@endcomponent

# Applicant Name - {{ $name }}

@component('mail::table')

Email: **{{ $form_data['email']['value'] }}**

Phone Number: **{{ $form_data['phone']['value'] }}**

Mailing Address: **{{ $form_data['address']['value'] }}, {{ $form_data['city']['value'] }}, {{ $form_data['province']['value'] }}  {{ $form_data['postal']['value'] }}**

Photo: {{ $photo }}

@endcomponent

## Applicant Interview

@foreach($form_data as $key => $row)
@if(array_search($key, $skip_fields) === FALSE)

{{ $row['label'] }}

@if(is_array($row['value']))
@foreach ($row['value'] as $key => $value)
**{{ $value }}**

@endforeach
@else
**{{ $row['value'] }}**
@endif
@endif
@endforeach

@component('mail::panel')
<span style="color:#DD0000;font-weight:bold">DO NOT REPLY TO THIS MESSAGE -- This notification is for informational purposes only.</span> Please reply to the membership application sent to the member's list.
@endcomponent

@endcomponent

@else
# Membership Application - {{ $name }}

@component('mail::panel')
A membership application has been submitted. If you know or have met this applicant, please leave some feedback. If you endorse their membership, reply to this message and leave a +1. An applicant requires five +1's to become a member.

If for any reason you believe they should not be accepted, leave a -1. If you're uncomfortable posting your reason on the members list, please contact the membership coordinator directly to discuss and it will be considered.
@endcomponent

@component('mail::table')

Email: **{{ $form_data['email']['value'] }}**

Photo: {{ $photo }}

@endcomponent

## Applicant Interview

@foreach($form_data as $key => $row)
@if(array_search($key, $skip_fields) === FALSE)

{{ $row['label'] }}

@if(is_array($row['value']))
@foreach ($row['value'] as $key => $value)
**{{ $value }}**

@endforeach
@else
**{{ $row['value'] }}**
@endif
@endif
@endforeach

@endcomponent

@endif
