<!DOCTYPE html>
<!--[if IE 9]><html class="lt-ie10" lang="en" > <![endif]-->
<html class="no-js" lang="en" >

<head>
  <meta charset="utf-8">
  <!-- If you delete this meta tag World War Z will become a reality -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fundtastic</title>

  <!-- If you are using the gem version, you need this only -->
  <link rel="stylesheet" href="{{ elixir('css/app.css') }}">

  <script src="{{ asset('js/vendor/fastclick.js') }}"></script>
  <script src="{{ asset('js/vendor/modernizr.js') }}"></script>
  <script>
    var site = (function() {
        return{
            base_url : '{{{ url('/') }}}',
            lang: {
                save : '{{{ trans('app.save') }}}',
                update : '{{{ trans('app.update') }}}',
                add : '{{{ trans('app.add') }}}',
                city : '{{{ trans('routes.city') }}}'
            },
        };
    })();
</script>
</head>
<body>