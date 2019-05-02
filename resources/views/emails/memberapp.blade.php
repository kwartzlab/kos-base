@component('mail::message')

@if ($destination == 'admin')
@component('mail::panel')
<span style="color:#DD0000;font-weight:bold">DO NOT REPLY TO THIS MESSAGE -- This notification is for informational purposes only.</span> Please reply to the membership application sent to the member's list.
@endcomponent

# Applicant Name - {{ $name }}

@component('mail::table')

Email: **{{ $form_data[15]['value'] }}**

Phone Number: **{{ $form_data[16]['value'] }}**

Mailing Address: **{{ $form_data[17]['value'] }}, {{ $form_data[18]['value'] }}, {{ $form_data[19]['value'] }}  {{ $form_data[20]['value'] }}**

Photo: {{ $photo }}

@endcomponent

## Applicant Interview

@for ($i = 0; $i < 14; $i++)
{{ $form_data[$i]['label'] }}

**{{ $form_data[$i]['value'] }}**

@endfor

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

Email: **{{ $form_data[15]['value'] }}**

Photo: {{ $photo }}

@endcomponent

## Applicant Interview

@for ($i = 0; $i < 14; $i++)
{{ $form_data[$i]['label'] }}

**{{ $form_data[$i]['value'] }}**

@endfor

@endcomponent

@endif
