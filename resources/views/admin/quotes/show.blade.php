@extends('admin.layouts.app')
@section('title', $quote->quote_number)
@section('heading', 'Talep '.$quote->quote_number)
@section('subheading', $quote->status ? admin_label($quote->status) : '')
@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <div class="admin-card space-y-2 p-6 text-sm">
            <p>
                <strong>{{ $quote->first_name }} {{ $quote->last_name }}</strong>
                · {{ $quote->email }}
                · {{ $quote->phone }}
            </p>
            <p>{{ $quote->company }}</p>
            <p>
                Etkinlik: {{ optional($quote->event_date)->format('d.m.Y') }}
                {{ $quote->event_time }}
                · {{ $quote->persons ?: '—' }} Kişi
                · {{ $quote->location }}
                · {{ $quote->eventType?->name }}
            </p>
            <p>
                Bütçe:
                @if ($quote->budget)
                    {{ number_format($quote->budget, 2, ',', '.') }} €
                @else
                    belirtilmedi — yapay zekâ kişi sayısına göre teklif üretir
                @endif
            </p>
            @if ($quote->message)
                <p class="pt-2 text-stone-600">{{ $quote->message }}</p>
            @endif
            @if ($quote->catering_wunsch)
                <p class="text-stone-600">İstek: {{ $quote->catering_wunsch }}</p>
            @endif
            @if ($quote->source === 'kontakt')
                <p class="pt-1 text-stone-500">
                    Kaynak: Kontakt formu
                    @if ($quote->contactMessage)
                        · <a href="{{ route('admin.contact-messages.show', $quote->contactMessage) }}" class="text-[var(--admin-terracotta)]">Mesaja git</a>
                    @endif
                </p>
            @endif
        </div>

        <div class="admin-card p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl">Teklif kalemleri</h2>
                @if ($quote->total > 0)
                    <span class="text-sm text-[var(--admin-muted)]">
                        Toplam {{ number_format($quote->total, 2, ',', '.') }} €
                        @if ($quote->budget)
                            · bütçe {{ number_format($quote->budget, 2, ',', '.') }} €
                        @endif
                    </span>
                @endif
            </div>
            @forelse ($quote->items as $item)
                <div class="flex justify-between gap-4 border-b border-stone-100 py-3 text-sm">
                    <span class="whitespace-pre-line">{{ $item->name }}</span>
                    <span class="shrink-0 font-medium">{{ number_format($item->line_total, 2, ',', '.') }} €</span>
                </div>
            @empty
                <p class="text-sm text-[var(--admin-muted)]">Henüz kalem yok. Yapay zekâ ile teklif oluşturun.</p>
            @endforelse
            @if ($quote->items->count())
                <div class="mt-4 space-y-1 text-sm">
                    <div class="flex justify-between text-[var(--admin-muted)]">
                        <span>Netto</span>
                        <span>{{ number_format($quote->subtotal, 2, ',', '.') }} €</span>
                    </div>
                    @foreach ($quote->taxLines() as $taxLine)
                        <div class="flex justify-between text-[var(--admin-muted)]">
                            <span>MwSt. ({{ format_mwst_percent($taxLine['rate']) }}%)</span>
                            <span>{{ number_format($taxLine['amount'], 2, ',', '.') }} €</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between font-semibold">
                        <span>Gesamt</span>
                        <span>{{ number_format($quote->total, 2, ',', '.') }} €</span>
                    </div>
                </div>
            @endif
            @if ($quote->notes)
                <div class="mt-5 rounded-xl bg-[#f7f1e8] p-4 text-sm text-stone-700">
                    {{ $quote->notes }}
                </div>
            @endif
        </div>
    </div>
    <div class="space-y-3">
        <form method="POST" action="{{ route('admin.quotes.generate', $quote) }}" class="admin-card space-y-3 p-5" onsubmit="return confirm('{{ $quote->items->count() ? 'Mevcut kalemlerin üzerine yazılacak. Yapay zekâ teklifi oluşturulsun mu?' : 'Yapay zekâ menü, yemek ve bütçeye göre teklif oluştursun mu?' }}')">
            @csrf
            <p class="text-sm text-[var(--admin-muted)]">
                GPT-4o mini yalnızca menü paketleri ve yemeklerden teklif kurar — içecek, Geschirr veya diğer ekstralar eklenmez. Bütçe yoksa kişi sayısına göre paket önerir.
            </p>
            <button class="admin-btn admin-btn-gold w-full">Yapay zekâ ile teklif oluştur</button>
        </form>
        <form method="POST" action="{{ route('admin.quotes.update-status', $quote) }}" class="admin-card space-y-3 p-5">
            @csrf
            @method('PATCH')
            <label class="admin-label">Durum</label>
            <select name="status" class="admin-select">
                @foreach (['neu', 'kontaktiert', 'angebot_erstellt', 'verhandlung', 'gewonnen', 'verloren'] as $s)
                    <option value="{{ $s }}" @selected($quote->status === $s)>{{ admin_label($s) }}</option>
                @endforeach
            </select>
            <select name="assigned_user_id" class="admin-select">
                <option value="">Atanan…</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}" @selected($quote->assigned_user_id == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
            <button class="admin-btn w-full">Durumu kaydet</button>
        </form>
        <a href="{{ route('admin.quotes.pdf', $quote) }}" class="admin-btn-ghost admin-btn block w-full">PDF indir</a>
        <form method="POST" action="{{ route('admin.quotes.send-pdf', $quote) }}">
            @csrf
            <button class="admin-btn-ghost admin-btn w-full text-[var(--admin-terracotta)]">PDF gönder</button>
        </form>
        <form method="POST" action="{{ route('admin.quotes.convert', $quote) }}">
            @csrf
            <button class="admin-btn w-full">Siparişe dönüştür</button>
        </form>
        <a href="{{ route('admin.quotes.edit', $quote) }}" class="admin-btn-ghost admin-btn block w-full">Düzenle</a>
    </div>
</div>
@endsection
