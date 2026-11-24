<template>
  <div class="dnid-page">
    <header class="dnid-header">
      <img src="../../../assets/imagenes/logotiny.png" alt="UNA Puno" class="dnid-logo" />
      <div>
        <h1>Plataforma de Admisión</h1>
        <p>Universidad Nacional del Altiplano · Puno</p>
      </div>
    </header>

    <main class="dnid-main">
      <section class="dnid-card" aria-labelledby="dnid-title">
        <div class="dnid-card-header">
          <span class="dnid-badge">DNI digital</span>
          <h2 id="dnid-title">Verifica tu identidad</h2>
          <p>Escanea el código QR con la aplicación DNId desde tu celular.</p>
        </div>

        <div v-if="state === 'pending'" class="dnid-qr-wrap">
          <div class="dnid-qr-frame">
            <img :src="qrPngDataUrl" alt="Código QR para autorizar el ingreso con DNId" class="dnid-qr" />
            <div v-if="checking" class="dnid-scan-line" aria-hidden="true"></div>
          </div>
          <p class="dnid-expiry" :class="{ 'dnid-expiry-urgent': secondsLeft <= 30 }">
            El código vence en <strong>{{ formattedTime }}</strong>
          </p>
        </div>

        <div v-else class="dnid-result" :class="`dnid-result-${state}`" role="status" aria-live="polite">
          <div v-if="state === 'submitted' || state === 'renewing'" class="dnid-spinner" aria-hidden="true"></div>
          <svg v-else-if="state === 'expired'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
          </svg>
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 8v5M12 16h.01" />
          </svg>
          <h3>{{ resultTitle }}</h3>
          <p>{{ resultMessage }}</p>
        </div>

        <div v-if="state === 'pending'" class="dnid-instructions">
          <ol>
            <li>Abre la aplicación <strong>DNId</strong> en tu celular.</li>
            <li>Selecciona <strong>Escanear QR</strong> y apunta al código.</li>
            <li>Autoriza el acceso con tu DNI digital.</li>
          </ol>
        </div>

        <a v-if="state === 'pending'" :href="deepLink" class="dnid-mobile-link">
          Abrir DNId en este celular
        </a>

        <p v-if="errorMessage" class="dnid-error" role="alert">{{ errorMessage }}</p>

        <div class="dnid-actions">
          <button v-if="state === 'expired'" type="button" class="dnid-button dnid-button-primary" @click="renew">
            Generar nuevo QR
          </button>
          <button v-else-if="state === 'failed'" type="button" class="dnid-button dnid-button-primary" @click="renew">
            Intentar nuevamente
          </button>
          <a :href="route('login')" class="dnid-button dnid-button-secondary">
            Volver al inicio de sesión
          </a>
        </div>

        <p class="dnid-security">
          No compartas este QR. La autorización se completa dentro de la Plataforma de Admisión.
        </p>
      </section>
    </main>

    <footer class="dnid-footer">© 2026 UNA Puno · Todos los derechos reservados</footer>
  </div>
</template>

<script setup>
import axios from 'axios';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
  qrPngDataUrl: { type: String, required: true },
  deepLink: { type: String, required: true },
  expiresAtEpochMs: { type: Number, required: true },
});

const state = ref('pending');
const secondsLeft = ref(0);
const checking = ref(false);
const errorMessage = ref('');
const resultMessage = ref('');
const renewAtSeconds = 15;
const fallbackTtlMs = 60_000;
const maxTrustedTtlMs = 15 * 60_000;
const providerRemainingMs = props.expiresAtEpochMs - Date.now();
const effectiveExpiresAt = providerRemainingMs > 0 && providerRemainingMs <= maxTrustedTtlMs
  ? props.expiresAtEpochMs
  : Date.now() + fallbackTtlMs;
let countdownTimer = null;
let pollTimer = null;
let stopped = false;

const formattedTime = computed(() => {
  const minutes = Math.floor(secondsLeft.value / 60);
  const seconds = secondsLeft.value % 60;
  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
});

const resultTitle = computed(() => ({
  submitted: 'Identidad verificada',
  renewing: 'Renovando el código QR',
  expired: 'El código QR venció',
  failed: 'No se pudo completar la verificación',
}[state.value]));

const updateCountdown = () => {
  secondsLeft.value = Math.max(0, Math.ceil((effectiveExpiresAt - Date.now()) / 1000));

  if (state.value === 'pending' && secondsLeft.value <= renewAtSeconds) {
    renew();
  }
};

