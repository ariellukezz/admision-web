<!--
  ============================================================================
  Captura fotográfica — Foto de inscripción
  ----------------------------------------------------------------------------
  El encuadre manda: la guía de recorte es el contrato visual entre el operador
  y el archivo que se guarda. Todo lo demás (estado de la cámara, DNI, última
  captura) se subordina y se lee de un vistazo desde un metro de distancia.
  ============================================================================
-->
<template>
  <Head title="Foto de inscripción" />
  <AuthenticatedLayout pagina="Foto de inscripción">
    <div class="cap rev-scope">

      <!-- Visor ---------------------------------------------------------- -->
      <section class="cap-stage">
        <header class="cap-stage-head">
          <span class="cap-stage-title">Cámara</span>
          <span class="cap-state" :class="cargado ? 'is-live' : 'is-off'">
            <i class="cap-state-dot" />{ cargado ? 'Cámara activa' : 'Sin señal de cámara' }
          </span>
        </header>

        <div ref="captureContainer" class="cap-video">
          <video ref="videoElement" autoplay playsinline></video>

          <!-- Guía de recorte: lo que realmente se guarda -->
          <div
            v-if="cargado === true"
            ref="cropArea"
            class="cap-crop"
            :style="`top: ${cropAreaTop}px; left: ${cropAreaLeft}px; width: ${cropAreaWidth}px; height: ${cropAreaHeight}px;`"
          >
            <span class="cap-crop-corner tl" /><span class="cap-crop-corner tr" />
            <span class="cap-crop-corner bl" /><span class="cap-crop-corner br" />
            <span class="cap-crop-tag">Área que se guarda</span>
          </div>

          <div v-if="!cargado" class="cap-video-off">
            <span>Esperando permiso de la cámara…</span>
          </div>
        </div>

        <footer class="cap-stage-foot">
          <label class="cap-dni">
            <span class="cap-dni-label">DNI</span>
            <input
              v-model="dni"
              type="text"
              inputmode="numeric"
              maxlength="8"
              placeholder="00000000"
              :disabled="!cargado"
              autocomplete="off"
            />
          </label>
          <span class="cap-hint">Al completar los 8 dígitos se captura y guarda automáticamente.</span>
          <button type="button" class="cap-shoot" :disabled="!cargado" @click="capturePhoto">
            Capturar ahora
          </button>
        </footer>
      </section>

      <!-- Última captura --------------------------------------------------- -->
      <aside class="cap-last">
        <header class="cap-last-head">Última captura</header>
        <div class="cap-last-body">
          <img v-if="capturedPhoto" :src="capturedPhoto" alt="Fotografía capturada" />
          <div v-else class="cap-last-empty">
            <span>Sin capturas en esta sesión</span>
          </div>
        </div>
        <footer v-if="capturedPhoto" class="cap-last-foot">
          <span class="cap-last-ok">Guardada para el DNI {{ dni || '—' }}</span>
        </footer>
      </aside>
    </div>
  </AuthenticatedLayout>
