@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
    <img src="{{ asset('images/logo.png') }}" class="logo" alt="{{ config('app.name') }}" style="max-height: 50px;">
</a>
</td>
</tr>
