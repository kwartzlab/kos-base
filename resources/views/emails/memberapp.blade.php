@component('mail::message')
# Membership Application - {{ $name }}

@component('mail::panel')
A membership application has been submitted. If you know or have met this applicant, please leave some feedback. If you endorse their membership, reply to this message and leave a +1. An applicant requires five +1's to become a member.

If for any reason you believe they should not be accepted, leave a -1. If you're uncomfortable posting your reason on the members list, please contact the membership coordinator directly to discuss and it will be considered.
@endcomponent

@component('mail::table')
@if ($destination == 'admin')
Email: **{{ $form_data[15]['value'] }}**

Phone Number: **{{ $form_data[16]['value'] }}**

Mailing Address: **{{ $form_data[17]['value'] }}, {{ $form_data[18]['value'] }}, {{ $form_data[19]['value'] }}  {{ $form_data[20]['value'] }}**

Photo: {{ $photo }}
@else
Email: **{{ $form_data[15]['value'] }}**

Photo: {{ $photo }}
@endif

@endcomponent

## Applicant Interview

@for ($i = 0; $i < 13; $i++)
{{ $form_data[$i]['label'] }}

**{{ $form_data[$i]['value'] }}**

@endfor


@endcomponent