</template>

  <script setup>
      import { Head } from '@inertiajs/vue3';
      import AuthenticatedLayout from '@/Layouts/LayoutDocente.vue'    
      import {ref, watch} from 'vue'
      import { ExclamationCircleOutlined, FormOutlined, DeleteOutlined, EyeOutlined } from '@ant-design/icons-vue';
  </script>
  <script>
    import axios from 'axios';
    export default {
      data() {
        return {
          dni:null,
          capturedPhoto: null,
          cropAreaTop: 0, // Posición vertical del área de recorte
          cropAreaLeft: 120, // Posición horizontal del área de recorte
          cropAreaWidth: 400, // Ancho del área de recorte
          cropAreaHeight: 480, // Alto del área de recorte
          cargado: false,
        };
      },
      watch: {
          dni(newValue, oldValue) {
              if(this.dni.length === 8){
                  this.capturePhoto()
              }
          }
      },
      mounted() {
        this.initializeCamera();
      },
      beforeUnmount() {
        this.stopCamera();
      },
      methods: {
        async initializeCamera() {
          try {
            const constraints = { video: true };
            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.$refs.videoElement.srcObject = stream;
            this.cargado = true;
          } catch (error) {
            console.error('Error al acceder a la cámara: ', error);
          }
        },
        stopCamera() {
          const stream = this.$refs.videoElement.srcObject;
          if (stream) {
            stream.getTracks().forEach(track => track.stop());
          }
        },
        capturePhoto() {
          const video = this.$refs.videoElement;
          const canvas = document.createElement('canvas');
          const context = canvas.getContext('2d');
          const cropArea = this.$refs.cropArea;
          const width = cropArea.offsetWidth;
          const height = cropArea.offsetHeight;
          const x = cropArea.offsetLeft - video.offsetLeft;
          const y = cropArea.offsetTop - video.offsetTop;
          canvas.width = width;
          canvas.height = height;
          context.drawImage(video, x, y, width, height, 0, 0, width, height);
    
          const capturedPhotoURL = canvas.toDataURL();
          this.capturedPhoto = capturedPhotoURL;
          this.saveCroppedPhotoBiometrico()
        },
  
          saveCroppedPhotoBiometrico() {
              if (this.capturedPhoto) {
              axios.post('guardar-foto-inscripcion', { dni:this.dni, photo: this.capturedPhoto })
                  .then(response => {
                  console.log('Foto recortada guardada correctamente');
                  // Realiza cualquier acción adicional después de guardar la foto recortada
                  })
                  .catch(error => {
                  console.error('Error al guardar la foto recortada: ', error);
                  });
              }
          }
  
      }
    };
    </script>

<style scoped>
.cap {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 280px;
  gap: var(--rev-s-6);
  align-items: start;
}

/* Visor --------------------------------------------------------------------- */
.cap-stage {
  background: var(--rev-surface);
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-lg);
  box-shadow: var(--rev-sh-xs);
  overflow: hidden;
}
.cap-stage-head {
  display: flex; align-items: center; justify-content: space-between; gap: var(--rev-s-5);
  padding: 10px var(--rev-s-6);
  border-bottom: 1px solid var(--rev-line);
}
.cap-stage-title {
  font-size: var(--rev-fs-md); font-weight: 650;
  letter-spacing: var(--rev-track-tight); color: var(--rev-ink);
}
.cap-state {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: var(--rev-fs-sm); font-weight: 620;
  padding: 2px 8px; border-radius: var(--rev-r-sm);
  border: 1px solid transparent;
}
.cap-state.is-live { background: var(--rev-success-bg); border-color: var(--rev-success-border); color: var(--rev-success-ink); }
.cap-state.is-off  { background: var(--rev-pending-bg); border-color: var(--rev-pending-border); color: var(--rev-pending-ink); }
.cap-state-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.cap-state.is-live .cap-state-dot { animation: rev-pulse-soft 1.9s var(--rev-ease) infinite; }

.cap-video {
  position: relative;
  background: var(--rev-n-950);
  min-height: 360px;
  display: flex; align-items: center; justify-content: center;
}
.cap-video video { display: block; max-width: 100%; }

.cap-crop {
  position: absolute;
  border: 1px dashed rgba(255,255,255,.55);
  box-shadow: 0 0 0 9999px rgba(10,18,32,.42);
  pointer-events: none;
}
.cap-crop-corner {
  position: absolute; width: 13px; height: 13px;
  border: 2px solid var(--rev-nav-accent);
}
.cap-crop-corner.tl { top: -1px; left: -1px; border-right: 0; border-bottom: 0; }
.cap-crop-corner.tr { top: -1px; right: -1px; border-left: 0; border-bottom: 0; }
.cap-crop-corner.bl { bottom: -1px; left: -1px; border-right: 0; border-top: 0; }
.cap-crop-corner.br { bottom: -1px; right: -1px; border-left: 0; border-top: 0; }
.cap-crop-tag {
  position: absolute; left: 0; bottom: -22px;
  font-size: var(--rev-fs-2xs); font-weight: 680;
  letter-spacing: var(--rev-track-caps); text-transform: uppercase;
  color: rgba(255,255,255,.72);
}

.cap-video-off {
  position: absolute; inset: 0;
  display: grid; place-items: center;
  font-size: var(--rev-fs-md); color: rgba(255,255,255,.55);
}