const scheduleNextPoll = (delay = 2000) => {
  clearTimeout(pollTimer);
  if (!stopped && state.value === 'pending') {
    pollTimer = setTimeout(checkStatus, delay);
  }
};

const checkStatus = async () => {
  if (stopped || state.value !== 'pending' || checking.value) return;

  checking.value = true;
  errorMessage.value = '';

  try {
    const { data } = await axios.post(route('dnid.status'));

    if (stopped || state.value !== 'pending') return;

    if (data.status === 'PENDING') {
      scheduleNextPoll();
      return;
    }

    if (data.status === 'EXPIRED') {
      renew();
      return;
    }

    if (data.status === 'SUBMITTED') {
      if (typeof data.callback_url !== 'string' || !data.callback_url.startsWith('/auth/dnid/callback')) {
        throw new Error('La respuesta de autorización no es válida.');
      }

      state.value = 'submitted';
      resultMessage.value = 'Estamos iniciando tu sesión en la Plataforma de Admisión…';
      stopPolling();
      window.location.assign(data.callback_url);
      return;
    }

    throw new Error(data.message || 'El servicio DNId devolvió una respuesta inválida.');
  } catch (error) {
    if (stopped || state.value !== 'pending') return;

    const status = error.response?.status;
    if (status === 410) {
      renew();
      return;
    }

    if (status === 400) {
      state.value = 'failed';
      resultMessage.value = error.response?.data?.message || 'La solicitud de verificación ya no es válida.';
      stopPolling();
      return;
    }

    errorMessage.value = 'No se pudo consultar DNId. Reintentando automáticamente…';
    scheduleNextPoll(4000);
  } finally {
    checking.value = false;
  }
};

const stopPolling = () => {
  clearTimeout(pollTimer);
  pollTimer = null;
};

const renew = () => {
  if (state.value !== 'pending') return;

  state.value = 'renewing';
  resultMessage.value = 'DNId emitió un nuevo código para que continúes con la autorización…';
  errorMessage.value = '';
  clearInterval(countdownTimer);
  stopPolling();
  window.location.assign(route('dnid.redirect'));
};

onMounted(() => {
  updateCountdown();

  if (state.value === 'pending') {
    countdownTimer = setInterval(updateCountdown, 1000);
    scheduleNextPoll(800);
  }
});

onBeforeUnmount(() => {
  stopped = true;
  clearInterval(countdownTimer);
  stopPolling();
});
</script>

<style scoped>
.dnid-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: #f4f7fb;
  color: #0f172a;
}

.dnid-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.9rem;
  padding: 1.15rem 1.5rem;
  color: #fff;
  background: linear-gradient(135deg, #1e3a5f, #2c5282);
  border-bottom: 3px solid #d4a017;
}

.dnid-logo {
  width: 48px;
  height: 48px;
  object-fit: contain;
}

.dnid-header h1 {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
}

