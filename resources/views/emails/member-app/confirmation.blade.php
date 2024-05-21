<x-mail::message>
Dear {{ $email_data['name'] }},

Thank you for applying to join Kwartzlab.
We have received your application and appreciate your interest in becoming a part of our community.

Our team will review your application and get back to you within 6 days with the next steps.
If you have any questions in the meantime, please don't hesitate to contact us by responding to this email.

We look forward to learning more about you and hope to welcome you to our maker space soon.

Best regards,

{{ config('kwartzlabos.membership_coordinator.name') }} <br/>
Membership Coordinator <br/>
membership@kwartzlab.ca <br/>
{{ config('kwartzlabos.org_name') }} <br/>
kwartzlab.ca <br/>
</x-mail::message>
