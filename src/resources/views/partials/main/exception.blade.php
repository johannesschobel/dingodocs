<div class="panel panel-danger">
    <div class="panel-heading">
        <h3 class="panel-title">Exceptions</h3>
    </div>

    <div class="panel-body">
        This route may throw the following Exceptions.
    </div>

    <table class="table table-striped table-condensed">
        <thead>
        <tr>
            <th width="10%">HTTP Status</th>
            <th width="90%">Description</th>
        </tr>
        </thead>
        <tbody>
        @foreach($route->getExceptions()->getExceptions() as $exception)
            @include('dingodocs::partials.main.exception_line', compact($exception))
        @endforeach
        </tbody>
    </table>
</div>