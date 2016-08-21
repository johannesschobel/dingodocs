<tr>
    <td><code>{!! $parameter->value !!}</code></td>
    <td><code>{!! $parameter->type !!}</code></td>
    @if($parameter->required)
        <td><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></td>
    @else
        <td><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></td>
    @endif
    <td>{!! $parameter->description !!}</td>
    <td><code>{!! $parameter->default !!}</code></td>
</tr>