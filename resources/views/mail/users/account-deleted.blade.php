<x-mail::message>
# Account restricted

Hello {{ $deletedUser->name }},

Your SiteSphere account has been restricted by an administrator and can no longer be used to sign in.

If this was a mistake, an administrator can review and restore your account. The action was performed by {{ $admin->name }}.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
