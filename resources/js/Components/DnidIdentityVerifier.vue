<template>
  <section class="dnid-verifier" :class="{ 'dnid-verifier-admin': area === 'admin' }">
    <header class="dnid-verifier-header">
      <span class="dnid-badge">DNI digital</span>
      <h1>Verifica tu identidad</h1>
      <p>Escanea el código QR con la aplicación DNId desde tu celular.</p>
      <span class="dnid-no-login">Solo verificación · no inicia sesión</span>
    </header>

    <div v-if="state === 'pending'" class="dnid-qr-section">
      <div class="dnid-qr-frame">
        <img :src="qrPngDataUrl" alt="Código QR para verificar la identidad con DNId" class="dnid-qr" />
        <div v-if="checking" class="dnid-scan-line" aria-hidden="true"></div>
      </div>
      <p class="dnid-expiry" :class="{ 'dnid-expiry-urgent': secondsLeft <= 30 }">
        El código vence en <strong>{{ formattedTime }}</strong>
      </p>
    </div>

    <div v-else-if="state === 'completed'" class="dnid-result dnid-result-success" role="status" aria-live="polite">
      <div class="dnid-success-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <circle cx="12" cy="12" r="9" />
          <path d="m8 12 2.5 2.5L16 9" />
        </svg>
      </div>
      <h2>Credencial verificada</h2>
      <p>DNId confirmó la autenticidad de la credencial compartida.</p>

      <dl class="dnid-identity-data">
        <div v-if="identity.dni">
          <dt>DNI</dt>
          <dd>{{ identity.dni }}</dd>
        </div>
        <div v-if="identity.nombres">
          <dt>Nombres</dt>
          <dd>{{ identity.nombres }}</dd>
        </div>
        <div v-if="identity.apellidos">
          <dt>Apellidos</dt>
          <dd>{{ identity.apellidos }}</dd>
        </div>
        <div v-if="identity.nombre_completo">
          <dt>Nombre completo</dt>
          <dd>{{ identity.nombre_completo }}</dd>
        </div>
      </dl>

      <p v-if="verifiedAt" class="dnid-verified-at">Verificado el {{ formattedVerifiedAt }}</p>
    </div>

    <div v-else class="dnid-result" :class="state === 'expired' ? 'dnid-result-expired' : 'dnid-result-failed'" role="status" aria-live="polite">
      <div v-if="state === 'renewing'" class="dnid-spinner" aria-hidden="true"></div>
      <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <circle cx="12" cy="12" r="9" />
        <path d="M12 7v5l3 2" />
      </svg>
      <h2>{{ resultTitle }}</h2>
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
      <button
        v-if="state === 'completed' || state === 'expired' || state === 'failed'"
        type="button"
        class="dnid-button dnid-button-primary"
        :disabled="state === 'renewing'"
        @click="renew"
      >
        {{ state === 'completed' ? 'Verificar otro DNI' : 'Intentar nuevamente' }}
      </button>
      <a :href="backUrl" class="dnid-button dnid-button-secondary">{{ backLabel }}</a>
    </div>

    <p class="dnid-security">
      No se crea una cuenta ni se inicia sesión. Los datos se muestran únicamente en esta pantalla.
    </p>
  </section>
</template>

<script setup>
import axios from 'axios';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
  area: { type: String, required: true },
  qrPngDataUrl: { type: String, default: null },
  deepLink: { type: String, default: null },
  expiresAtEpochMs: { type: Number, default: null },
  initialError: { type: String, default: '' },
  statusUrl: { type: String, required: true },
  restartUrl: { type: String, required: true },
  backUrl: { type: String, required: true },
  backLabel: { type: String, required: true },
});

const state = ref(props.initialError ? 'failed' : 'pending');
const secondsLeft = ref(0);
const checking = ref(false);
const errorMessage = ref('');
const resultMessage = ref(props.initialError);
const identity = ref(null);
const verifiedAt = ref('');
const renewAtSeconds = 15;
const fallbackTtlMs = 60_000;
const maxTrustedTtlMs = 15 * 60_000;
const providerRemainingMs = props.expiresAtEpochMs ? props.expiresAtEpochMs - Date.now() : 0;
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

const formattedVerifiedAt = computed(() => {
  if (!verifiedAt.value) return '';
  const date = new Date(verifiedAt.value);
  return Number.isNaN(date.getTime()) ? verifiedAt.value : date.toLocaleString('es-PE');
});

