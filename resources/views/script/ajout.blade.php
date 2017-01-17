@extends('layouts.app')

@section('content') 



<div class="page-header" id="banner">
    <div class="row">
        <div class="col-md-12">

            <h1>Ajouter un script</h1>

            @if (session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
            @endif

            <form class="form-horizontal" role="form" method="POST" action="{{ route('script.store') }}">
                {{ csrf_field() }}

                <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                    <label for="name" class="col-md-4 control-label">Nom du script *</label>

                    <div class="col-md-6">
                        <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus>

                        @if ($errors->has('name'))
                        <span class="help-block">
                            <strong>{{ $errors->first('name') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group{{ $errors->has('autor') ? ' has-error' : '' }}">
                    <label for="autor" class="col-md-4 control-label">Auteur du script </label>

                    <div class="col-md-6">
                        <input id="autor" type="text" class="form-control" name="autor" value="{{ old('autor') }}">

                        @if ($errors->has('autor'))
                        <span class="help-block">
                            <strong>{{ $errors->first('autor') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>


                <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
                    <label for="description" class="col-md-4 control-label">Description</label>

                    <div class="col-md-6">
                        <textarea id="description"   class="form-control" name="description" >{{ old('description') }}</textarea>

                        @if ($errors->has('description'))
                        <span class="help-block">
                            <strong>{{ $errors->first('description') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group{{ $errors->has('js_url') ? ' has-error' : '' }}">
                    <label for="js_url" class="col-md-4 control-label">Lien du script (.js) *</label>

                    <div class="col-md-6">
                        <input id="js_url" type="text" placeholder="http://.../usercript.js" class="form-control" name="js_url" value="{{ old('js_url') }}" required autofocus>

                        @if ($errors->has('js_url'))
                        <span class="help-block">
                            <strong>{{ $errors->first('js_url') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group{{ $errors->has('repo_url') ? ' has-error' : '' }}">
                    <label for="repo_url" class="col-md-4 control-label">Lien du repository </label>

                    <div class="col-md-6">
                        <input id="repo_url" type="text" placeholder="http://github.com/..." class="form-control" name="repo_url" value="{{ old('repo_url') }}" >

                        @if ($errors->has('repo_url'))
                        <span class="help-block">
                            <strong>{{ $errors->first('repo_url') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group{{ $errors->has('photo_url') ? ' has-error' : '' }}">
                    <label for="photo_url" class="col-md-4 control-label">Lien vers le logo/image </label>

                    <div class="col-md-6">
                        <input id="photo_url" type="text" placeholder="http://image.noelshack.com/..." class="form-control" name="photo_url" value="{{ old('photo_url') }}"  >

                        @if ($errors->has('photo_url'))
                        <span class="help-block">
                            <strong>{{ $errors->first('photo_url') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group{{ $errors->has('user_email') ? ' has-error' : '' }}">
                    <label for="user_email" class="col-md-4 control-label">Votre email pour être notifié de la publication du script </label>

                    <div class="col-md-6">
                        <input id="user_email" type="email" placeholder="email@domaine.fr" class="form-control" name="user_email" value="{{ old('user_email') }}"  >

                        @if ($errors->has('user_email'))
                        <span class="help-block">
                            <strong>{{ $errors->first('user_email') }}</strong>
                        </span>
                        @endif
                    </div>
                </div> 

                <div class="form-group">
                    <div class="col-md-6 col-md-offset-4">
                        <button type="submit" class="btn btn-primary">
                            Ajouter
                        </button>
                    </div>
                </div>
            </form>


        </div>
    </div>

</div>


@endsection