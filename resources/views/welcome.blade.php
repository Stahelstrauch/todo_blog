@extends('layouts.app')

@section('content')    

    <div class="container py-4">
        <h2 class="mb-4">Tere tulemast</h2>

        @if($posts->count() === 0)
            <p>Hetkel pole avalikke postitusi.</p>
        @else
            <div class="row g-4">
                @foreach($posts as $post)
                    <div class="col-12 p-3 bg-warning border border-success col-md-6 col-lg-4">
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
                                
                                {{-- Kuupäev algne --}}
                                {{-- <div class="text-muted small mb-2">
                                    {{ $post->published_at_date }}
                                </div> --}}

                                <div class="d-flex justify-content-between align-items-center text-muted small mb-2">
                                    <span>{{ $post->published_at_date }}</span>

                                    @php
                                        $avg = $post->reactions_avg_value; // float|null
                                        $nearest = $avg !== null ? (int) round($avg) : null; // 1..3
                                        $nearest = ($nearest !== null) ? max(1, min(3, $nearest)) : null;
                                    @endphp

                                    @if($nearest !== null)
                                        <span
                                            title="Arvutuste keskmine ({{ number_format($avg, 2) }})"
                                            class="ms-2"
                                        >
                                            @if($nearest === 1)
                                                <i class="fa-solid fa-thumbs-down text-danger"></i>
                                            @elseif($nearest === 2)
                                                <i class="fa-solid fa-thumbs-up text-success"></i>
                                            @else
                                                <i class="fa-solid fa-heart text-warning"></i>
                                            @endif
                                        </span>
                                    @endif
                                </div>

                                <p class="card-text">
                                    {{ strip_tags($post->intro ?? '') }}
                                </p>

                                <div class="mt-auto">
                                    <a href="{{ route('posts.show', $post) }}" class="btn btn-success">
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
