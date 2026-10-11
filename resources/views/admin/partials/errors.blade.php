@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
        <p class="font-medium mb-1">Lütfen girişleri kontrol edin:</p>
        <ul class="list-disc pl-5 space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
