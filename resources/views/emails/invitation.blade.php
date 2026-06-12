<x-mail::message>
# You're invited

You've been invited to join **{{ $organization }}** as a **{{ $role }}**.

<x-mail::button :url="$acceptUrl">
Accept Invitation
</x-mail::button>

If you weren't expecting this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
