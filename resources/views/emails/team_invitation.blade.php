<x-mail::message>
# You have been invited to {{ $teamInvite->team->name }} team

<x-mail::button :url="$url">
Accept Invite
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
