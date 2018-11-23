<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Title | Log in</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.2 -->
    <link href="{{ url("backend/css/bootstrap.min.css") }}" rel="stylesheet" type="text/css" />
    <!-- Font Awesome Icons -->
    <link href="{{ url("backend/css/font-awesome.min.css") }}" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="{{ url("backend/css/AdminLTE.min.css") }}" rel="stylesheet" type="text/css" />
    <!-- iCheck -->
    <link href="{{ url("backend/css/iCheck/square/blue.css") }}" rel="stylesheet" type="text/css" />
    <link href="{{ url("backend/css/fonts.css") }}" rel="stylesheet" type="text/css" />
    <link href="{{ url("backend/css/login.css") }}" rel="stylesheet" type="text/css" />
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
<body class="login-page">
<div class="login-box">
    <div class="login-logo">{!! config_db_cached('settings::site_title') !!}</div><!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">Sign in to start your session</p>
            {!! Form::open() !!}

            @if ($errors->has('login'))
                <div class="alert alert-danger">
                    {!! $errors->first('login', ':message') !!}
                </div>
            @endif

            <div class="form-group has-feedback">
                <input type="text" class="form-control" name="email" placeholder="Email"/>
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" name="password" placeholder="Password"/>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-8">
                    <div class="checkbox icheck">
                        <label>
                            <input type="checkbox"> Remember Me
                        </label>
                    </div>
                </div><!-- /.col -->
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
                </div><!-- /.col -->


                <!-- <a href="http://localhost:8000/auth/facebook">Login with Facebook</a> -->


            </div>
        {!! Form::close() !!}

        <div class="social-auth-links text-center">
            <!--<p>- OR -</p>
            <a href="#" class="btn btn-block btn-social btn-facebook btn-flat"><i class="fa fa-facebook"></i> Sign in using Facebook</a>
            <a href="#" class="btn btn-block btn-social btn-google-plus btn-flat"><i class="fa fa-google-plus"></i> Sign in using Google+</a>
        </div>

        <a href="#">I forgot my password</a><br>
        <a href="#" class="text-center">Register a new membership</a>-->

    </div><!-- /.login-box-body -->
</div><!-- /.login-box -->

<!-- jQuery 2.1.3 -->
<script src="{!! url('backend/plugins/jQuery/jQuery-2.1.3.min.js') !!}"></script>
<!-- Bootstrap 3.3.2 JS -->
<!-- <script src="{{ url('backend/js/app.js') }}" type="text/javascript"></script>
<!-- iCheck -->
<script src="{!! url('backend/plugins/iCheck/icheck.min.js') !!}" type="text/javascript"></script> 
<script>
    $(function () {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
    });
</script>
</body>
</html>