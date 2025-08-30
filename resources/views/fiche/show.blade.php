@extends('adminlte::page')
@section('title','Fiche')
@section('content_header')<h1>Fiche</h1>@stop
@section('content')
  @include('partials._fiche_client', ['fiche'=>$fiche, 'client' => $client ?? null])
@stop