.dnid-header p {
  margin: 0.2rem 0 0;
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.7rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.dnid-main {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
}

.dnid-card {
  width: 100%;
  max-width: 480px;
  padding: 2rem;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  box-shadow: 0 20px 55px rgba(30, 58, 95, 0.1);
}

.dnid-card-header {
  text-align: center;
}

.dnid-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.35rem 0.75rem;
  color: #1e40af;
  background: #eff6ff;
  border-radius: 999px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.dnid-card-header h2 {
  margin: 1rem 0 0.5rem;
  font-size: 1.65rem;
  line-height: 1.2;
}

.dnid-card-header p {
  margin: 0;
  color: #64748b;
  font-size: 0.9rem;
  line-height: 1.6;
}

.dnid-qr-wrap {
  text-align: center;
  margin-top: 1.5rem;
}

.dnid-qr-frame {
  position: relative;
  width: min(260px, 100%);
  margin: 0 auto;
  padding: 12px;
  overflow: hidden;
  background: #fff;
  border: 1px solid #cbd5e1;
  border-radius: 16px;
}

.dnid-qr {
  display: block;
  width: 100%;
  height: auto;
}

.dnid-scan-line {
  position: absolute;
  left: 12px;
  right: 12px;
  height: 2px;
  background: linear-gradient(90deg, transparent, #2563eb, transparent);
  box-shadow: 0 0 10px rgba(37, 99, 235, 0.6);
  animation: scan 2.5s ease-in-out infinite;
}

@keyframes scan {
  0%, 100% { top: 14px; opacity: 0.35; }
  50% { top: calc(100% - 16px); opacity: 1; }
}

.dnid-expiry {
  margin: 0.8rem 0 0;
  color: #64748b;
  font-size: 0.82rem;
}

.dnid-expiry strong {
  color: #334155;
  font-variant-numeric: tabular-nums;
}

.dnid-expiry-urgent,
.dnid-expiry-urgent strong {
  color: #dc2626;
}

.dnid-instructions {
  margin-top: 1.5rem;
  padding: 1rem 1.1rem;
  background: #f8fafc;
  border-radius: 12px;
}

.dnid-instructions ol {
  margin: 0;
  padding-left: 1.2rem;
  color: #475569;
  font-size: 0.82rem;
  line-height: 1.8;
}

.dnid-instructions li + li {
  margin-top: 0.25rem;
}

.dnid-instructions strong {
  color: #1e293b;
}

.dnid-mobile-link {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 44px;
  margin-top: 1rem;
  color: #1d4ed8;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 10px;
  font-size: 0.85rem;
  font-weight: 600;
  text-decoration: none;
}

.dnid-mobile-link:hover {
  background: #dbeafe;
}

.dnid-result {
  margin-top: 1.75rem;
  padding: 2rem 1rem;
  text-align: center;
  background: #f8fafc;
  border-radius: 14px;
}

.dnid-result > svg {
  width: 44px;
  height: 44px;
  margin: 0 auto 0.8rem;
}

.dnid-result h3 {
  margin: 0;
  font-size: 1.1rem;
}

.dnid-result p {
  margin: 0.5rem 0 0;
  color: #64748b;
  font-size: 0.85rem;
  line-height: 1.6;
}

.dnid-result-submitted {
  color: #1d4ed8;
  background: #eff6ff;
}

.dnid-result-expired {
  color: #b45309;
  background: #fffbeb;
}

.dnid-result-failed {
  color: #b91c1c;
  background: #fef2f2;
}

.dnid-spinner {
  width: 40px;
  height: 40px;
  margin: 0 auto 1rem;
  border: 3px solid #bfdbfe;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.dnid-error {
  margin: 1rem 0 0;
  padding: 0.75rem;
  color: #b91c1c;
  background: #fef2f2;
  border-radius: 8px;
  font-size: 0.8rem;
  text-align: center;
}

.dnid-actions {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  margin-top: 1.5rem;
}

.dnid-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 44px;
  padding: 0.7rem 1rem;
  border-radius: 10px;
  font-size: 0.85rem;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
}

.dnid-button-primary {
  color: #fff;
  background: #2563eb;
  border: 1px solid #2563eb;
}

.dnid-button-primary:hover {
  background: #1d4ed8;
}

.dnid-button-secondary {
  color: #475569;
  background: #fff;
  border: 1px solid #cbd5e1;
}

.dnid-button-secondary:hover {
  background: #f8fafc;
}

.dnid-security {
  margin: 1.25rem 0 0;
  color: #94a3b8;
  font-size: 0.7rem;
  line-height: 1.5;
  text-align: center;
}

.dnid-footer {
  padding: 1rem;
  color: #94a3b8;
  font-size: 0.68rem;
  text-align: center;
}

@media (max-width: 520px) {
  .dnid-header {
    justify-content: flex-start;
    padding: 0.9rem 1rem;
  }

  .dnid-logo {
    width: 40px;
    height: 40px;
  }

  .dnid-header h1 {
    font-size: 0.95rem;
  }

  .dnid-header p {
    font-size: 0.6rem;
  }

  .dnid-main {
    align-items: flex-start;
    padding: 1rem 0.75rem;
  }

  .dnid-card {
    padding: 1.35rem 1.1rem;
    border-radius: 16px;
  }
}

@media (prefers-color-scheme: dark) {
  .dnid-page { background: #0f172a; color: #f1f5f9; }
  .dnid-card { background: #1e293b; border-color: #334155; }
  .dnid-card-header p,
  .dnid-expiry,
  .dnid-result p,
  .dnid-security,
  .dnid-footer { color: #94a3b8; }
  .dnid-qr-frame { background: #fff; border-color: #475569; }
  .dnid-instructions { background: #0f172a; }
  .dnid-instructions ol { color: #cbd5e1; }
  .dnid-instructions strong { color: #f1f5f9; }
  .dnid-button-secondary { color: #cbd5e1; background: #1e293b; border-color: #475569; }
  .dnid-button-secondary:hover { background: #334155; }
}
</style>
