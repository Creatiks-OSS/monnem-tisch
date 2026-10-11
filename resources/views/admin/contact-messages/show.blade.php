@extends('admin.layouts.app')
@section('title', $message->subject ?: 'Mesaj')
@section('heading', $message->subject ?: 'Kontakt mesajı')
@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <div class="admin-card space-y-2 p-6 text-sm">
            <p>
                <strong>{{ $message->name }}</strong>
                · {{ $message->email }}
                · {{ $message->phone ?: '—' }}
            </p>
            <p class="text-[var(--admin-muted)]">{{ $message->created_at->format('d.m.Y H:i') }}</p>
            <p>
                Etkinlik: {{ optional($message->event_date)->format('d.m.Y') ?: '—' }}
                · {{ $message->event_type ?: '—' }}
            </p>
            @if ($message->subject)
                <p>Konu: {{ $message->subject }}</p>
            @endif
            @if ($message->gclid)
                <p>gclid: {{ $message->gclid }}</p>
            @endif
            <div class="pt-3 border-t border-stone-100 whitespace-pre-wrap text-stone-700">{{ $message->message }}</div>
        </div>

        @if ($message->quote)
            <div class="admin-card p-6">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-xl">Teklif {{ $message->quote->quote_number }}</h2>
                    @if ($message->quote->total > 0)
                        <span class="text-sm text-[var(--admin-muted)]">
                            Toplam {{ number_format($message->quote->total, 2, ',', '.') }} €
                        </span>
                    @endif
                </div>
                <p class="mb-4 text-sm text-[var(--admin-muted)]">
                    Durum: {{ admin_label($message->quote->status) }}
                    · {{ $message->quote->persons ?: '—' }} Kişi
                    · {{ $message->quote->eventType?->name ?: $message->event_type }}
                </p>
                @forelse ($message->quote->items as $item)
                    <div class="flex justify-between gap-4 border-b border-stone-100 py-3 text-sm">
                        <span class="whitespace-pre-line">{{ $item->name }}</span>
                        <span class="shrink-0 font-medium">{{ number_format($item->line_total, 2, ',', '.') }} €</span>
                    </div>
                @empty
                    <p class="text-sm text-[var(--admin-muted)]">Henüz kalem yok. Yapay zekâ ile teklif oluşturun.</p>
                @endforelse
                @if ($message->quote->items->count())
                    <div class="mt-4 space-y-1 text-sm">
                        <div class="flex justify-between text-[var(--admin-muted)]">
                            <span>Netto</span>
                            <span>{{ number_format($message->quote->subtotal, 2, ',', '.') }} €</span>
                        </div>
                        @foreach ($message->quote->taxLines() as $taxLine)
                            <div class="flex justify-between text-[var(--admin-muted)]">
                                <span>MwSt. ({{ format_mwst_percent($taxLine['rate']) }}%)</span>
                                <span>{{ number_format($taxLine['amount'], 2, ',', '.') }} €</span>
                            </div>
                        @endforeach
                        <div class="flex justify-between font-semibold">
                            <span>Gesamt</span>
                            <span>{{ number_format($message->quote->total, 2, ',', '.') }} €</span>
                        </div>
                    </div>
                @endif
                @if ($message->quote->notes)
                    <div class="mt-5 rounded-xl bg-[#f7f1e8] p-4 text-sm text-stone-700">
                        {{ $message->quote->notes }}
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="space-y-3">
        @can('create', App\Models\Quote::class)
            @unless ($message->quote)
                <form method="POST" action="{{ route('admin.contact-messages.create-quote', $message) }}" class="admin-card space-y-3 p-5">
                    @csrf
                    <p class="text-sm text-[var(--admin-muted)]">
                        Bu mesajdan Angebot ile aynı teklif kaydını oluşturun. Ardından yapay zekâ teklifi üretip e-posta ile gönderebilirsiniz.
                    </p>
                    <button class="admin-btn admin-btn-gold w-full">Teklif kaydı oluştur</button>
                </form>
            @endunless

            <form method="POST" action="{{ route('admin.contact-messages.generate-quote', $message) }}" class="admin-card space-y-3 p-5" onsubmit="return confirm('{{ $message->quote?->items->count() ? 'Mevcut kalemlerin üzerine yazılacak. Yapay zekâ teklifi oluşturulsun mu?' : 'Yapay zekâ menü, yemek ve kişi sayısına göre teklif oluştursun mu?' }}')">
                @csrf
                <p class="text-sm text-[var(--admin-muted)]">
                    GPT-4o mini yalnızca menü paketleri ve yemeklerden teklif kurar. Kontakt formunda kişi sayısı yoksa buradan girebilirsiniz.
                </p>
                <label class="admin-label">Kişi sayısı</label>
                <input type="number" name="persons" min="1" max="500" value="{{ old('persons', $message->quote?->persons ?? 25) }}" class="admin-input">
                <button class="admin-btn admin-btn-gold w-full">Yapay zekâ ile teklif oluştur</button>
            </form>
        @endcan

        @if ($message->quote)
            <a href="{{ route('admin.quotes.show', $message->quote) }}" class="admin-btn-ghost admin-btn block w-full">Teklif sayfasını aç</a>
            <a href="{{ route('admin.quotes.pdf', $message->quote) }}" class="admin-btn-ghost admin-btn block w-full">PDF indir</a>
            @can('update', $message->quote)
                <form method="POST" action="{{ route('admin.contact-messages.send-pdf', $message) }}" onsubmit="return confirm('Teklif PDF müşteriye e-posta ile gönderilsin mi?')">
                    @csrf
                    <button class="admin-btn-ghost admin-btn w-full text-[var(--admin-terracotta)]" @disabled($message->quote->items->isEmpty())>PDF gönder</button>
                </form>
            @endcan
        @endif

        <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" class="pt-2" onsubmit="return confirm('Silinsin mi?')">
            @csrf
            @method('DELETE')
            <button class="admin-btn-ghost admin-btn w-full text-red-700">Sil</button>
        </form>
    </div>
</div>
@endsection