.cap-stage-foot {
  display: flex; align-items: center; gap: var(--rev-s-6);
  flex-wrap: wrap;
  padding: 11px var(--rev-s-6);
  border-top: 1px solid var(--rev-line);
  background: var(--rev-surface-2);
}
.cap-dni { display: flex; align-items: center; gap: 0; flex: none; }
.cap-dni-label {
  font-size: var(--rev-fs-2xs); font-weight: 680;
  letter-spacing: var(--rev-track-caps); text-transform: uppercase;
  color: var(--rev-ink-4);
  padding: 0 10px; height: 36px; display: grid; place-items: center;
  background: var(--rev-surface);
  border: 1px solid var(--rev-line-strong); border-right: 0;
  border-radius: var(--rev-r-md) 0 0 var(--rev-r-md);
}
.cap-dni input {
  width: 150px; height: 36px; padding: 0 12px;
  font-family: var(--rev-mono); font-size: var(--rev-fs-xl); font-weight: 650;
  letter-spacing: .1em; font-variant-numeric: tabular-nums;
  color: var(--rev-ink); background: var(--rev-surface);
  border: 1px solid var(--rev-line-strong);
  border-radius: 0 var(--rev-r-md) var(--rev-r-md) 0;
  transition: border-color var(--rev-t-fast) var(--rev-ease), box-shadow var(--rev-t-fast) var(--rev-ease);
}
.cap-dni input:focus { outline: none; border-color: var(--rev-primary-500); box-shadow: var(--rev-ring); }
.cap-dni input:disabled { background: var(--rev-n-50); color: var(--rev-ink-4); }
.cap-dni input::placeholder { color: var(--rev-n-300); letter-spacing: .1em; }

.cap-hint { font-size: var(--rev-fs-sm); color: var(--rev-ink-4); flex: 1 1 auto; min-width: 140px; }

.cap-shoot {
  height: 36px; padding: 0 18px; flex: none;
  border: 1px solid var(--rev-primary-600); border-radius: var(--rev-r-md);
  background: var(--rev-primary-600); color: #fff;
  font-family: var(--rev-font); font-size: var(--rev-fs-md); font-weight: 600;
  cursor: pointer;
  transition: background var(--rev-t-fast) var(--rev-ease), border-color var(--rev-t-fast) var(--rev-ease);
}
.cap-shoot:hover:not(:disabled) { background: var(--rev-primary-700); border-color: var(--rev-primary-700); }
.cap-shoot:active:not(:disabled) { background: var(--rev-primary-800); }
.cap-shoot:focus-visible { outline: none; box-shadow: var(--rev-ring); }
.cap-shoot:disabled {
  background: var(--rev-n-100); border-color: var(--rev-line);
  color: var(--rev-ink-4); cursor: not-allowed;
}

/* Última captura ------------------------------------------------------------ */
.cap-last {
  background: var(--rev-surface);
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-lg);
  box-shadow: var(--rev-sh-xs);
  overflow: hidden;
  position: sticky; top: var(--rev-s-6);
}
.cap-last-head {
  padding: 10px var(--rev-s-6);
  border-bottom: 1px solid var(--rev-line);
  font-size: var(--rev-fs-2xs); font-weight: 680;
  letter-spacing: var(--rev-track-caps); text-transform: uppercase;
  color: var(--rev-ink-4);
}
.cap-last-body { padding: var(--rev-s-6); }
.cap-last-body img {
  width: 100%; aspect-ratio: 3 / 4; object-fit: cover;
  border: 1px solid var(--rev-line); border-radius: var(--rev-r-md);
  background: var(--rev-n-100);
  animation: rev-rise var(--rev-t-slow) var(--rev-ease-out) both;
}
.cap-last-empty {
  display: grid; place-items: center;
  aspect-ratio: 3 / 4;
  border: 1px dashed var(--rev-line-strong); border-radius: var(--rev-r-md);
  font-size: var(--rev-fs-sm); color: var(--rev-ink-4); text-align: center; padding: var(--rev-s-5);
}
.cap-last-foot {
  padding: 9px var(--rev-s-6);
  border-top: 1px solid var(--rev-line);
  background: var(--rev-success-bg);
}
.cap-last-ok { font-size: var(--rev-fs-sm); font-weight: 600; color: var(--rev-success-ink); }

@media (max-width: 1080px) {
  .cap { grid-template-columns: 1fr; }
  .cap-last { position: static; }
  .cap-last-body img, .cap-last-empty { max-width: 240px; }
}
</style>
