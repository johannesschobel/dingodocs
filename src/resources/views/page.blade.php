<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">

    <title>{!! config('dingodocs.title') !!}</title>

    <!-- Bootstrap core CSS -->
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <link href="./css/bootstrap-theme.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="./css/dingodocs.css" rel="stylesheet">
    <link href="./css/custom.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body data-spy="scroll" data-target=".scrollspy">

@include('dingodocs::partials.navigation')

<div class="container-fluid" role="main">

    <div class="row">

        <div class="col-md-2 scrollspy">
            @include('dingodocs::partials.sidebar', compact($routes))
        </div>

        <div class="col-md-10">
            <div class="jumbotron">
                <h1>{!! config("dingodocs.title") !!} <small>{!! $version !!}</small></h1>
                <p>@include('dingodocs::partials.hero')</p>
            </div>

            <div class="row">
                <div class="col-md-12">
                    @include('dingodocs::partials.info')
                </div>
            </div>

            @foreach($routes as $group => $values)
                @include('dingodocs::partials.main.group', compact($group, $values))
            @endforeach
        </div>

    </div>

</div> <!-- /container -->


<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
<script src="./js/bootstrap.min.js"></script>
<script src="./js/dingodocs.js"></script>
</body>
</html>
