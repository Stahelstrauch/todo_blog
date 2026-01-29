@extends('layouts.app')

@section('content')
    
    <div class="container py-4">

        <article class="blog-post">

            {{-- Pealkiri --}}
            <h1 class="mb-2">
                {{ $post->title }}
            </h1>

            {{-- Avaldamise aeg --}}
            @if($post->published_at)
                <div class="text-muted mb-3">
                    Avaldatud: {{ $post->published_at_date }}
                </div>
            @endif

            {{-- Featured image --}}
            @if(!empty($post->featured_image_path))
                <div class="mb-4 text-center">
                    <img
                        src="{{ asset('storage/' . $post->featured_image_path) }}"
                        alt="{{ $post->title }}"
                        class="img-fluid rounded"
                        style="max-width: 720px;"
                    >
                </div>
            @endif

            {{-- Sissejuhatus --}}
            @if(!empty($post->intro))
                <div class="lead mb-4">
                    {{ $post->intro }}
                </div>
            @endif

            {{-- Sisu --}}
            <div class="post-body">
                {!! $post->body_html !!}
            </div>

            {{-- Reaktsioonid --}}
            {{-- @php
                $reactionsEnabled = (bool) \App\Models\Setting::get('reactions.enabled', 1);
                $myReactionValue  = optional($post->myReaction)->value;

                $c1 = $post->reaction_1_count ?? 0;
                $c2 = $post->reaction_2_count ?? 0;
                $c3 = $post->reaction_3_count ?? 0;
                $total = $post->reactions_total ?? ($c1 + $c2 + $c3);
            @endphp

            @if ($errors->has('reaction'))
                <div class="alert alert-danger mt-4 mb-0">
                    {{ $errors->first('reaction') }}
                </div>
            @endif --}}

            <div class="mt-4 pt-3 border-top">
                <div class="d-flex flex-wrap align-items-center gap-2">

                    @guest
                        <div class="ms-auto text-muted small">
                            Reageerimiseks logi sisse.
                        </div>
                    @endguest

                </div>
            </div>

        </article>

    </div>

    <hr>

    <h3>Kommentaarid</h3>

    @php
        $comments = $post->comments()
            ->where('is_hidden', false)
            ->latest()
            ->get();
    @endphp

    @forelse($comments as $c)
        <div class="mb-3">
            <strong>{{ $c->user->name ?? 'Kasutaja' }}</strong>
            <small class="text-muted">{{ $c->created_at?->format('d.m.Y H:i') }}</small>
            <div>{{ $c->comment }}</div>
        </div>
    @empty
        <p class="text-muted">Kommentaare veel pole.</p>
    @endforelse

    @include('partials.comment-form', ['post' => $post])

@endsection
