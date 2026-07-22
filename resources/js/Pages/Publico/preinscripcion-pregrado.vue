<template>
  <Head title="Preinscripción" />
  <Layout :nombre="props.procceso_seleccionado.nombre">

    <!-- Verification Modal -->
    <VerificationModal
      :visible="open"
      :datospersonales="datospersonales"
      :datosresidencia="datosresidencia"
      :datoscolegio="datoscolegio"
      :datospadre="datospadre"
      :datosmadre="datosmadre"
      :datos_transversales="datos_transversales"
      :datos_preinscripcion="datos_preinscripcion"
      :checkbox1="checkbox1"
      :temp_date="temp_date"
      :nombrecolegiox="nombrecolegiox"
      :formState="formState"
      :tipo_docs="tipo_docs"
      :estados_civil="estados_civil"
      :sexos="sexos"
      :programas="programas"
      :condiciones_lengua="condiciones_lengua"
      :lenguas_indigenas="lenguas_indigenas"
      :opciones_pertenencia_cultural="opciones_pertenencia_cultural"
      :pueblos_indigenas="pueblos_indigenas"
      @close="open = false"
      @submit="submit"
      @update:checkbox1="(val) => checkbox1 = val"
    />

    <!-- Example modals -->
    <a-modal :visible="ejemplo" @update:visible="ejemplo = $event" :footer="false">
      <span class="font-bold text-xl">{{ datos_preinscripcion.tipo_certificado }}</span>
      <a-tabs v-model:activeKey="activeKey">
        <a-tab-pane key="1" tab="CERT. AMARILLO">
          <div style="max-height: 450px; overflow-y: scroll;">
            <img src="../../../assets/imagenes/certificados/amarillo.jpg">
          </div>
        </a-tab-pane>
        <a-tab-pane key="2" tab="CERT. BLANCO">
          <div><img src="../../../assets/imagenes/certificados/blanco.jpg"></div>
        </a-tab-pane>
        <a-tab-pane key="3" tab="CONST. DE LOGROS">
          <div><img src="../../../assets/imagenes/certificados/constancia.jpg"></div>
        </a-tab-pane>
      </a-tabs>
    </a-modal>

    <a-modal :visible="modalUbigeo" @update:visible="modalUbigeo = $event" :footer="false">
      <span class="font-bold text-xl">Ejemplos de DNI</span>
      <a-tabs v-model:activeKey="activeKey">
        <a-tab-pane key="1" tab="DNI ELECTRÓNICO">
          <div style="max-height: 450px; overflow-y: scroll;">
            <img src="../../../assets/imagenes/dni/1.jpg">
          </div>
        </a-tab-pane>
        <a-tab-pane key="2" tab="DNI AMARILLO">
          <div><img src="../../../assets/imagenes/dni/2.jpg"></div>
        </a-tab-pane>
        <a-tab-pane key="3" tab="DNI AZUL">
          <div><img src="../../../assets/imagenes/dni/3.jpg"></div>
        </a-tab-pane>
      </a-tabs>
    </a-modal>

    <!-- Carreras Previas Modal -->
    <CarrerasPreviasModal
      :visible="modalcarrerasprevias"
      @update:visible="modalcarrerasprevias = $event"
      :loading="loading"
      :modalSancionado="modalSancionado"
      :anteriores="anteriores"
      :datacepre="datacepre"
      :selectedItems="selectedItems"
      :confirmacion="confirmacion"
      :id_modalidad_proceso="props.procceso_seleccionado.id_modalidad_proceso"
      :toggleSelection="toggleSelection"
      @close="handleCerrarVerificacion"
      @cancel="cancelarInscripcion"
    />

    <!-- Step 0: Identity Validation -->
    <IdentityValidation
      v-if="pagina_pre === 0"
      :formState="formState"
      :datospersonales="datospersonales"
      :codigo_aleatorio="codigo_aleatorio"
      :loading="loading"
      :modalCargarDatos="modalCargarDatos"
      :datosPrevios="datosPrevios"
      :enviandoCodigo="enviandoCodigo"
      :verificandoCodigo="verificandoCodigo"
      :codigoVerificacion="codigoVerificacion"
      :codigoEnviado="codigoEnviado"
      :codigoExpirado="codigoExpirado"
      :codigoError="codigoError"
      :emailMasked="emailMasked"
      :requiereCodigoVerificacion="requiereCodigoVerificacion"
      :countdownSegundos="countdownSegundos"
      :setFormRef="setFormRef"
      :dniInput="dniInput"
      :getCodigoAleatorio="getCodigoAleatorio"
      :validateCodigoSecreto="validateCodigoSecreto"
      :aceptarCargarDatos="aceptarCargarDatos"
      :rechazarCargarDatos="rechazarCargarDatos"
      :solicitarCodigoVerificacion="solicitarCodigoVerificacion"
      :verificarCodigoYCargar="verificarCodigoYCargar"
      :resetCodigoVerificacion="resetCodigoVerificacion"
      :setCodigoVerificacion="setCodigoVerificacion"
      :setCodigoError="setCodigoError"
      @proceed="iniciarPostulacion"
    />

    <!-- Step 1: Personal Data -->
    <PersonalDataForm
      v-if="pagina_pre === 1"
      :datospersonales="datospersonales"
      :setFormDatosPersonales="setFormDatosPersonales"
      :pimerapellidoInput="pimerapellidoInput"
      :nombresInput="nombresInput"
      :celularInput="celularInput"
      :validateCorreo="validateCorreo"
      :validateCelular="validateCelular"
      :validateFechaNacimiento="validateFechaNacimiento"
      :ubigeoNacOptions="ubigeoNacOptions"
      :ubigeoNacSeleccionado="ubigeoNacSeleccionado"
      :getUbigeosNac="getUbigeosNac"
      :onSelectUbigeoNac="onSelectUbigeoNac"
      @showUbigeoExample="modalUbigeo = true"
    />

    <!-- Step 2: Residence Data -->
    <ResidenceDataForm
      v-if="pagina_pre === 2"
      :datosresidencia="datosresidencia"
      :datospersonales="datospersonales"
      :setFormDatosResidencia="setFormDatosResidencia"
      :ubigeoResOptions="ubigeoResOptions"
      :ubigeoResSeleccionado="ubigeoResSeleccionado"
      :getUbigeosRes="getUbigeosRes"
      :onSelectUbigeoRes="onSelectUbigeoRes"
    />

    <!-- Step 3: School Data -->
    <SchoolDataForm
      v-if="pagina_pre === 3"
      :datoscolegio="datoscolegio"
      :setFormDatosColegio="setFormDatosColegio"
      :ubigeoColeOptions="ubigeoColeOptions"
      :ubigeoColeSeleccionado="ubigeoColeSeleccionado"
      :getUbigeosCole="getUbigeosCole"
      :onSelectUbigeoCole="onSelectUbigeoCole"
      :onSelectColegio="onSelectColegio"
      :colegios="colegios"
    />

    <!-- Step 4: Father Data -->
    <ParentDataForm
      v-if="pagina_pre === 4"
      :datos="datospadre"
      title="Datos del padre"
      name="datospadre"
      stepLabel="Paso 4 de 7"
      :setFormRef="setFormDatosPadre"
    />

    <!-- Step 5: Mother Data -->
    <ParentDataForm
      v-if="pagina_pre === 5"
      :datos="datosmadre"
      title="Datos de la madre"
      name="datosmadre"
      stepLabel="Paso 5 de 7"
      :setFormRef="setFormDatosMadre"
    />

    <!-- Step 6: Additional Data (discapacidad + cultural) -->
    <AdditionalDataForm
      v-if="pagina_pre === 6"
      :datos_transversales="datos_transversales"
      :setFormDatosTransversales="setFormDatosTransversales"
      :condiciones_lengua="condiciones_lengua"
      :lenguas_indigenas="lenguas_indigenas"
      :opciones_pertenencia_cultural="opciones_pertenencia_cultural"
      :pueblos_indigenas="pueblos_indigenas"
    />

    <!-- Step 7: Postulation Data -->
    <PostulationDataForm
      v-if="pagina_pre === 7"
      :datos_preinscripcion="datos_preinscripcion"
      :setFormPreinscripcion="setFormPreinscripcion"
      :modalidades="modalidades"
      :programas="programas"
      :carreras_previas="carreras_previas"
      :id_modalidad_proceso="props.procceso_seleccionado.id_modalidad_proceso"
      :toggleSelection2="toggleSelection2"
      @showExample="ejemplo = true"
    />

    <!-- Step 8: Success -->
    <SuccessMessage
      v-if="pagina_pre === 8 || postulante_inscrito === 1"
      @downloadSolicitud="getDocs"
      @download="descargaReglamento"
      @diagnostico="irDiagnostico"
    />

    <!-- Navigation -->
    <NavigationButtons
      v-if="pagina_pre !== 0 && pagina_pre !== 8 && postulante_inscrito !== 1"
      :pagina_pre="pagina_pre"
      :avance="avance"
      :bottom="bottom"
      @previous="prev"
      @next="handleNext"
      @verify="abrirModalDatos"
    />

  </Layout>
