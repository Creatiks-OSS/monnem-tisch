<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giriş · Monnemtisch Yönetim</title>
    <x-favicon />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'DM Sans', system-ui, sans-serif; }
        .brand { font-family: 'Cormorant Garamond', Georgia, serif; }
    </style>
</head>
<body class="min-h-screen bg-[#161412] text-white">
    <div class="grid min-h-screen lg:grid-cols-2">
        <div class="relative hidden overflow-hidden lg:block">
            <img src="{{ velora_photo('hero') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-[#161412] via-[#161412]/40 to-transparent"></div>
            <div class="absolute bottom-12 left-12 right-12">
                <p class="brand text-5xl leading-tight">Catering,<br><em class="italic text-[#c4a574]">kontrolliert.</em></p>
                <p class="mt-4 max-w-sm text-sm text-white/70">Menü paketleri, yemek kotaları ve siparişler — tek panel.</p>
            </div>
        </div>
        <div class="flex items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-md">
                <div class="mb-10">
                    @if(site_logo_url())
                        <img src="{{ site_logo_url() }}" alt="Monnemtisch Catering" class="h-14 w-auto max-w-[260px] rounded-xl bg-white px-3 py-2 object-contain object-left">
                    @else
                        <div class="brand text-4xl text-[#c4a574]">Monnemtisch</div>
                    @endif
                    <p class="mt-2 text-sm tracking-[0.18em] uppercase text-white/45">Yönetim paneli</p>
                </div>
                <form method="POST" action="{{ route('admin.login') }}" class="rounded-[1.6rem] bg-[#fffcf8] text-stone-900 p-8 shadow-2xl space-y-5">
                    @csrf
                    @if($errors->any())
                        <div class="rounded-xl bg-red-50 text-red-700 text-sm px-3 py-2">{{ $errors->first() }}</div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium mb-1.5">E-posta</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full rounded-xl border border-[#e4d9c8] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#c4a574]/40">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5">Şifre</label>
                        <input type="password" name="password" required
                               class="w-full rounded-xl border border-[#e4d9c8] px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#c4a574]/40">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="remember" value="1" class="rounded border-stone-300"> Beni hatırla
                    </label>
                    <button type="submit" class="w-full rounded-full bg-[#161412] text-white py-3 font-medium hover:bg-black transition">
                        Giriş yap
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
