{{-- Login — mobile: mockup LoginMobile.dc.html; desktop (≥992): kartu dua kolom di tengah, mengikuti gaya login MyWahana --}}
<x-guest-layout>
    <main class="sg-login">
      <div class="sg-login__card">
        <section class="sg-login__hero" aria-label="SiGAP">
            <img src="{{ asset('images/brand/login-crusher.webp') }}" alt="" class="sg-login__photo" fetchpriority="high">
            <span class="sg-login__brand">
                <img src="{{ asset('images/brand/logo-sigap.png') }}" alt="" width="30" height="30">SIGAP
            </span>
            <p class="sg-login__tagline d-none d-lg-block">Laporan kerja harian, persetujuan berjenjang, dan helpdesk IT dalam satu aplikasi.</p>
        </section>

        <section class="sg-login__panel">
            <div class="sg-login__form" x-data="{ show: false, busy: false }">
                <div>
                    <h1 class="sg-login__title">Masuk ke SiGAP</h1>
                    <p class="sg-login__lead">Gunakan User ID dan kata sandi kantor Anda.</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success mb-0" role="status">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="d-flex flex-column gap-3" @submit="busy = true" novalidate>
                    @csrf
                    <div>
                        <label for="user_id" class="sg-login__label">User ID</label>
                        <input id="user_id" type="text" name="user_id" value="{{ old('user_id') }}" required autofocus
                               autocomplete="username" autocapitalize="off" spellcheck="false"
                               @class(['sg-login__input', 'is-invalid' => $errors->has('user_id')])
                               @error('user_id') aria-invalid="true" aria-describedby="user_id-error" @enderror>
                        @error('user_id')
                            <div id="user_id-error" class="sg-login__error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="sg-login__label">Kata sandi</label>
                        <div class="position-relative">
                            <input id="password" name="password" required autocomplete="current-password"
                                   :type="show ? 'text' : 'password'" type="password"
                                   @class(['sg-login__input pe-5', 'is-invalid' => $errors->has('password')])
                                   @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                            <button type="button" class="sg-login__eye" @click="show = !show"
                                    :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'" aria-label="Tampilkan kata sandi">
                                <i class="bi" :class="show ? 'bi-eye-slash' : 'bi-eye'" aria-hidden="true"></i>
                            </button>
                        </div>
                        @error('password')
                            <div id="password-error" class="sg-login__error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <label class="sg-login__remember" for="remember">
                            <input type="checkbox" id="remember" name="remember" @checked(old('remember'))> Ingat saya
                        </label>
                        <span class="small text-muted text-end">Lupa kata sandi? Hubungi admin.</span>
                    </div>

                    <button type="submit" class="sg-login__submit" :disabled="busy">
                        <span x-show="!busy">Masuk</span>
                        <span x-show="busy" x-cloak><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Memeriksa…</span>
                    </button>
                </form>

                <div class="sg-login__divider">Butuh bantuan IT?</div>
                <div class="d-flex gap-2">
                    <a href="{{ route('tickets.public.form') }}" class="sg-login__ghost"><i class="bi bi-ticket-perforated" aria-hidden="true"></i>Ajukan Tiket</a>
                    <a href="{{ route('tickets.public.track') }}" class="sg-login__ghost"><i class="bi bi-search" aria-hidden="true"></i>Lacak Tiket</a>
                </div>
                <p class="small text-muted text-center mb-0">Tiket IT bisa diajukan tanpa login.</p>

                {{-- Banner pasang PWA: hanya muncul jika browser menawarkan instalasi --}}
                <div class="sg-login__install" x-data="{ prompt: null }" x-show="prompt" x-cloak
                     @beforeinstallprompt.window.prevent="prompt = $event">
                    <span class="sg-login__install-icon" aria-hidden="true"><i class="bi bi-download"></i></span>
                    <span class="flex-grow-1 min-w-0 lh-sm">
                        <b class="d-block">Pasang aplikasi</b>
                        <span class="sg-login__install-text">Buka dari layar utama, lebih cepat dibuka.</span>
                    </span>
                    <button type="button" class="sg-login__install-btn" @click="prompt.prompt(); prompt = null">Pasang</button>
                </div>
            </div>
        </section>
      </div>
    </main>
</x-guest-layout>