</template>

<script setup>
import Layout from '@/Layouts/LayoutPreinscripcionSegundas.vue'
import { Head } from '@inertiajs/vue3'
import { usePreinscripcionPregrado } from '@/composables/usePreinscripcionPregrado'

import IdentityValidation from './components/preinscripcion/IdentityValidation.vue'
import PersonalDataForm from './components/preinscripcion/PersonalDataForm.vue'
import ResidenceDataForm from './components/preinscripcion/ResidenceDataForm.vue'
import SchoolDataForm from './components/preinscripcion/SchoolDataForm.vue'
import ParentDataForm from './components/preinscripcion/ParentDataForm.vue'
import AdditionalDataForm from './components/preinscripcion/AdditionalDataForm.vue'
import PostulationDataForm from './components/preinscripcion/PostulationDataForm.vue'
import SuccessMessage from './components/preinscripcion/SuccessMessage.vue'
import NavigationButtons from './components/preinscripcion/NavigationButtons.vue'
import VerificationModal from './components/preinscripcion/VerificationModal.vue'
import CarrerasPreviasModal from './components/preinscripcion/CarrerasPreviasModal.vue'

const props = defineProps(['procceso_seleccionado'])

const {
  // Form state
  formState,
  datospersonales,
  datosresidencia,
  datoscolegio,
  datospadre,
  datosmadre,
  datos_transversales,
  datos_preinscripcion,

  // UI state
  pagina_pre,
  avance,
  avance_current,
  id_pasos,
  examen,
  open,
  checkbox1,
  activeKey,
  loading,
  modalcarrerasprevias,
  modalSancionado,
  modalUbigeo,
  ejemplo,
  presionado,
  participa,
  postulante_inscrito,
  confirmacion,
  codigo_aleatorio,
  nombrecolegiox,
  temp_date,
  datacepre,
  bottom,
  anteriores,
  carreras_previas,
  modalCargarDatos,
  datosPrevios,

  // Data lists
  departamentos,
  departamentoscolegio,
  provincias,
  provinciasC,
  distritos,
  distritosC,
  colegios,
  modalidades,
  programas,

  // Search refs
  buscarDep,
  buscarDepC,
  buscarProv,
  buscarProvC,
  buscarDist,
  buscarDistC,

  // Nacimiento ubigeo (unified search)
  ubigeoNacOptions,
  ubigeoNacSeleccionado,
  getUbigeosNac,
  onSelectUbigeoNac,

  // Residencia ubigeo (unified search)
  ubigeoResOptions,
  ubigeoResSeleccionado,
  getUbigeosRes,
  onSelectUbigeoRes,

  // Colegio ubigeo (unified search)
  ubigeoColeOptions,
  ubigeoColeSeleccionado,
  getUbigeosCole,
  onSelectUbigeoCole,
  onSelectColegio,

  // Cultural data lists
  condiciones_lengua,
  opciones_pertenencia_cultural,
  lenguas_indigenas,
  pueblos_indigenas,

  // Constants
  tipo_docs,
  estados_civil,
  sexos,

  // Input sanitizers
  dniInput,
  nombresInput,
  pimerapellidoInput,
  celularInput,

  // Validators
  validateFechaNacimiento,
  validateCelular,
  validateCorreo,
  validateCodigoSecreto,
  getCodigoAleatorio,

  // Navigation
  next,
  prev,
  abrirModalDatos,
  cancelarInscripcion,

  // Step tracking
  getPasos,

  // Data loading
  getDatosPersonales,
  getModalidades,

  // Save methods
  saveDatosPersonales,
  saveDatosResidencia,
  savecolegio,
  saveApoderado,
  saveApoderadoM,
  saveAdditionalData,
  submit,

  // Apoderado helpers
  getApoderado,
  getApoderadoM,

  // Form ref setters
  setFormRef,
  setFormDatosPersonales,
  setFormDatosResidencia,
  setFormDatosColegio,
  setFormDatosPadre,
  setFormDatosMadre,
  setFormDatosTransversales,
  setFormPreinscripcion,

  // UI helpers
  getDocs,
  irDiagnostico,
  descargaReglamento,

  // Cargar datos
  aceptarCargarDatos,
  rechazarCargarDatos,
  verificarDatosExistentes,
  iniciarPostulacion,

  // Code verification
  enviandoCodigo,
  verificandoCodigo,
  codigoVerificacion,
  codigoEnviado,
  codigoExpirado,
  codigoError,
  emailMasked,
  requiereCodigoVerificacion,
  countdownSegundos,
  solicitarCodigoVerificacion,
  verificarCodigoYCargar,
  resetCodigoVerificacion,
  setCodigoVerificacion,
  setCodigoError,

  // Carreras previas
  toggleSelection,
  toggleSelection2,
  selectedItems,
} = usePreinscripcionPregrado(props)

