<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100">

    {{-- SUCCESS --}}
    @if (session('success'))
        <div class="toast align-items-center text-bg-success border-0 show mb-2" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    {{ session('success') }}
                </div>
                <button type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"></button>
            </div>
        </div>
    @endif

    {{-- ERROR (globaalne) --}}
    @if ($errors->any())
        <div class="toast align-items-center text-bg-danger border-0 show" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    {{ $errors->first() }}
                </div>
                <button type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast"></button>
            </div>
        </div>
    @endif

</div>
<script>
    @push('scripts')
<script>    
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.toast').forEach(function (toastEl) {
            new bootstrap.Toast(toastEl, {
                delay: 10000 // 10 sek.
            }).show();
        });
    });
</script>
@endpush
</script>
