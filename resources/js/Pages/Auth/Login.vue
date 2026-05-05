<template>
  <div class="login-wrapper">
    <!-- Mobile Header -->
    <div class="mobile-header">
      <div class="header-inner">
        <img :src="logoUrl" alt="UNA Puno" class="mobile-logo"/>
        <h1 class="header-title">Sistema de Plataforma de Admisión</h1>
        <p class="header-subtitle">PUNO · PERÚ</p>
      </div>
      <div class="header-bar"></div>
    </div>

    <!-- Desktop Split -->
    <div class="split">
      <!-- Left Panel — Branding -->
      <div class="panel-left">
        <div class="panel-bg"></div>
        <div class="panel-content">
          <div class="logo-container" style="margin-bottom: 1.5rem; justify-content: center; display: flex;">
            <img :src="logoUrl" alt="UNA Puno" class="seal-logo"/>
          </div>
          <h2 class="panel-title">Universidad Nacional<br>del Altiplano</h2>
          <p class="panel-desc">Plataforma Digital de Admisión</p>
          <div class="divider-gold"></div>
          <div class="panel-features">
            <div class="pf-item">
              <svg class="pf-check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              <span>Proceso de admisión en línea</span>
            </div>
            <div class="pf-item">
              <svg class="pf-check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              <span>Seguridad y confidencialidad</span>
            </div>
            <div class="pf-item">
              <svg class="pf-check" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              <span>Resultados en tiempo real</span>
            </div>
          </div>
        </div>
        <div class="panel-footer">© 2026 UNA Puno · Todos los derechos reservados</div>
      </div>

      <!-- Right Panel — Form -->
      <div class="panel-right">
        <div class="form-wrapper">
          <div class="form-header">
            <h2 class="form-title">Iniciar sesión</h2>
            <p class="form-subtitle">Ingrese sus credenciales para acceder al sistema</p>
          </div>

          <div v-if="status" class="status-alert">
            <svg class="sa-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
            <span>{{ status }}</span>
          </div>

          <button type="button" @click="loginWithGoogle" class="btn-google" :disabled="processing">
            <svg viewBox="0 0 24 24" class="g-icon">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continuar con Google
          </button>

          <div class="divider"><span>o inicie sesión con correo</span></div>

          <form @submit.prevent="submit" class="login-form">
            <div class="field">
              <label for="email" class="field-label">Correo electrónico</label>
              <div class="input-wrap">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                <input id="email" type="email" v-model="form.email" class="input-field" required autofocus autocomplete="username" placeholder="correo@ejemplo.com"/>
              </div>
              <InputError class="field-error" :message="form.errors.email"/>
            </div>

            <div class="field">
              <label for="password" class="field-label">Contraseña</label>
              <div class="input-wrap">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                <input id="password" :type="showPassword ? 'text' : 'password'" v-model="form.password" class="input-field" required autocomplete="current-password" placeholder="Ingrese su contraseña"/>
                <button type="button" class="toggle-pw" @click="showPassword = !showPassword" tabindex="-1">
                  <svg v-if="!showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                </button>
              </div>
              <InputError class="field-error" :message="form.errors.password"/>
            </div>

            <div class="form-row">
              <label class="check-label">
                <input type="checkbox" v-model="form.remember" class="check-box"/>
                <span>Recordarme</span>
              </label>
              <Link v-if="canResetPassword" :href="route('password.request')" class="link-forgot">¿Olvidó su contraseña?</Link>
            </div>

            <PrimaryButton class="btn-login" :disabled="form.processing || processing">
              <span v-if="form.processing || processing" class="btn-loading">
                <span class="spinner"></span> Verificando...
              </span>
              <span v-else>Iniciar sesión</span>
            </PrimaryButton>
          </form>

          <div class="form-bottom">
            <span>¿No tiene una cuenta?</span>
            <Link :href="route('register')" class="link-register">Registrarse</Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import logoUrl from '../../../assets/imagenes/logotiny.png';

defineProps({ canResetPassword: Boolean, status: String });

const form = useForm({ email: '', password: '', remember: false });
const showPassword = ref(false);
const processing = ref(false);

const submit = () => {
  form.post(route('login'), {
    onFinish: () => { form.reset('password'); processing.value = false; },
    onStart: () => { processing.value = true; }
  });
};

const loginWithGoogle = () => {
  processing.value = true;
  window.location.href = route('google.redirect');
};
</script>

<style scoped>
/* ═══════════════════════════════════════════
   LOGIN — UNA Puno · Plataforma de Admisión
   ═══════════════════════════════════════════ */

