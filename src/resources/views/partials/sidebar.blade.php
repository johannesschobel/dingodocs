<ul id="scrollspy" class="nav hidden-xs hidden-sm" data-spy="affix">
    @foreach($routes as $group => $values)
        @include('dingodocs::partials.sidebar.group', compact($group, $values))
    @endforeach
</ul>
