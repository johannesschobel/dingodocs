<li class="">
    <a href="#{!! $route->getID() !!}">
    @foreach($route->getMethods() as $method)
        @if($method != "HEAD")
        <span class="label label-info {!! $method !!}">{!! $method !!}</span>
        @endif
    @endforeach
    {!! $route->getShortDescription() !!}
    </a>
</li>