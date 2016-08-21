<section>
    <div class="row">
        <div class="col-md-9">
            <div>
                <h2 id="{!! $route->getID() !!}" class="text-info">{!! $route->getShortDescription() !!}</h2>
                <hr />
                <h3>{!! $route->getLongDescription() !!}</h3>
                @if($route->getAuthentication() != null)
                    @include('dingodocs::partials.main.authentication', compact($route))
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-12">
                                @include('dingodocs::partials.main.method', compact($route))
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                @if( $route->getTransformer() != null )
                                    @include('dingodocs::partials.main.transformer', compact($route))
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($route->getRequest() != null)
                <div class="row">
                    @include('dingodocs::partials.main.request', compact($route))
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        @if($route->getQueryParameters() != null)
                            @include('dingodocs::partials.main.queryparameter', compact($route))
                        @endif
                    </div>

                    <div class="col-md-6">
                        @if(! empty($route->getValidator()))
                            @include('dingodocs::partials.main.validator', compact($route))
                        @endif
                    </div>
                </div>

                @if($route->getResponse() != null)
                    <div class="row">
                        @include('dingodocs::partials.main.response', compact($route))
                    </div>
                @endif


            </div>
        </div>

        <div class="col-md-3">
            @if(! empty(""))
            <div class="well well-sm">
                <h4>HTTP Status Codes</h4>
                <table class="table table-striped table-condensed">
                    <thead>
                    <tr>
                        <th width="20%">Code</th>
                        <th width="80%">Description</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</section>
