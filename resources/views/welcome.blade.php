@extends('layouts.app')

@section('content')    

    <div class="container py-4">
        <h2 class="mb-4">Tere tulemast</h2>

        @if($posts->count() === 0)
            <p>Hetkel pole avalikke postitusi.</p>
        @else
            <div class="row g-4">
                @foreach($posts as $post)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100">
                            {{-- Pilt nii oma kui default --}}
                            <img
                                src="{{ $post->featured_image_path
                                    ? asset('storage/' . $post->featured_image_path)
                                    : asset('images/post-default.png') }}"
                                class="card-img-top"
                                alt="{{ $post->title }}"
                                style="object-fit: cover; height: 200px;"
                            >


                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">
                                    {{ $post->title }}
                                    @if(($post->comments_count ?? 0) > 0)
                                        <span class="text-muted">({{ $post->comments_count }})</span>
                                    @endif

                                </h5>
                                

                                <div class="text-muted small mb-2">
                                    {{ $post->published_at_date }}
                                </div>

                                <p class="card-text">
                                    {{ strip_tags($post->intro ?? '') }}
                                </p>

                                <div class="mt-auto">
                                    <a href="{{ route('posts.show', $post) }}" class="btn btn-primary">
                                        Loe edasi
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
@endsection
