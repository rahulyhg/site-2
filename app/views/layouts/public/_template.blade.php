<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="utf-8">
    <title>{{ $page_title }} - Научи ме!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ URL::asset('favicon.ico') }}" rel="shortcut icon" type="image/vnd.microsoft.icon" >

    <meta property="og:title" content="{{ $page_title }}" />
    <meta property="og:url" content="{{ Request::url() }}" />
    <meta property="og:image" content="{{ $page_image }}" />
@if (isset($videos))
@foreach($videos as $video)
    <meta property="og:type" content="video.other" >
    <meta property="og:video" content="{{ $video->name }}" >
    <meta property="og:video:type" content="video/mp4" >
@endforeach
@endif

    <meta name="keywords" content="{{ isset($page_keywords)
                                    ? trim($page_keywords, ', ')
                                    : 'php, ООП, mvc, mysql, sql, nosql, javascript, ajax, jquery, linux' }}">
    <meta name="description" content="{{ !empty($page_desc) ? $page_desc : 'Нау4и.ме - Видео уроци на български.' }}">

    <!-- Cascase Style Sheets -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="{{ URL::asset('css/common.css') }}">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">

    <!-- player skin -->
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('packages/flowplayer-5.4.6/skin/playful.css') }}">

    <!-- flowplayer depends on jQuery 1.7.1+ (for now) -->
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script type="text/javascript" src="{{ URL::asset('js/scripts.js') }}"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

    <!-- include flowplayer -->
    <script type="text/javascript" src="{{ URL::asset('packages/flowplayer-5.4.6/flowplayer.min.js') }}"></script>
</head>
<body>
    <div class="container-fluid">
        <header class="navbar navbar-fixed-top navbar-default">
            <nav role="navigation">
                <!-- Some serious JavaScript responsive stuff -->
                <div class="navbar-header">
                    <button type="button" data-target="#toggleNav" data-toggle="collapse" class="navbar-toggle">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="{{ URL::route('home') }}">
                        <img src="{{ URL::asset('img/logo.png') }}" alt="nau4i.me" title="Начало - Научи ме!"/>
                    </a>
                </div>
                <div id="toggleNav" class="collapse navbar-collapse">
                    <div id="navbarCollapse" class="navbar-collapse collapse in">
                        <ul class="nav navbar-nav navbar-right text-uppercase">
                            <li>
                            {{ Form::open(array(
                                'url' => URL::route('search'),
                                'method' => 'GET',
                                'role' => 'search',
                                'class' => 'navbar-form navbar-left')
                            ) }}
                            <div id="main-search" class="form-group">
                                <input autocomplete="off" type="text" name="find" placeholder="&#xF002 търси" required="required" class="form-control" id="main-search-input">
                            </div>
                            {{ Form::close() }}
                            </li>
                            <li {{ (isset($active) && $active == 'home') ? 'class="active"' : null }}>
                                <a href="{{ URL::route('home') }}" title="начало">начало</a>
                            </li>
                            <li {{ (isset($active) && $active == 'about') ? 'class="active"' : null }}>
                                <a href="{{ URL::route('about') }}" title="за нас">за нас</a>
                            </li>
                            @if (isset($topmenu))
                                @foreach ($topmenu as $v)
                            <li {{ (isset($active) && $active == $v->id) ? 'class="active"' : null }}>
                                {{ HTML::page_or_link($v->path, $v->title) }}
                            </li>
                                @endforeach
                            @endif
                            <li {{ (isset($active) && $active == 'sitemap') ? 'class="active"' : null }}>
                                <a href="{{ URL::route('sitemap') }}" title="карта на сайта">карта на сайта</a>
                            </li>
                            @if (Auth::user())
                                <li><a href="{{ URL::route('admin') }}" title="администрация">администрация</a></li>
                                <li><a class="fa fa-user" href="{{ URL::to('/forum/index.php?action=profile') }}" title="SMF Профил"></a></li>
                                <li><a class="fa fa-power-off" href="{{ URL::route('admin.logout') }}" title="Изход"></a></li>
                            @else
                                <li><a class="fa fa-unlock-alt" href="{{ URL::route('admin') }}" title="Вход"></a></li>
                            @endif
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
    </div>
    <div class="container-fluid">
        <div class="row">
            @yield('main')
        </div>
    </div>
    @include('layouts.public._footer')
</body>
</html>