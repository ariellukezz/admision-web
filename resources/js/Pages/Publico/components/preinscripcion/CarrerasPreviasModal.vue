<template>
  <a-modal
    :visible="visible"
    @update:visible="$emit('update:visible', $event)"
    centered
    :keyboard="false"
    :footer="false"
    :closable="false"
    :maskClosable="false"
  >
    <!-- Loading spinner -->
    <div v-if="loading === true" class="flex justify-center" style="min-height:300px; align-items:center;">
      <div>
        <div class="flex justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rotating-svg">
            <line x1="12" y1="2" x2="12" y2="6"></line>
            <line x1="12" y1="18" x2="12" y2="22"></line>
            <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
            <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
            <line x1="2" y1="12" x2="6" y2="12"></line>
            <line x1="18" y1="12" x2="22" y2="12"></line>
            <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
            <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
          </svg>
        </div>
        <div class="flex justify-center mt-3">
          <div style="text-align: center;">
            <div><span style="font-size:1.3rem;">Estamos revisando su información</span></div>
            <div><span style="font-size:1rem;">Tomará poco tiempo</span></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content when loading is done -->
    <div v-if="loading === false" class="flex justify-center">
      <!-- Sancionado view -->
      <div v-if="modalSancionado === true" class="aparecer-div-mostrar">
        <div class="flex justify-center mt-6">
          <img src="../../../../../assets/imagenes/alert.png" width="125" />
        </div>
        <div class="mt-4">
          <h2 class="text-center" style="font-size:1.4rem;">¡Importante!</h2>
          <p class="text-center mx-4" style="font-size:1.1rem;">El participante no reúne las condiciones para participar en este proceso.</p>
        </div>
        <div class="flex justify-center">
          <a-button @click="$emit('cancel')" style="background:teal; color:white; font-weight:bold; height:40px; width:110px; border-radius:8px; border:none;">
            Aceptar
          </a-button>
        </div>
      </div>

      <div v-else>
        <!-- Carreras previas (>=2) -->
        <div v-if="anteriores.length >= 2" style="width: 100%; max-width: 1000px; margin-top:20px;">
          <div class="mb-4">
            <div class="flex justify-center"><span>Estimado postulante se ha detectado Que Ud. Tiene ingresos previos</span></div>
            <div class="mt-3 flex justify-left"><span>Se ha verificado que usted registra dos o más ingresos previos a nuestra universidad. De acuerdo con las normativas vigentes, no es posible continuar con una nueva postulación.</span></div>
          </div>

          <a-row style="display:flex; justify-content:center;" class="pb-0">
            <a-col :span="24">
              <a-row :gutter="16" style="display:flex; justify-content:center;">
                <a-col v-for="item in anteriores" :key="item" :xs="24" :sm="24" :md="24" :lg="24" style="margin-bottom: 10px;">
                  <div
                    @click="toggleSelection(item)"
                    :class="{ 'selected': item.selected }"
                    style="height:80px; border-radius:5px; cursor:pointer; border:solid 1px #d9d9d9; align-items: center;"
                    class="flex p-4"
                  >
                    <div style="display:flex; justify-content: space-between; width: 100%; align-items: center;">
                      <div style="width: calc(100% - 50px);">
                        <div><span style="font-size:.8rem; text-transform: capitalize;">{{ item.career }}</span></div>
                        <div class="flex justify-left"><span style="font-weight:bold; font-size:.8rem">cod: {{ item.code }}</span></div>
                      </div>
                      <div class="flex justify-center" style="width: 50px; height: 50px; align-items: center;">
                        <img src="../../../../../assets/imagenes/logotiny.png" width="45px" />
                      </div>
                    </div>
                  </div>
                </a-col>
              </a-row>
            </a-col>
          </a-row>

          <div class="my-2 mb-4">
            <a-alert message="Si no reconoce haber ingresado a ninguna de esas carreras presione en CANCELAR y aproximese a la DIRECCIÓN DE ADMISIÓN de la UNA PUNO" type="warning" show-icon />
          </div>

          <div class="flex justify-center" v-if="confirmacion === true">
            <div>
              <div class="mt-4"><span>Se ha registrado su información</span></div>
              <div class="flex justify-center mt-6">
                <a-button style="background: teal; border:none; color:white; width:120px;" @click="$emit('close')">Continuar</a-button>
              </div>
            </div>
          </div>

          <div>
            <div class="flex justify-end mt-6 mb-3">
              <a-button @click="$emit('cancel')" class="mr-2" style="color: teal; border: 1px solid teal; border-radius:5px;">Cancelar</a-button>
              <div v-if="selectedItems">
                <a-button v-if="anteriores.length >= 2" disabled style="border: 1px solid gray; border-radius:5px;">Continuar</a-button>
              </div>
            </div>
          </div>
        </div>

        <!-- Verificación finalizada (no CEPRE, no anteriores) -->
        <div v-else>
          <div v-if="id_modalidad_proceso !== 2 && anteriores.length === 0" style="width: 100%; max-width: 1000px; margin-top:20px;">
            <div class="flex justify-center">
              <div>
                <div class="mt-0 mb-3 flex justify-center" style="text-align:center;">
                  <div><span style="font-size:1.4rem;">VERIFICACIÓN FINALIZADA</span></div>
                </div>
                <div class="mt-3 mb-3 flex justify-center" style="text-align:justify;">
                  <div><span style="font-size:1rem;">Hemos revisado tu información y cumples con los requisitos para postular. Para continuar con el proceso de postulación, sigue estos pasos:</span></div>
                </div>
                <div>
                  <div>1. Presiona en "CONTINUAR".</div>
                  <div>2. Ingresa el código secreto proporcionado.</div>
                  <div>3. Presiona en "Iniciar Postulación".</div>
                </div>
                <div class="mt-4 mb-4">
                  <a-alert message="!Importante! los datos registrados anteriormente se cargarán automáticamente" type="info" show-icon />
                </div>
                <div class="flex justify-center mt-4">
                  <a-button type="" style="border:none; background: teal; color:white; width:120px; height:42px;" @click="$emit('close')">Continuar</a-button>
                </div>
              </div>
            </div>
          </div>

          <!-- CEPRE verification -->
          <div v-if="id_modalidad_proceso === 2 && datacepre && datacepre.nro_documento" style="width: 100%; max-width: 1000px; margin-top:20px;">
            <div class="flex justify-center">
              <div>
                <div class="mt-0 mb-3 flex justify-center" style="text-align:center;">
                  <div><span style="font-size:1.4rem;">VERIFICACIÓN FINALIZADA</span></div>
                </div>
                <div class="mt-3 mb-3 flex justify-center" style="text-align:justify;">
                  <div><span style="font-size:1rem;">Hemos revisado tu información y cumples con los requisitos para postular. Para continuar con el proceso de postulación, sigue estos pasos:</span></div>
                </div>
                <div>
                  <div>1. Presiona en "CONTINUAR".</div>
                  <div>2. Ingresa el código secreto proporcionado.</div>
                  <div>3. Presiona en "Iniciar Postulación".</div>
                </div>
                <div class="mt-4 mb-4">
                  <a-alert message="!Importante! los datos registrados anteriormente se cargarán automáticamente" type="info" show-icon />
                </div>
                <div class="flex justify-center mt-4">
                  <a-button type="primary" style="border:none; background: teal; color:white; width:120px; height:42px;" @click="$emit('close')">Continuar</a-button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </a-modal>
</template>

<script setup>
const props = defineProps({
  visible: Boolean,
  loading: Boolean,
  modalSancionado: Boolean,
  anteriores: Array,
  datacepre: [Object, null],
  selectedItems: [Array, Object],
  confirmacion: [Boolean, null],
  id_modalidad_proceso: Number,
  toggleSelection: Function,
})

defineEmits(['close', 'cancel', 'update:visible'])
</script>

<style scoped>
.selected { background-color: #e0e0e06d; }
@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.rotating-svg { animation: rotate 2s linear infinite; }
.aparecer-div { opacity: 0; transition: opacity 0.5s ease-in-out; }
.aparecer-div-mostrar { opacity: 1; }
</style>
