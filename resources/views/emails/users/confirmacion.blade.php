@component('mail::message')
# Introduction

The body of your message.

@component('mail::button', ['url' => 'google.com'])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
