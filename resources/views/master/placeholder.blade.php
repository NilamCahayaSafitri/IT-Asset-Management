<x-master-layout>
    <div class="card">
        <div class="card-body" style="padding:28px">
            <p style="font-weight:600;margin:0 0 6px">Tab {{ $label }} sedang disiapkan.</p>
            @if (!empty($plan))
                <p style="margin:0 0 8px;color:#66707d">Rencana isi:</p>
                <ul style="margin:0;padding-left:20px;color:#66707d">
                    @foreach ($plan as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-master-layout>
