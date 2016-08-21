@if( count($route->getTransformer()->getTransformer()->getAvailableIncludes()) > 0 )
<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">Transformer includes</h3>
    </div>
    <div class="panel-body">
        @foreach($route->getTransformer()->getTransformer()->getAvailableIncludes() as $include)
            <span class="label label-info">{!! $include !!}</span>
        @endforeach
    </div>
</div>
@endif