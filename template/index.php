<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="google" content="notranslate">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="robots" content="index, nofollow">
    <title>SeMin</title>
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.min.css" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/css/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/themes/explorer/theme.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/octicons/4.4.0/font/octicons.css">
    <link rel="stylesheet" type="text/css" href="./static/stylesheet/pincode.css">
    <link rel="stylesheet" type="text/css" href="./static/stylesheet/index.css">
</head>

<body>
    <div id="modal-list">
    </div>

    <div id="outter-wrapper" class="container-xl">
        <div class="row rounded shadow-sm" style="height: 100%; margin: 0;">
            <div id="sidebar" class="col-3 float-left pe-3">
                <ul class="filter-list" id="sidebar-menu">
                </ul>
            </div>
            <div id="contents" class="col-9 float-left ps-2">
                <div id="alert-box">
                </div>
                <div id="loading-icon"  style="margin-top: 10px;margin-left: 48%;">
                    <div class="spinner-border" role="status"></div>
                </div>
                <div id="content-wrapper">
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="//unpkg.com/axios/dist/axios.min.js"></script>
    <script id="markdown-script" src="//cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./static/javascript/weather.js"></script>
    <script src="./static/javascript/main.js"></script>
    <script src="./static/javascript/pincode.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/plugins/buffer.min.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/plugins/filetype.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/plugins/piexif.min.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/plugins/sortable.min.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/fileinput.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/locales/LANG.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/themes/explorer/theme.min.js"></script>
</body>

</html>