<section>
    <div class="page-header">
        <h1 id="{!! $group !!}" class="text-primary">{!! $group !!}</h1>
    </div>

    @if(! empty($values))
        @foreach($values as $route)
            @include('dingodocs::partials.main.route', compact($route))
        @endforeach
    @endif
</section>
