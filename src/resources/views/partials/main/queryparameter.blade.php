<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">Query Parameters</h3>
    </div>

    <div class="panel-body">
        Query parameters, which can be added to the request (e.g., in order to filter, sort, ... the data).
    </div>

    <table class="table table-striped table-condensed">
        <thead>
        <tr>
            <th width="20%">Parameter</th>
            <th width="10%">Type</th>
            <th width="10%">Required</th>
            <th width="50%">Details</th>
            <th width="10%">Default</th>
        </tr>
        </thead>
        <tbody>
        @foreach($route->getQueryParameters()->getParameters() as $parameter)
            @include('dingodocs::partials.main.queryparameter_line', compact($parameter))
        @endforeach
        </tbody>
    </table>
</div>