<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">HTTP Request Methods</h3>
    </div>

    <table class="table table-striped table-condensed">
        <thead>
        <tr>
            <th width="10%">Method</th>
            <th width="90%">URI</th>
        </tr>
        </thead>
        <tbody>
        @foreach($route->getMethods() as $method)
            @include('dingodocs::partials.main.method_line', compact($route, $method))
        @endforeach
        </tbody>
    </table>
</div>