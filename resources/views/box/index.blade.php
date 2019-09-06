@extends('layouts.app')

@section('title','La boite à idées | jvscript.fr')

@section('content')

@section('javascript')
<script>
    $(document).ready(function () {
        //envoie du formulaire de commentaire en ajax
        $(document).on('submit', '.ajax-comment', function (e) {
            e.preventDefault();
            var $this = $(this);
            var id_idea = $this.attr("id-item"); //id de l'idée
            $.ajax({
                url: $this.attr('action'),
                type: $this.attr('method'),
                data: $this.serialize(),
                dataType: 'json', // JSON
                success: function (data) {
                    if (data != "") {
//                        $("#comment-" + id_item).html(data);
                        $("#comment-" + id_idea).html(data.html);
                        $("#comment-count-" + id_idea).text(data.count);
                    }
                }
            });
        });
        //delete comment en ajax
        $(document).on('click', 'a[name="delete-comment"]', function (e) {
            e.preventDefault();
            var $this = $(this);
            var id_idea = $this.attr("data-idea-id"); //id de l'idée
            var id_comment = $this.attr("data-comment-id"); //id du commentaire
            console.log("url : " + $this.attr('href'));
            $.ajax({
                url: $this.attr('href'),
                type: 'GET',
                data: $this.serialize(),
                dataType: 'json', // JSON
                success: function (data) {
                    if (data != "") {
                        $("#comment-" + id_idea).html(data.html);
                        $("#comment-count-" + id_idea).text(data.count);
                    } else {
                        //_TODO show error wait 30 seconde
                    }
                }
            });
        });
        //ajax pagination commentaire
        function getComments(id_idea, page) {
            $.ajax({
                url: '?id_idea=' + id_idea + '&page=' + page,
                dataType: 'json',
            }).done(function (data) {
                $("#comment-" + id_idea).html(data.html);
                $("#comment-count-" + id_idea).text(data.count);
            }).fail(function () {
                console.log("Erreur lors de l'affichage des commentaires.");
            });
        }

        $(document).on('click', '.pagination a', function (e) {
            var page = $(this).attr('href').split('page=')[1].replace(/#\d*/, '');
            var id_idea = $(this).attr('href').split('#')[1];
            getComments(id_idea, page);
            e.preventDefault();
        });
    });
</script>
@endsection

<style>
    .vcenter {
        display: flex;
        align-items: center;
    }
</style>

<div class="row">

    <div class="col-md-8 col-md-offset-2">

        @if (session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
        @endif

        <p class="text-center">
            <a href="{{route("box.form")}}" class="btn btn-default">Proposer une idée <i class="fa fa-lightbulb-o" aria-hidden="true"></i>
            </a><br><br>
        </p>
        <?php
        $types_label = ['Script', 'Skin'];
        ?>

        @foreach ($ideas as $key => $idea)

        <?php
        $disliked = $liked = 0;
        if (!Auth::guest()) {
            $liked = $idea->likes()->where(['user_id' => Auth::user()->id, 'liked' => true])->count();
            $disliked = $idea->likes()->where(['user_id' => Auth::user()->id, 'liked' => false])->count();
        }
        ?>

        <div class="panel-body">
            <div class="row">

                <div class="col-xs-2 text-center" style="padding-top:44px; ">

                    <a class="{{$liked ? '' : 'like'}} center-block"   href="#" onclick="document.getElementById('like-{{$idea->id}}').submit(); return false;"><i class="fa fa-arrow-up" aria-hidden="true"></i></a>
                    <b class="note center-block">
                        {{$idea->likes()->where('liked',1)->count() - $idea->likes()->where('liked',0)->count()}}
                    </b> <a class="{{$disliked ? '' : 'dislike'}} center-block" href="#" onclick="document.getElementById('dislike-{{$idea->id}}').submit(); return false;"> <i class="fa fa-arrow-down" aria-hidden="true"></i> </a>

                    <form id="like-{{$idea->id}}" action="{{route('box.like',['id' => $idea->id])}}" method="POST" style="display: none;">
                        {{ csrf_field() }}
                        <input type="submit" name="like-{{$idea->id}}" style="display: none;" />
                    </form>

                    <form id="dislike-{{$idea->id}}" action="{{route('box.dislike',['id' => $idea->id, 'dislike' => true])}}" method="POST" style="display: none;">
                        {{ csrf_field() }}
                        <input type="submit" name="dislike-{{$idea->id}}" style="display: none;" />
                    </form>
                </div>
                <div class="col-xs-10">
                    <div class="panel idea">
                        <div class="panel-heading idea " style='text-align: left'>
                            [{{$types_label[$idea->type]}}]
                            {{str_limit($idea->title,50)}}

                            <span class="date pull-right hidden-xs">
                                Par {{$idea->user()->first()->name}} le
                                {{$idea->created_at->format('d/m/Y')}}
                            </span>
                        </div>
                        <div class="panel-body idea" style="  word-wrap: break-word;  ">{{str_limit($idea->description,150,'')}}
                            @if(strlen($idea->description) > 150)
                            <a class="btn btn-default" type="button" data-toggle="collapse" data-target="#description-{{$idea->id}}" aria-expanded="false" aria-controls="collapseExample">
                                Lire la suite...
                            </a>
                            <div class="collapse" id='description-{{$idea->id}}'>
                                {{substr($idea->description,153)}}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-2 text-center">
                </div>
                <div class="col-xs-10">

                    <div class="panel idea btn-com ments">
                        <a class="btn btn-default" type="button" data-toggle="collapse" data-target="#comment-{{$idea->id}}" aria-expanded="false" aria-controls="collapseExample">
                            <i class="fa fa-comment" aria-hidden="true"></i>   <span id="comment-count-{{$idea->id}}">{{$idea->comments()->count()}}</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="panel idea btn-comments">
                        <div class="collapse" id="comment-{{$idea->id}}">
                            @include('global.comments-idea', [ 'comments' =>  $idea->comments()->latest()->paginate(5) , 'commentClass' => ' ' , 'recaptcha' => 1])
                        </div>
                    </div>
                </div>
            </div>


        </div>

        @endforeach

    </div>



</div>


@endsection
