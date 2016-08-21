@if($route->getAuthentication()->getValue())
<div class="panel panel-danger">
    <div class="panel-heading">
        <h3 class="panel-title">Authentication Required</h3>
    </div>
    <div class="panel-body">
        Heads up! You need to authenticate yourself, before using this route!
    </div>
</div>
@endif