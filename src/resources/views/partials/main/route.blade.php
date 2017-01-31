<section>
    <div class="row">
        <div class="col-md-9">
            <div>
                <h3 id="{!! $route->getID() !!}" class="text-info">{!! $route->getShortDescription() !!}</h3>
                <hr />
                <h4>{!! $route->getLongDescription() !!}</h4>

                <div class="row">
                    <div class="col-md-8">
                        @if($route->getAuthentication() != null)
                            @include('dingodocs::partials.main.authentication', compact($route))
                        @endif
                    </div>
                    <div class="col-md-4">
                        @if($route->getRole() != false)
                            @include('dingodocs::partials.main.role', compact($route))
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        @include('dingodocs::partials.main.method', compact($route))
                    </div>

                    <div class="col-md-4">
                        @if( $route->getTransformer() != null )
                            @include('dingodocs::partials.main.transformer', compact($route))
                        @endif
                    </div>
                </div>

                @if($route->getRequest() != null)
                <div class="row">
                    @include('dingodocs::partials.main.request', compact($route))
                </div>
                @endif

                <div class="row">
                    <div class="col-md-8">
                        @if(! empty($route->getValidator()))
                            @include('dingodocs::partials.main.validator', compact($route))
                        @endif
                    </div>

                    <div class="col-md-4">
                        @if($route->getQueryParameters() != null)
                            @include('dingodocs::partials.main.queryparameter', compact($route))
                        @endif
                    </div>
                </div>

                @if($route->getResponse() != null)
                    <div class="row">
                        @include('dingodocs::partials.main.response', compact($route))
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-8">
                        @if(! empty($route->getExceptions()))
                            @include('dingodocs::partials.main.exception', compact($route))
                        @endif
                    </div>

                </div>

            </div>
        </div>

    </div>
</section>
