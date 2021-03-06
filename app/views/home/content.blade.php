@extends('layouts.public.extendable')

@section('content')

<ol class="breadcrumb">
    <li><i class="fa fa-terminal"></i></li>
@if (count($ancestors))
    @foreach($ancestors as $v)
    <li>
        {{ HTML::page_or_link($v->path, $v->title) }}
    </li>
    @endforeach
    <li>
        {{ HTML::link_to_content(array(
            'id' => $content->id, 'title' => $content->title, 'slug' => $content->slug, 'path' => $v->path
        )) }}
    </li>
@endif
</ol>

<h1><a href="" title="{{ $content->title }}">{{ $content->title }}</a></h1>

@if(Session::get('is_admin'))
<a href="{{ URL::route('admin.content.edit', $content->id) }}" title="промени" class="fa fa fa-pencil-square-o" target="_blank"></a>
@endif

<hr />

<div id="content-rate-form">

    <div class="panel panel-default">
        <div class="panel-heading">
        <h4>
        @if ($content_rating != null)
            Оценка <strong>{{ $content_rating->rate }}</strong> от максимум 5.
            Общо гласували: <strong>{{ $content_rating->rating_count }}</strong>.
        @else
            Все още няма гласували
        @endif
        </h4>
        </div>
        <div class="panel-body">
            {{ Form::open(array('url' => URL::route('content.rate', $content->id))) }}
            @for($i = 1; $i <= 5; $i++)
                {{ Form::radio('rate', $i, false, array('id' => 'fr' . $i, 'title' => $rate_tooltip[$i], 'class' => 'pretty-radio')) }}
                <label class="radio-inline vote-{{ $i }}" title="{{ $rate_tooltip[$i] }}" for="fr{{ $i }}">
                {{ $rate_tooltip[$i] }}
                </label>
            @endfor
            {{ Form::submit('оцени', array('class' => 'btn btn-primary')) }}
            {{ Form::close() }}
        </div>
    </div>
    <div class="clearfix"></div>
</div>

@if ($errors->has('failure'))
<div class="alert alert-danger">
    {{ $errors->first('failure') }}
</div>
@elseif ($errors->has('success'))
<div class="alert alert-success">
    {{ $errors->first('success') }}
</div>
@endif

<div class="col-md-7">
    <dl class="dl-horizontal">
        <dt>добавен:</dt>
        <dd>{{ $content->created_at }}</dd>

        <dt>прегледи:</dt>
        <dd>{{ $content->hits }}</dd>

        <dt>автор:</dt>
        <dd><a href="#">{{ $content->created_by_alias }}</a></dd>
    </dl>
</div>

@if ($topic_url)
<div class="col-md-5">
    <a href="{{ $topic_url }}" target="_blank" class="btn btn-success" title="{{ $content->title }}">Дискусия за урока във форума</a>
</div>
@endif

<div class="clearfix"></div>

<hr />

{{ htmlspecialchars_decode(stripslashes($content->introtext)) }}
{{ HTML::strip_flowplayer(stripslashes(htmlspecialchars_decode($content->fullcontent))) }}

@foreach($videos as $video)
    {{ HTML::video($video->youtube) }}
    {{ HTML::flowplayer($video->name, $video->legacy) }}
@endforeach

@stop
