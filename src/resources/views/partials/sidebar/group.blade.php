<li class=""><a href="#{!! $group !!}">{!! $group !!}</a>
    @if(!empty($values))
        <ul class="nav">
        @foreach($values as $route)
            @include('dingodocs::partials.sidebar.route', compact($route))
        @endforeach
        </ul>
    @endif
</li>