const resultTitle = computed(() => ({
  renewing: 'Renovando el código QR',
  expired: 'El código QR venció',
  failed: 'No se pudo completar la verificación',
}[state.value] || 'No se pudo completar la verificación'));

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
  if (stopped || state.value !== 'pending' || checking.value || !props.qrPngDataUrl) return;

  checking.value = true;
  errorMessage.value = '';

  try {
    const { data } = await axios.post(props.statusUrl);

    if (stopped || state.value !== 'pending') return;

    if (data.status === 'PENDING') {
      scheduleNextPoll();
      return;
    }

    if (data.status === 'EXPIRED') {
      state.value = 'expired';
      resultMessage.value = data.message || 'El código QR venció. Solicite uno nuevo.';
      stopPolling();
      return;
    }

    if (data.status === 'COMPLETED' && data.identity && typeof data.identity.dni === 'string') {
      identity.value = data.identity;
      verifiedAt.value = typeof data.verified_at === 'string' ? data.verified_at : '';
      state.value = 'completed';
      stopPolling();
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

    if (status === 400 || status === 422) {
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
  if (state.value === 'renewing') return;

  state.value = 'renewing';
  resultMessage.value = 'Estamos generando un nuevo código QR…';
  errorMessage.value = '';
  clearInterval(countdownTimer);
  stopPolling();
  window.location.assign(props.restartUrl);
};

onMounted(() => {
  if (state.value !== 'pending' || !props.qrPngDataUrl) return;

  updateCountdown();
  countdownTimer = setInterval(updateCountdown, 1000);
  scheduleNextPoll(800);
});

onBeforeUnmount(() => {
  stopped = true;
  clearInterval(countdownTimer);
  stopPolling();
});
</script>

<style scoped>
.dnid-verifier {
  width: 100%;
  max-width: 520px;
  margin: 0 auto;
  padding: 2rem;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  box-shadow: 0 20px 55px rgba(30, 58, 95, 0.1);
  color: #0f172a;
}

.dnid-verifier-admin {
  max-width: 680px;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
}

.dnid-verifier-header {
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

.dnid-verifier-header h1 {
  margin: 1rem 0 0.5rem;
  font-size: 1.65rem;
  line-height: 1.2;
}

.dnid-verifier-header p {
  margin: 0;
  color: #64748b;
  font-size: 0.9rem;
  line-height: 1.6;
}

.dnid-no-login {
  display: inline-block;
  margin-top: 0.75rem;
  color: #475569;
  font-size: 0.75rem;
  font-weight: 600;
}

.dnid-qr-section {
  margin-top: 1.5rem;
  text-align: center;
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
  padding: 1.75rem 1rem;
  text-align: center;
  background: #f8fafc;
  border-radius: 14px;
}

.dnid-result > svg,
.dnid-success-icon svg {
  width: 44px;
  height: 44px;
  margin: 0 auto 0.8rem;
}

.dnid-result h2 {
  margin: 0;
  font-size: 1.1rem;
}

.dnid-result p {
  margin: 0.5rem 0 0;
  color: #64748b;
  font-size: 0.85rem;
  line-height: 1.6;
}

.dnid-result-success {
  color: #166534;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
}

.dnid-result-expired {
  color: #b45309;
  background: #fffbeb;
}

.dnid-result-failed {
  color: #b91c1c;
  background: #fef2f2;
}

.dnid-identity-data {
  display: grid;
  gap: 0.75rem;
  margin: 1.25rem 0 0;
  text-align: left;
}

.dnid-identity-data > div {
  display: grid;
  grid-template-columns: 130px minmax(0, 1fr);
  gap: 0.75rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.75);
  border: 1px solid #dcfce7;
  border-radius: 10px;
}

.dnid-identity-data dt {
  color: #64748b;
  font-size: 0.78rem;
  font-weight: 600;
}

.dnid-identity-data dd {
  margin: 0;
  color: #0f172a;
  font-size: 0.88rem;
  font-weight: 600;
  overflow-wrap: anywhere;
}

.dnid-verified-at {
  font-variant-numeric: tabular-nums;
}

.dnid-spinner {
  width: 40px;
  height: 40px;
  margin: 0 auto 0.8rem;
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

.dnid-button:disabled {
  opacity: 0.6;
  cursor: wait;
}

.dnid-button-primary {
  color: #fff;
  background: #2563eb;
  border: 1px solid #2563eb;
}

.dnid-button-primary:hover:not(:disabled) {
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

@media (max-width: 520px) {
  .dnid-verifier {
    padding: 1.35rem 1.1rem;
    border-radius: 16px;
  }

  .dnid-identity-data > div {
    grid-template-columns: 1fr;
    gap: 0.25rem;
  }
}

@media (prefers-color-scheme: dark) {
  .dnid-verifier { background: #1e293b; border-color: #334155; color: #f1f5f9; }
  .dnid-verifier-header p,
  .dnid-expiry,
  .dnid-result p,
  .dnid-security { color: #94a3b8; }
  .dnid-no-login { color: #cbd5e1; }
  .dnid-qr-frame { background: #fff; border-color: #475569; }
  .dnid-instructions { background: #0f172a; }
  .dnid-instructions ol { color: #cbd5e1; }
  .dnid-instructions strong { color: #f1f5f9; }
  .dnid-button-secondary { color: #cbd5e1; background: #1e293b; border-color: #475569; }
  .dnid-button-secondary:hover { background: #334155; }
  .dnid-result { background: #0f172a; }
  .dnid-result-success { background: #052e16; border-color: #166534; }
  .dnid-identity-data > div { background: rgba(15, 23, 42, 0.65); border-color: #166534; }
  .dnid-identity-data dd { color: #f1f5f9; }
}
</style>
