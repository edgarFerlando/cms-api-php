<!DOCTYPE html>
<html lang="{{{ getLang() }}}">
    <head>
        <meta charset="UTF-8">
        <meta name="_token" content="{!! csrf_token() !!}" />
        <title>{!! strip_tags(config_db_cached('settings::site_title')) !!} | Dashboard</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- Bootstrap 3.3.2 -->
        <link href="{!! url('backend/css/bootstrap.min.css') !!}" rel="stylesheet" type="text/css" />
        <!-- Font Awesome Icons -->
        <link href="{!! url('backend/css/font-awesome.min.css') !!}" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="{!! url('backend/css/ionicons.min.css') !!}" rel="stylesheet" type="text/css"/>
        <!-- Morris chart -->
        <link href="{!! url('backend/plugins/morris/morris.css') !!}" rel="stylesheet" type="text/css"/>
        <!-- jvectormap -->
        <link href="{!! url('backend/plugins/jvectormap/jquery-jvectormap-1.2.2.css') !!}" rel="stylesheet" type="text/css"/>
        <!-- Daterange picker -->
        <link href="{!! url('backend/plugins/daterangepicker/daterangepicker-bs3.css') !!}" rel="stylesheet" type="text/css"/>
        <link href="{!! url('backend/plugins/datepicker/datepicker3.css') !!}" rel="stylesheet" type="text/css"/>
        <link href="{!! url('backend/plugins/datetimepicker/bootstrap-datetimepicker.min.css') !!}" rel="stylesheet" type="text/css"/>
        <!-- Theme style -->
        <link href="{!! url('backend/css/AdminLTE.min.css') !!}" rel="stylesheet" type="text/css" />
        <!-- ckeditor -->
        <link href="{!! url('backend/plugins/ckeditor/contents.css') !!}" rel="stylesheet" type="text/css"/>
        <link href="{!! url('backend/css/langTabs.css') !!}" rel="stylesheet" type="text/css"/>
        <link href="{!! url('backend/css/select2.min.css') !!}" rel="stylesheet" type="text/css"/>
        <link href="{!! url('backend/css/selectize.css') !!}" rel="stylesheet" type="text/css"/>
        <link href="{!! url('backend/css/style.css') !!}" rel="stylesheet" type="text/css"/>

        <!-- jQuery 2.1.3 -->
        <script src="{!! url('backend/plugins/jQuery/jQuery-2.1.3.min.js') !!}"></script>
        <!-- Bootstrap 3.3.2 JS -->
        <script src="{!! url('backend/bootstrap/js/bootstrap.min.js') !!}" type="text/javascript"></script>
        <!-- FastClick -->
        <script src="{!! url('backend/plugins/fastclick/fastclick.min.js') !!}"></script>
        <!-- AdminLTE App -->
        <script src="{!! url('backend/js/adminlte.js') !!}" type="text/javascript"></script>
        <!-- Sparkline -->
        <script src="{!! url('backend/plugins/sparkline/jquery.sparkline.min.js') !!}" type="text/javascript"></script>
        <!-- jvectormap -->
        <script src="{!! url('backend/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js') !!}" type="text/javascript"></script>
        <script src="{!! url('backend/plugins/jvectormap/jquery-jvectormap-world-mill-en.js') !!}" type="text/javascript"></script>
        <!-- daterangepicker -->
        <script src="{!! url('backend/plugins/daterangepicker/daterangepicker.js') !!}" type="text/javascript"></script>
        <!-- datepicker -->
        <script src="{!! url('backend/plugins/datepicker/bootstrap-datepicker.js') !!}" type="text/javascript"></script>
        <!-- datetimepicker -->
        <script src="{!! url('backend/plugins/datetimepicker/bootstrap-datetimepicker.min.js') !!}" type="text/javascript"></script>
       
        <!-- iCheck -->
        <script src="{!! url('backend/plugins/iCheck/icheck.min.js') !!}" type="text/javascript"></script>
        <!-- SlimScroll 1.3.0 -->
        <script src="{!! url('backend/plugins/slimScroll/jquery.slimscroll.min.js') !!}" type="text/javascript"></script>
        <!-- ChartJS 1.0.1 -->
        <script src="{!! url('backend/plugins/chartjs/Chart.min.js') !!}" type="text/javascript"></script>

        <!-- ckeditor -->
        <script src="{!! url('backend/plugins/ckeditor/ckeditor.js') !!}" type="text/javascript"></script>

        <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
        <!-- AdminLTE for demo purposes -->
        <!-- <script src="{!! url('backend/js/demo.js') !!}" type="text/javascript"></script> -->
        <script src="{!! url('backend/js/langTabs.js') !!}" type="text/javascript"></script>
        <script src="{!! url('backend/js/speakingurl.min.js') !!}" type="text/javascript"></script>
        <script src="{!! url('backend/js/slugify.js') !!}" type="text/javascript"></script>
        <script src="{!! url('backend/js/select2.min.js') !!}" type="text/javascript"></script>
        <script src="{!! url('backend/js/selectize.min.js') !!}" type="text/javascript"></script>
        <script src="{!! url('backend/js/selectizePlugin.js') !!}" type="text/javascript"></script>
        <script>
            var site = (function() {
                return{
                    base_url : '{{{ url('/') }}}',
                    base_url_locale : '{!! langUrl('/') !!}',
                    lang: {
                        save : '{{{ trans('app.save') }}}',
                        update : '{{{ trans('app.update') }}}',
                        add : '{{{ trans('app.add') }}}'
                    },
                    cfp_working_hour_start : '{{{ config_db_cached('settings::cfp_working_hour_start') }}}',
                    cfp_working_hour_end : '{{{ config_db_cached('settings::cfp_working_hour_end') }}}',
                };
            })();
        </script>
        <script src="{!! url('backend/js/autoNumeric.js') !!}" type="text/javascript"></script>
        <script src="{!! url('backend/js/accounting.min.js') !!}" type="text/javascript"></script>
        <script src="{!! url('backend/js/daySchedule.js') !!}" type="text/javascript"></script>
        <script src="{!! url('backend/js/fn.js') !!}" type="text/javascript"></script>
        <script src="{!! url('backend/js/app.js') !!}" type="text/javascript"></script>
        <script src="{!! url('backend/js/jscolor.js') !!}" type="text/javascript"></script>
        <!-- AdminLTE Skins. Choose a skin from the css/skins 
             folder instead of downloading all of them to reduce the load. -->
        <link href="{!! url('backend/css/skins/_all-skins.min.css') !!}" rel="stylesheet" type="text/css"/>
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// --><!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script><![endif]-->

    </head>
    <body class="skin-blue">
        <div class="wrapper">
            <header class="main-header">
                <!-- Logo -->
                <a href="{{ url(Lang::getLocale() . '/admin') }}" class="logo">{!! config_db_cached('settings::site_title') !!}</a>
                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top" role="navigation">
                    <!-- Sidebar toggle button-->
                    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button"> <span class="sr-only">Toggle navigation</span>
                    </a>
                    <!-- Navbar Right Menu -->
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">

                            <!-- User Account: style can be found in dropdown.less -->
                            <li class="dropdown messages-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-user"></i>
                                    <span class="hidden-xs">{{ Auth::user()->name }}</span> </a>
                                <ul class="dropdown-menu">
                                    @if(Entrust::can(['edit_profile']))
                                    <li>
                                        {!! HTML::decode( HTML::link(getLang().'/admin/profile', '<i class="fa fa-user"></i> <span class="hidden-xs">'.trans('app.profile').'</span>') ) !!}
                                    </li>
                                    @endif
                                    @if(Entrust::can(['change_password']))
                                    <li> 
                                        {!! HTML::decode( HTML::link(URL::route('admin.user.edit.password', array(Auth::user()->id)), '<i class="fa fa-pencil"></i> '.trans('app.change_password')) ) !!}
                                    </li>
                                    @endif
                                    <li>
                                        <a href="{{ url('/admin/logout') }}">
                                            <i class="fa fa-sign-out"></i>
                                            <span class="hidden-xs">Logout</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>

            @include('backend/layout/menu')

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                @yield('content')
            </div><!-- /.content-wrapper -->

            @include('backend/partial/footer')
        </div>
        <!-- ./wrapper -->

    </body>
</html>