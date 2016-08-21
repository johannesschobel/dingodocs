<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">Response (Example):</h3>
    </div>

    <div class="panel-body">
        <textarea readonly class="scrollabletextarea" rows="{!! config('dingodocs.size.response') !!}">{!! $route->getResponse()->getContent() !!}</textarea>
    </div>
</div>