// Close verification modal and check for existing data
const handleCerrarVerificacion = async () => {
  modalcarrerasprevias.value = false
  if (props.procceso_seleccionado.id_modalidad_proceso == 2) {
    if (pagina_pre.value === 0) {
      pagina_pre.value = 1
    }
    loading.value = false
    return
  }
  // Non-CEPREUNA: verify existing data before allowing access
  const tieneDatos = await verificarDatosExistentes()
  if (tieneDatos) {
    // "En buena hora" modal shown or data loaded directly (if verification disabled)
    loading.value = false
    return
  }
  // No existing data — fetch from external API
  await getDatosPersonales()
  loading.value = false
}

// Step-specific next handler
const handleNext = async () => {
  try {
    switch (pagina_pre.value) {
      case 1: await saveDatosPersonales(); break
      case 2: await saveDatosResidencia(); break
      case 3: await savecolegio(); break
      case 4: await saveApoderado(); break
      case 5: await saveApoderadoM(); break
      case 6: await saveAdditionalData(); break
    }
  } catch (error) {
    console.error('Error en guardado:', error)
  }
}
</script>

<style scoped>
:deep(.ant-input),
:deep(.ant-select-selector),
:deep(.ant-picker) {
  border-radius: 6px !important;
}

:deep(.ant-input):focus,
:deep(.ant-select-selector):focus,
:deep(.ant-picker):focus {
  border-color: #3b82f6 !important;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1) !important;
}

:deep(.ant-btn-primary) {
  background-color: #2563eb !important;
  border-color: #2563eb !important;
}

:deep(.ant-btn-primary:hover) {
  background-color: #1d4ed8 !important;
  border-color: #1d4ed8 !important;
}

:deep(.ant-radio-button-wrapper-checked) {
  background-color: #2563eb !important;
  border-color: #2563eb !important;
  color: white !important;
}

:deep(.ant-alert-warning) {
  background-color: #fffbeb !important;
  border-color: #fde68a !important;
}
:deep(.ant-alert-warning .ant-alert-icon) {
  color: #f59e0b !important;
}
:deep(.ant-alert-warning .ant-alert-message) {
  color: #92400e !important;
  font-weight: 500;
}
:deep(.ant-alert-warning .ant-alert-description) {
  color: #92400e !important;
}

.fixed {
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
}

@media (max-width: 768px) {
  .step-indicator {
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
  }
  .step-line {
    width: 2px !important;
    height: 40px !important;
    margin: 0 auto;
  }
}
</style>
