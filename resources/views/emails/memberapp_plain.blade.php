{{ config('kwartzlabos.org_name') }}

New Member Application - {{ $name }} (@if($form_data['pronouns']['value'] != NULL){{ $form_data['pronouns']['value'] }}@endif)

@switch ($destination)
@case('admin')
DO NOT REPLY TO THIS MESSAGE -- This notification is for informational purposes only. Please reply to the membership application sent to the member's list.
@break
@case('members')
A membership application has been submitted. If you know or have met this applicant, please leave some feedback. If you endorse their membership, reply to this message and leave a +1. An applicant requires five +1's to become a member.

If for any reason you believe they should not be accepted, leave a -1. If you're uncomfortable posting your reason on the members list, please contact the membership coordinator directly to discuss and it will be considered.
@break
@endswitch

Email: {{ $form_data['email']['value'] }}
@if($destination == 'admin')
Legal Name: {{ $form_data['first_name']['value'] }} {{ $form_data['last_name']['value'] }}
Phone: {{ $form_data['phone']['value'] }}
Address: {{ $form_data['address']['value'] }}, {{ $form_data['city']['value'] }}, {{ $form_data['province']['value'] }}  {{ $form_data['postal']['value'] }}
@endif
Photo URL: {{ $photo }}

Applicant Interview

@foreach($form_data as $key => $row)
@if(array_search($key, $skip_fields) === FALSE)
{{ $row['label'] }}
@if(is_array($row['value']))
@foreach ($row['value'] as $key => $value)
{{ $value }}
@endforeach
@else
{{ $row['value'] }}
@endif
@endif

@endforeach

@if($destination == 'admin')
DO NOT REPLY TO THIS MESSAGE -- This notification is for informational purposes only. Please reply to the membership application sent to the member's list.
@endif

Sent on {{ date('Y-m-d') }} by KwartzlabOS (kOS)
