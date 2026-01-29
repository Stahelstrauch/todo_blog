@php
    use App\Models\Setting;
@endphp

@if(!Setting::get('comments.enabled', true))
    <p class="text-muted">Kommenteerimine on hetkel välja lülitatud.</p>
@else
    @auth
        {{-- @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif --}}

        <form method="POST" action="{{ route('comments.store', $post) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Kommentaar</label>
                <textarea name="comment"
                          class="form-control"
                          rows="5"
                          minlength="3"
                          required>{{ old('comment') }}</textarea>

                @error('comment')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Saada</button>
        </form>
    @else
        <p>
            Kommenteerimiseks palun <a href="{{ route('login') }}">logi sisse</a>.
        </p>
    @endauth
@endif
