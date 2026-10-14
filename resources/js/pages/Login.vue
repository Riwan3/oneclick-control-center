<script setup>
import { computed, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import UiCard from '../components/ui/UiCard.vue';
import UiButton from '../components/ui/UiButton.vue';
import { authService } from '../services/authService';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const username = ref('');
const password = ref('');

// Set only when the router redirected here after a mid-session 401/419
// (see resources/js/app.js's registerSessionExpiredHandler) — a normal
// "not logged in yet" visit never carries this query param.
const sessionMessage = computed(() => {
    if (route.query.reason === 'session_expired') {
        return 'Your session has expired. Please log in again.';
    }
    if (route.query.reason === 'csrf_expired') {
        return 'Your session expired while this page was open. Please log in again.';
    }
    return null;
});

const showPassword = ref(false);

const fieldClasses =
    'w-full rounded-xl border border-border bg-surface pl-11 pr-11 py-3.5 text-sm text-text-primary placeholder:text-text-muted/70 transition-colors focus:outline-none focus:ring-2 focus:ring-primary/20';

// "Lupa Password": a request is sent to the Super Admin, who verifies
// the requester in User Management and resets the password.
const mode = ref('login');
const forgot = ref({ username: '', full_name: '', position: '', phone: '' });
const forgotBusy = ref(false);
const forgotError = ref(null);
const forgotDone = ref(null);

const plainFieldClasses =
    'w-full rounded-xl border border-border bg-surface px-4 py-3 text-sm text-text-primary placeholder:text-text-muted/70 transition-colors focus:outline-none focus:ring-2 focus:ring-primary/20';

function openForgot() {
    forgotError.value = null;
    forgotDone.value = null;
    mode.value = 'forgot';
}

async function handleForgot() {
    forgotBusy.value = true;
    forgotError.value = null;
    const { ok, data } = await authService.requestPasswordReset(forgot.value);
    if (ok) {
        forgotDone.value = data?.message ?? 'Permintaan terkirim.';
        forgot.value = { username: '', full_name: '', position: '', phone: '' };
    } else {
        forgotError.value =
            Object.values(data?.errors ?? {}).flat()[0] ?? data?.message ?? 'Gagal mengirim permintaan. Coba lagi.';
    }
    forgotBusy.value = false;
}

async function handleSubmit() {
    const success = await auth.login(username.value, password.value);
    if (success) {
        const redirectTo = typeof route.query.redirect === 'string' ? route.query.redirect : '/';
        router.push(redirectTo);
    }
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-end bg-cover bg-center bg-no-repeat px-4 pr-6 sm:pr-12 md:pr-24 lg:pr-32 xl:pr-40" style="background-image: url('/login-bg.png');">
        <UiCard class="w-full max-w-[420px] shadow-2xl" padding="lg">
            <div class="mb-6 text-center">
                <img src="../../images/logo-oneclick.png" alt="OneClick Logo" class="mx-auto h-20 w-auto object-contain" />
                <h1 class="text-page-title -mt-1 font-bold leading-tight tracking-tight"><span class="block text-maroon">OneClick</span><span class="block text-xl text-text-primary">Control Center</span></h1>
                <div class="mt-2 flex items-center gap-3 text-sm font-medium text-text-secondary"><span class="h-px flex-1 bg-border"></span><span>PPA Group</span><span class="h-px flex-1 bg-border"></span></div>
                
                <div class="mt-4 border-t border-border/50 pt-4">
                    <h2 class="text-base font-bold text-text-primary">{{ mode === 'login' ? 'Selamat Datang Kembali!' : 'Lupa Password' }}</h2>
                    <p class="text-xs text-text-muted mt-1">{{ mode === 'login' ? 'Silakan masuk untuk melanjutkan' : 'Isi data berikut, Super Admin akan memverifikasi dan mereset password Anda' }}</p>
                </div>
            </div>

            <p v-if="sessionMessage" class="mb-4 text-sm font-medium text-text-secondary">{{ sessionMessage }}</p>

            <form v-if="mode === 'login'" class="space-y-4" @submit.prevent="handleSubmit">
                <!-- Username / NRP Input -->
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </span>
                    <input
                        id="username"
                        v-model="username"
                        type="text"
                        placeholder="NRP / Username"
                        autocomplete="username"
                        required
                        :class="fieldClasses"
                    />
                </div>

                <!-- Password Input -->
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </span>
                    <input
                        id="password"
                        v-model="password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Password"
                        autocomplete="current-password"
                        required
                        :class="fieldClasses"
                    />
                    <button type="button" @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-text-muted hover:text-text-primary focus:outline-none">
                        <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.822 7.822 3 3m-3-3-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>

                <!-- Remember Me & Forgot Password Links -->
                <div class="flex items-center justify-between text-sm mt-3 px-1">
                    <label class="flex items-center space-x-2.5 cursor-pointer select-none text-text-secondary">
                        <input type="checkbox" class="w-4 h-4 rounded border-border text-primary focus:ring-primary/20 focus:ring-offset-0" />
                        <span class="text-sm font-medium">Ingat saya</span>
                    </label>
                    <button type="button" class="text-primary hover:underline font-semibold text-sm" @click="openForgot">Lupa Password?</button>
                </div>

                <p v-if="auth.error" class="text-sm font-medium text-danger">{{ auth.error }}</p>

                <!-- Maroon Sign In Button -->
                <button type="submit" :disabled="auth.loading" class="w-full py-3.5 bg-maroon hover:bg-maroon-hover active:scale-[0.99] text-white font-bold rounded-xl transition-all shadow-md focus:outline-none focus:ring-2 focus:ring-maroon/40 flex items-center justify-center text-base" style="background-color: #7b1220;">
                    {{ auth.loading ? 'Signing in…' : 'Masuk' }}
                </button>
            </form>

            <div v-else>
                <div v-if="forgotDone" class="space-y-4 text-center">
                    <p class="rounded-xl bg-success-soft px-4 py-3 text-sm font-medium text-success">{{ forgotDone }}</p>
                    <button type="button" class="text-primary hover:underline font-semibold text-sm" @click="mode = 'login'">Kembali ke halaman masuk</button>
                </div>

                <form v-else class="space-y-3" @submit.prevent="handleForgot">
                    <input v-model="forgot.username" type="text" required placeholder="NRP / Username" autocomplete="username" :class="plainFieldClasses" />
                    <input v-model="forgot.full_name" type="text" required placeholder="Nama lengkap" autocomplete="name" :class="plainFieldClasses" />
                    <input v-model="forgot.position" type="text" required placeholder="Jabatan" :class="plainFieldClasses" />
                    <input v-model="forgot.phone" type="tel" required placeholder="Nomor telepon" autocomplete="tel" :class="plainFieldClasses" />

                    <p v-if="forgotError" class="text-sm font-medium text-danger">{{ forgotError }}</p>

                    <button type="submit" :disabled="forgotBusy" class="w-full py-3.5 text-white font-bold rounded-xl transition-all shadow-md focus:outline-none focus:ring-2 focus:ring-maroon/40 text-base disabled:opacity-60" style="background-color: #7b1220;">
                        {{ forgotBusy ? 'Mengirim…' : 'Kirim Permintaan' }}
                    </button>
                    <button type="button" class="block w-full text-center text-primary hover:underline font-semibold text-sm" @click="mode = 'login'">Kembali ke halaman masuk</button>
                </form>
            </div>

            <div class="mt-6 text-center text-xs text-text-muted border-t border-border/50 pt-4">
                <p class="text-[10px]">&copy; 2026 PT Putra Perkasa Abadi. All rights reserved.</p>
            </div>
        </UiCard>
    </div>
</template>
