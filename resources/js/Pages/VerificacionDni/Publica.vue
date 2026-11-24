<template>
  <div class="dnid-public-page">
    <Head title="Verificar DNI digital" />

    <header class="dnid-public-header">
      <img src="../../../assets/imagenes/logotiny.png" alt="UNA Puno" class="dnid-public-logo" />
      <div>
        <h1>Plataforma de Admisión</h1>
        <p>Universidad Nacional del Altiplano · Puno</p>
      </div>
    </header>

    <main class="dnid-public-main">
      <DnidIdentityVerifier v-bind="$props" />
    </main>

    <footer class="dnid-public-footer">© 2026 UNA Puno · Todos los derechos reservados</footer>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DnidIdentityVerifier from '@/Components/DnidIdentityVerifier.vue';

defineProps({
  area: String,
  qrPngDataUrl: String,
  deepLink: String,
  expiresAtEpochMs: Number,
  initialError: String,
  statusUrl: String,
  restartUrl: String,
  backUrl: String,
  backLabel: String,
});
</script>

<style scoped>
.dnid-public-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: #f4f7fb;
}

.dnid-public-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.9rem;
  padding: 1.15rem 1.5rem;
  color: #fff;
  background: linear-gradient(135deg, #1e3a5f, #2c5282);
  border-bottom: 3px solid #d4a017;
}

.dnid-public-logo {
  width: 48px;
  height: 48px;
  object-fit: contain;
}

.dnid-public-header h1 {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
}

.dnid-public-header p {
  margin: 0.2rem 0 0;
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.7rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.dnid-public-main {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
}

.dnid-public-footer {
  padding: 1rem;
  color: #94a3b8;
  font-size: 0.68rem;
  text-align: center;
}

@media (max-width: 520px) {
  .dnid-public-header {
    justify-content: flex-start;
    padding: 0.9rem 1rem;
  }

  .dnid-public-logo {
    width: 40px;
    height: 40px;
  }

  .dnid-public-header h1 {
    font-size: 0.95rem;
  }

  .dnid-public-header p {
    font-size: 0.6rem;
  }

  .dnid-public-main {
    align-items: flex-start;
    padding: 1rem 0.75rem;
  }
}

@media (prefers-color-scheme: dark) {
  .dnid-public-page { background: #0f172a; }
  .dnid-public-footer { color: #94a3b8; }
}
</style>
