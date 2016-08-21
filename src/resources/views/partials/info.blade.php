<h1 class="text-primary">General Information</h1>
<hr />

<p class="text-danger">You can change me in <code>/resources/vendor/dingodocs/partials/info.blade.php</code></p>

<div class="panel panel-warning">
    <div class="panel-heading">
        <h3 class="panel-title">Authentication</h3>
    </div>
    <div class="panel-body">
        We use a token-based authentication (e.g., JWT). In order to send the token, you must provide a
        <code>?token=XYZ</code> parameter to each of your requests!
    </div>
</div>

<div class="panel panel-warning">
    <div class="panel-heading">
        <h3 class="panel-title">Request Throttling</h3>
    </div>
    <div class="panel-body">
        We limit the amount of requests you can send to our API. The limit is set to 60 requests per minute!
    </div>
</div>

<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">Language</h3>
    </div>
    <div class="panel-body">
        Our API may provide data in multiple languages. In order to request the data in a specific language, please use
        the <code>Accept-Language</code> header field. To request data in German, use: <code>Accept-Language=de</code>
    </div>
</div>