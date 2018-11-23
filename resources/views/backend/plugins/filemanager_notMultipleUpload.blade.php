<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>File Manager</title>
    <link href="{!! url('backend/plugins/filemanager/styles/reset.css') !!}" rel="stylesheet" type="text/css"/>
    <link href="{!! url('backend/plugins/filemanager/scripts/jquery.filetree/jqueryFileTree.css') !!}" rel="stylesheet" type="text/css"/>
    <link href="{!! url('backend/plugins/filemanager/scripts/jquery.contextmenu/jquery.contextMenu-1.01.css') !!}" rel="stylesheet" type="text/css"/>
    <link href="{!! url('backend/plugins/filemanager/styles/filemanager.css') !!}" rel="stylesheet" type="text/css"/>
    <link href="{!! url('backend/css/filemanager.custom.css') !!}" rel="stylesheet" type="text/css"/>
    <!--[if IE 10]>
    <link href="{!! url('backend/plugins/filemanager/styles/ie10.css') !!}" rel="stylesheet" type="text/css"/>
    <![endif]-->
    <!--[if IE 9]>
    <link href="{!! url('backend/plugins/filemanager/styles/ie9.css') !!}" rel="stylesheet" type="text/css"/>
    <![endif]-->
    <!--[if lte IE 8]>
    <link href="{!! url('backend/plugins/filemanager/styles/ie8.css') !!}" rel="stylesheet" type="text/css"/>
    <![endif]-->
</head>
<script type="text/javascript">

</script>
<body>
<div>
    <form id="uploader" method="post">
        <button id="home" name="home" type="button" value="Home">&nbsp;</button>
        <h1></h1>

        <div id="uploadresponse"></div>
        <input id="mode" name="mode" type="hidden" value="add"/>
        <input id="currentpath" name="currentpath" type="hidden"/>

        <div id="file-input-container">
            <div id="alt-fileinput">
                <input id="filepath" name="filepath" type="text"/>
                <button id="browse" name="browse" type="button" value="Browse"></button>
            </div>
            <input id="newfile" name="newfile" type="file"/>
        </div>
        <button id="upload" name="upload" type="submit" value="Upload"></button>
        <button id="newfolder" name="newfolder" type="button" value="New Folder"></button>
        <button id="grid" class="ON" type="button">&nbsp;</button>
        <button id="list" type="button">&nbsp;</button>
    </form>
    <div id="splitter">
        <div id="filetree"></div>
        <div id="fileinfo">
            <h1></h1>
        </div>
    </div>
    <form name="search" id="search" method="get">
        <div>
            <input type="text" value="" name="q" id="q"/>
            <a id="reset" href="#" class="q-reset"></a>
            <span class="q-inactive"></span>
        </div>
    </form>

    <ul id="itemOptions" class="contextMenu">
        <li class="select"><a href="#select"></a></li>
        <li class="download"><a href="#download"></a></li>
        <li class="rename"><a href="#rename"></a></li>
        <li class="delete separator"><a href="#delete"></a></li>
    </ul>

    <script src="{!! url('backend/plugins/filemanager/scripts/jquery-1.8.3.min.js') !!}" type="text/javascript"></script>
    <script src="{!! url('backend/plugins/filemanager/scripts/jquery.form-3.24.js') !!}" type="text/javascript"></script>
    <script src="{!! url('backend/plugins/filemanager/scripts/jquery.splitter/jquery.splitter-1.5.1.js') !!}" type="text/javascript"></script>
    <script src="{!! url('backend/plugins/filemanager/scripts/jquery.filetree/jqueryFileTree.js') !!}" type="text/javascript"></script>
    <script src="{!! url('backend/plugins/filemanager/scripts/jquery.contextmenu/jquery.contextMenu-1.01.js') !!}" type="text/javascript"></script>
    <script src="{!! url('backend/plugins/filemanager/scripts/jquery.impromptu-3.2.min.js') !!}" type="text/javascript"></script>
    <script src="{!! url('backend/plugins/filemanager/scripts/jquery.tablesorter-2.7.2.min.js') !!}" type="text/javascript"></script>
    <script src="{!! url('backend/plugins/filemanager/scripts/filemanager.js') !!}" type="text/javascript"></script>
</div>
</body>
</html>
