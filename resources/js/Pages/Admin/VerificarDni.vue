<template>
  <div>
    <Head title="Verificar DNI digital" />

    <AuthenticatedLayout>
      <div class="admin-dnid-page">
        <header class="admin-dnid-heading">
          <div>
            <span class="admin-dnid-kicker">Herramienta de identidad</span>
            <h1>Verificador de DNI digital</h1>
            <p>Genera un código QR y muestra los datos compartidos por la credencial DNId.</p>
          </div>
          <span class="admin-dnid-badge">Sin inicio de sesión</span>
        </header>

        <DnidIdentityVerifier v-bind="$props" />
      </div>
    </AuthenticatedLayout>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
.admin-dnid-page {
  padding: 1.5rem;
}

.admin-dnid-heading {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  max-width: 680px;
  margin: 0 auto 1.25rem;
}

.admin-dnid-kicker {
  color: #2563eb;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.admin-dnid-heading h1 {
  margin: 0.35rem 0 0;
  color: #0f172a;
  font-size: 1.6rem;
}

.admin-dnid-heading p {
  margin: 0.45rem 0 0;
  color: #64748b;
  font-size: 0.88rem;
  line-height: 1.5;
}

.admin-dnid-badge {
  flex-shrink: 0;
  padding: 0.4rem 0.7rem;
  color: #166534;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 600;
}

@media (max-width: 640px) {
  .admin-dnid-page { padding: 1rem; }
  .admin-dnid-heading { flex-direction: column; }
  .admin-dnid-heading h1 { font-size: 1.35rem; }
}
</style>
