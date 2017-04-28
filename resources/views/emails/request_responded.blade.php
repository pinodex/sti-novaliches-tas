@component('mail::message')
# Hello {{ $recipient->name }}

{{ __('notification.' . $entry['content'], [
    'request_id' => $request->id,
    'type_name' => $request->type_name,
    'approver_name' => $request->approver ? $request->approver->name : 'Unknown',
    'requestor_name' => $request->requestor ? $request->requestor->name : 'Unknown',
    'time' => $request->responded_at
]) }}

@component('mail::button', [
    'url' => $url,
    'color' => 'blue'
])

    View Request

@endcomponent

@component('mail::subcopy')
    If you’re having trouble clicking the "View Request" button, copy and paste the URL below into your web browser: [{{ $url }}]({{ $url }})<br />

    This is a no reply e-mail. Any replies will be ignored. Should you have any questions, please proceed to your department head.
@endcomponent
@endcomponent
