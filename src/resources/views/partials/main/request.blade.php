<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">Request (Example):</h3>
    </div>

    <div class="panel-body">
        <textarea readonly class="scrollabletextarea" rows="{!! config('dingodocs.size.request') !!}">{!! $route->getRequest()->getContent() !!}</textarea>
    </div>
</div>