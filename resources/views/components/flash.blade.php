@php
    $isError = ! session()->has('success') && session()->has('error');
    $msg = session('success') ?? session('error');
@endphp

@if ($msg)
    <div class="toast-container position-fixed bottom-0 start-50 translate-middle-x p-3">
        <div id="flash-toast" class="toast toast-itam {{ $isError ? 'err' : '' }} show"
             role="{{ $isError ? 'alert' : 'status' }}" aria-live="polite" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">{{ $msg }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                new bootstrap.Toast(document.getElementById('flash-toast'), { delay: 4000 }).show();
            });
        </script>
    @endpush
@endif