.login-wrapper { min-height: 100vh; background: #f8fafc; }

/* ── Mobile Header ── */
.mobile-header { display: none; background: linear-gradient(160deg, #1e3a5f 0%, #2c5282 100%); padding: 2.25rem 1.5rem 0; text-align: center; overflow: hidden; }
.header-inner { display: flex; flex-direction: column; align-items: center; gap: .5rem; padding-bottom: 1.25rem; }
.mobile-logo { width: 64px; height: auto; }
.header-title { font-size: 1.15rem; font-weight: 700; color: #fff; line-height: 1.3; }
.header-subtitle { font-size: .7rem; color: rgba(255,255,255,.65); letter-spacing: 3px; text-transform: uppercase; }
.header-bar { height: 3px; background: #d4a017; margin-left: -1.5rem; margin-right: -1.5rem; }

/* ── Split Layout ── */
.split { display: flex; min-height: 100vh; }

/* ═══════════════════════════════════════════
   LEFT PANEL
   ═══════════════════════════════════════════ */
.panel-left {
  flex: 0 0 50%;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background: linear-gradient(160deg, #1e3a5f 0%, #2c5282 100%);

}

.panel-bg {
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 70% 50% at 20% 80%, rgba(37,99,235,.1) 0%, transparent 60%),
    radial-gradient(ellipse 50% 40% at 85% 15%, rgba(212,160,23,.08) 0%, transparent 50%);
}
.panel-bg::before {
  content: ''; position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,.02) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.02) 1px, transparent 1px);
  background-size: 52px 52px;
}

.panel-content { position: relative; z-index: 2; text-align: center; color: #fff; padding: 2.5rem; max-width: 420px; width: 100%; animation: fadeInLeft .6s ease-out; }
@keyframes fadeInLeft { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }

.logo-container { margin-bottom: 1.5rem; }
.seal-logo { width: 100px; height: auto; filter: drop-shadow(0 8px 20px rgba(0,0,0,.25)); }

.panel-title { font-size: 2.2rem; font-weight: 600; line-height: 1.2; margin-bottom: .5rem; letter-spacing: -.02em; }
.panel-desc { font-size: .95rem; color: rgba(255,255,255,.65); margin-bottom: 1.75rem; letter-spacing: .5px; }
.divider-gold { width: 50px; height: 2px; background: #d4a017; margin: 0 auto 1.75rem; border-radius: 2px; }

.panel-features { display: flex; flex-direction: column; gap: .875rem; align-items: flex-start; margin: 0 auto; width: fit-content; }
.pf-item { display: flex; align-items: center; gap: .75rem; font-size: .9rem; color: rgba(255,255,255,.8); }
.pf-check { width: 18px; height: 18px; color: #d4a017; flex-shrink: 0; }

.panel-footer { position: absolute; bottom: 1.5rem; left: 0; right: 0; text-align: center; font-size: .7rem; color: rgba(255,255,255,.25); z-index: 2; }

/* ═══════════════════════════════════════════
   RIGHT PANEL — FORM
   ═══════════════════════════════════════════ */
.panel-right {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2.5rem 2rem;
  background: #fff;
}

.form-wrapper { width: 100%; max-width: 400px; animation: fadeInRight .6s ease-out; }
@keyframes fadeInRight { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }

.form-header { margin-bottom: 2rem; text-align: center; }
.form-title { font-size: 1.75rem; font-weight: 700; color: #0f172a; letter-spacing: -.02em; }
.form-subtitle { font-size: .875rem; color: #64748b; margin-top: .5rem; }

/* Status */
.status-alert { background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 10px; padding: .75rem 1rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: .75rem; color: #1e40af; font-size: .8125rem; }
.sa-icon { width: 18px; height: 18px; flex-shrink: 0; }

/* Google */
.btn-google {
  width: 100%; height: 46px;
  border: 1px solid #e2e8f0; border-radius: 12px;
  background: #fff; display: flex; align-items: center; justify-content: center; gap: .75rem;
  font-size: .875rem; font-weight: 500; color: #334155;
  cursor: pointer; transition: all .2s ease;
}
.btn-google:hover:not(:disabled) { background: #f8fafc; border-color: #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,.05); transform: translateY(-1px); }
.btn-google:active:not(:disabled) { transform: scale(.98); }
.btn-google:disabled { opacity: .5; cursor: not-allowed; }
.g-icon { width: 20px; height: 20px; }

/* Divider */
.divider { display: flex; align-items: center; margin: 1.5rem 0; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }
.divider span { padding: 0 .875rem; font-size: .75rem; color: #94a3b8; white-space: nowrap; }

/* Form */
.login-form { display: flex; flex-direction: column; gap: 1.25rem; }
.field { display: flex; flex-direction: column; gap: .4rem; }
.field-label { font-size: .8125rem; font-weight: 600; color: #334155; }

/* ── Native Input with Icon ── */
.input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 12px;
  width: 17px;
  height: 17px;
  color: #94a3b8;
  pointer-events: none;
  z-index: 1;
}

.input-field {
  width: 100%;
  height: 46px;
  padding: 0 12px 0 40px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  background: #fff;
  font-size: .875rem;
  color: #0f172a;
  outline: none;
  transition: border-color .2s, box-shadow .2s;
  box-sizing: border-box;
  font-family: inherit;
}
.input-field::placeholder { color: #b0b8c4; }
.input-field:hover { border-color: #cbd5e1; }
.input-field:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.08); }

.toggle-pw {
  position: absolute;
  right: 4px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px;
  color: #94a3b8;
  display: flex;
  align-items: center;
  transition: color .15s;
}
.toggle-pw:hover { color: #64748b; }
.toggle-pw svg { width: 17px; height: 17px; }

.field-error { color: #dc2626; font-size: .75rem; margin-top: .125rem; }

/* Row */
.form-row { display: flex; justify-content: space-between; align-items: center; }
.check-label { display: flex; align-items: center; gap: .5rem; font-size: .8125rem; color: #475569; cursor: pointer; }
.check-box { width: 16px; height: 16px; accent-color: #2563eb; cursor: pointer; margin: 0; }
.link-forgot { font-size: .8125rem; color: #2563eb; text-decoration: none; font-weight: 500; transition: color .2s; }
.link-forgot:hover { color: #1d4ed8; text-decoration: underline; }

/* Login Button */
.btn-login {
  width: 100%; height: 48px;
  background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%) !important;
  border: none !important; border-radius: 12px !important;
  color: #fff !important; font-weight: 600; font-size: .9rem;
  cursor: pointer; transition: all .25s ease;
  margin-top: .25rem;
}
.btn-login:hover:not(:disabled) {
  background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%) !important;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(37,99,235,.3) !important;
}
.btn-login:active:not(:disabled) { transform: translateY(0); }
.btn-login:disabled { opacity: .6; cursor: not-allowed; transform: none; }

.btn-loading { display: flex; align-items: center; justify-content: center; gap: .5rem; }
.spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Bottom */
.form-bottom { text-align: center; margin-top: 1.75rem; font-size: .8125rem; color: #64748b; }
.link-register { color: #2563eb; text-decoration: none; font-weight: 600; margin-left: .25rem; transition: color .2s; }
.link-register:hover { color: #1d4ed8; text-decoration: underline; }

/* ════════════════════════
   TABLET
   ════════════════════════ */
@media (min-width: 768px) and (max-width: 1024px) {
  .panel-left { flex: 0 0 40%; }
  .panel-title { font-size: 1.8rem; }
  .seal-logo { width: 80px; }
  .form-wrapper { max-width: 360px; }
}

/* ════════════════════════
   MOBILE (< 768px)
   ════════════════════════ */
@media (max-width: 767px) {
  .mobile-header { display: block; }
  .split { flex-direction: column; min-height: auto; }
  .panel-left { display: none; }
  .panel-right { padding: 1.75rem 1.25rem 2rem; }
  .form-wrapper { max-width: 100%; animation: none; }
  .form-header { text-align: center; }
  .form-title { font-size: 1.35rem; }
  .form-subtitle { font-size: .8125rem; }
}

/* ════════════════════════
   DARK MODE
   ════════════════════════ */
@media (prefers-color-scheme: dark) {
  .login-wrapper { background: #020617; }
  .panel-left { background: linear-gradient(160deg, #0f172a 0%, #1e293b 100%); }
  .panel-right { background: #0f172a; }
  .form-title { color: #f1f5f9; }
  .form-subtitle { color: #94a3b8; }
  .status-alert { background: rgba(37,99,235,.12); border-left-color: #3b82f6; color: #93c5fd; }
  .btn-google { background: #1e293b; border-color: #334155; color: #e2e8f0; }
  .btn-google:hover:not(:disabled) { background: #334155; border-color: #475569; }
  .divider::before, .divider::after { background: #334155; }
  .divider span { color: #64748b; }
  .field-label { color: #cbd5e1; }
  .input-field { background: #1e293b; border-color: #334155; color: #f1f5f9; }
  .input-field::placeholder { color: #475569; }
  .input-field:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
  .input-icon { color: #64748b; }
  .toggle-pw { color: #64748b; }
  .toggle-pw:hover { color: #94a3b8; }
  .check-label { color: #cbd5e1; }
  .link-forgot { color: #60a5fa; }
  .link-forgot:hover { color: #93c5fd; }
  .btn-login { background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important; }
  .btn-login:hover:not(:disabled) { background: linear-gradient(135deg, #2563eb, #1e40af) !important; box-shadow: 0 8px 20px rgba(59,130,246,.35) !important; }
  .form-bottom { color: #64748b; }
  .link-register { color: #60a5fa; }
  .link-register:hover { color: #93c5fd; }
}
</style>
