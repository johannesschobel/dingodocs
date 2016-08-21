<tr>
    <td>{!! $attribute !!}</td>
    <td><code>{!! $parameter['type'] !!}</code></td>
    @if($parameter['required'])
        <td><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></td>
    @else
        <td><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></td>
    @endif
    <td>{!! implode(' ', $parameter['details']) !!}</td>
</tr>