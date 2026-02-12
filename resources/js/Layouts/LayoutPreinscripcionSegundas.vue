<template>
    <a-layout style="min-height: 100vh; background: #f8fafc;">
      <a-layout>
        <!-- Header Institucional -->
        <div class="institutional-header">
          <!-- Barra Superior -->
          <div class="top-bar">
            <div class="container mx-auto px-4">
              <div class="flex justify-between items-center py-2">
                <!-- Logo y Nombre -->
                <div class="flex items-center space-x-4">
                  <div class="university-logo">
                    <img src="./../../assets/imagenes/logotiny.png" alt="UNA Puno" class="logo-img" />
                  </div>
                  <div class="university-info">
                    <div class="university-name">Universidad Nacional del Altiplano</div>
                    <div class="campus-name">Campus Universitario - Puno</div>
                  </div>
                </div>

                <!-- Escudo de Calidad -->
                <div class="quality-badge">
                  <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Educación de Calidad</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Banner Principal -->
          <div class="main-banner">
            <div class="banner-overlay"></div>
            <div class="container mx-auto px-4 relative z-10">
              <div class="py-1 md:py-1">
                <!-- Título del Programa -->
                <div class="program-title mb-2">
                  <h1 class="main-title">{{ props.nombre }}</h1>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- Contenido Principal -->
        <a-layout-content style="min-height: calc(100vh - 200px); padding: 0;">
          <div class="content-container">
            <div class="content-wrapper">
              <slot />
            </div>
          </div>
        </a-layout-content>

        <!-- Footer Institucional -->
        <div class="institutional-footer">
          <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
              <div class="footer-info mb-4 md:mb-0">
                <div class="text-sm text-gray-600">
                  © {{ new Date().getFullYear() }} Universidad Nacional del Altiplano - Puno. Todos los derechos reservados.
                </div>
                <div class="text-xs text-gray-500 mt-1">
                  Sistema de Preinscripción en Línea - Dirección de Admisión
                </div>
              </div>
              <div class="footer-contact">
                <div class="flex items-center space-x-4">
                  <div class="contact-item">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span class="text-sm text-gray-600">(051) 367373</span>
                  </div>
                  <div class="contact-item">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm text-gray-600">admision@unap.edu.pe</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </a-layout>
    </a-layout>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps(['nombre']);
</script>

<style scoped>
:root {
  --primary-color: #1e40af;
  --secondary-color: #059669;
  --accent-color: #dc2626;
  --light-bg: #f8fafc;
  --dark-text: #1f2937;
  --medium-text: #4b5563;
  --light-text: #6b7280;
}

.institutional-header {
  background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%);
  color: white;
  position: relative;
}

.top-bar {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.university-logo {
  width: 48px;
  height: 48px;
  background: white;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.logo-img {
  width: 32px;
  height: auto;
}

.university-info {
  color: white;
}

.university-name {
  font-size: 0.95rem;
  font-weight: 600;
  line-height: 1.2;
}

.campus-name {
  font-size: 0.8rem;
  opacity: 0.9;
  line-height: 1.2;
}

.quality-badge {
  background: rgba(255, 255, 255, 0.15);
  padding: 6px 12px;
  border-radius: 20px;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.main-banner {
  position: relative;
  padding: 2rem 0;
  background: linear-gradient(135deg, rgba(30, 64, 175, 0.7) 0%, rgba(29, 78, 216, 0.9) 100%);
}

.banner-overlay {
  position: absolute;
  inset: 0;
  background: url('./../../assets/imagenes/f-head-sim.jpg') center/cover no-repeat;
  opacity: 0.1;
}

.main-title {
  font-size: 2.2rem;
  font-weight: 700;
  color: white;
  text-align: center;
}

.content-container {
  background: #f8fafc;
  min-height: calc(100vh - 280px);
}

.content-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem;
}

.institutional-footer {
  background: white;
  border-top: 1px solid #e5e7eb;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Animación */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.main-banner {
  animation: fadeIn 0.6s ease-out;
}

/* Accesibilidad */
@media (prefers-reduced-motion: reduce) {
  .main-banner {
    animation: none;
  }
}

/* Impresión */
@media print {
  .institutional-header,
  .institutional-footer {
    background: white !important;
    color: black !important;
  }

  .main-title {
    color: black !important;
    text-shadow: none !important;
  }

  .banner-overlay {
    display: none;
  }
}
</style>
