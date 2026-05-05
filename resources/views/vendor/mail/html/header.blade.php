@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="{{static_asset('assets/img/app_logo.png')}}" class="logo" alt="Samh Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
