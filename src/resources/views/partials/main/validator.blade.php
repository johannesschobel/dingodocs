<div class="panel panel-warning">
    <div class="panel-heading">
        <h3 class="panel-title">Validator Parameters</h3>
    </div>

    <div class="panel-body">
        These parameters are validated upon each request to the API.
    </div>

    <table class="table table-striped table-condensed">
        <thead>
        <tr>
            <th width="30%">Parameter</th>
            <th width="20%">Type</th>
            <th width="10%">Required</th>
            <th width="40%">Details</th>
        </tr>
        </thead>
        <tbody>
        @foreach($route->getValidatorParameters() as $attribute => $parameter)
            @include('dingodocs::partials.main.validator_line', compact($attribute, $parameter))
        @endforeach
        </tbody>
    </table>
</div>