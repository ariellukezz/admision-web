<template>
  <Head title="Preinscripción" />
  <Layout :nombre="props.procceso_seleccionado.nombre">
    <IdentityValidation
      v-if="pagina_pre === 0"
      :formState="formState"
      :formRef="formRef"
      :codigo_aleatorio="codigo_aleatorio"
      :datospersonales="datospersonales"
      :participa="participa"
      :modalcarrerasprevias="modalcarrerasprevias"
      :dniInput="dniInput"
      :getCodigoAleatorio="getCodigoAleatorio"
      :validateCodigoSecreto="validateCodigoSecreto"
      :getDatosPersonales="getDatosPersonales"
      @proceed="pagina_pre = 1"
      @update:dni="(val) => formState.dni = val"
      @update:codigo_secreto="(val) => formState.codigo_secreto = val"
      @update:tipo_doc="(val) => datospersonales.tipo_doc = val"
    />

    <PersonalDataForm
      v-if="pagina_pre === 1"
      :setFormRef="setFormRef"
      :datospersonales="datospersonales"
      :ubigeosNacimiento="ubigeosNacimiento"
      :residencias="residencias"
      :buscarNacimiento="buscarNacimiento"
      :buscarResidencia="buscarResidencia"
      :pimerapellidoInput="pimerapellidoInput"
      :nombresInput="nombresInput"
      :validateFechaNacimiento="validateFechaNacimiento"
      :validateCelular="validateCelular"
      :validateCorreo="validateCorreo"
      :correoInput="correoInput"
      :onSelectNacimiento="onSelectNacimiento"
      :onSelectResidencias="onSelectResidencias"
      :celularInput="celularInput"
      @update:primerapellido="(val) => datospersonales.primerapellido = val"
      @update:segundo_apellido="(val) => datospersonales.segundo_apellido = val"
      @update:nombres="(val) => datospersonales.nombres = val"
      @update:sexo="(val) => datospersonales.sexo = val"
      @update:estado_civil="(val) => datospersonales.estado_civil = val"
      @update:fec_nacimiento="(val) => datospersonales.fec_nacimiento = val"
      @update:celular="(val) => datospersonales.celular = val"
      @update:correo="(val) => datospersonales.correo = val"
      @update:direccion="(val) => datospersonales.direccion = val"
      @update:egreso="(val) => datospersonales.egreso = val"
      @update:nacimiento="(val) => datospersonales.nacimiento = val"
      @update:residencia="(val) => datospersonales.residencia = val"
      @update:buscarNacimiento="(val) => buscarNacimiento = val"
      @update:buscarResidencia="(val) => buscarResidencia = val"
    />

    <AdditionalDataForm
      v-if="pagina_pre === 2"
      :datospersonales="datospersonales"
      :datos_transversales="datos_transversales"
      :condiciones_lengua="condiciones_lengua"
      :lenguas_indigenas="lenguas_indigenas"
      :opciones_pertenencia_cultural="opciones_pertenencia_cultural"
      :pueblos_indigenas="pueblos_indigenas"
      :setFormDatosTransversales="setFormDatosTransversales"
      @update:discapacidad="(val) => datos_transversales.discapacidad = val"
      @update:tipo_discapacidad="(val) => datos_transversales.tipo_discapacidad = val"
      @update:id_condicion_lengua="(val) => datos_transversales.id_condicion_lengua = val"
      @update:id_lengua_indigena="(val) => datos_transversales.id_lengua_indigena = val"
      @update:id_pertenencia_cultural="(val) => datos_transversales.id_pertenencia_cultural = val"
      @update:id_pueblo_indigena="(val) => datos_transversales.id_pueblo_indigena = val"
    />
    <ProgramAndDocs
      v-if="pagina_pre === 6"
      :setFormPreinscripcion="setFormPreinscripcion"
      :datos_preinscripcion="datos_preinscripcion"
      :programas="programas"
      @update:programa="(val) => datos_preinscripcion.programa = val"
    >
      <template #documents>
        <Titulo :id_proceso="props.procceso_seleccionado.id" :dni="formState.dni" />
      </template>
    </ProgramAndDocs>

    <SuccessMessage
      v-if="pagina_pre === 7 || postulante_inscrito === 1"
      @download="descargaReglamento"
    />

    <NavigationButtons
      v-if="pagina_pre !== 0 && pagina_pre !== 7 && postulante_inscrito !== 1"
      :pagina_pre="pagina_pre"
      @previous="handlePrevious"
      @next="handleNext"
      @verify="abrirModalDatos"
    />

    <VerificationModal
      :open="open"
      :datospersonales="datospersonales"
      :datos_preinscripcion="datos_preinscripcion"
      :checkbox1="checkbox1"
      :tipo_docs="tipo_docs"
      :estados_civil="estados_civil"
      :sexos="sexos"
      :especialidades="programas"
      :formState="formState"
      @close="open = false"
      @submit="submit"
    />


    <a-modal
      :open="modalcarrerasprevias"
      centered
      :footer="null"
      :closable="false"
      :maskClosable="false"
    >
      <div class="py-12 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16">
          <div class="w-12 h-12 border-4 border-blue-100 border-t-blue-600 rounded-full animate-spin"></div>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">Verificando información</h3>
        <p class="text-gray-600">Estamos revisando sus datos en el sistema, tomará solo unos momentos.</p>
      </div>
    </a-modal>
  </Layout>
</template>

<script setup>
import Layout from '@/Layouts/LayoutPreinscripcionSegundas.vue'
import { defineProps } from 'vue'
import { Head } from '@inertiajs/vue3'
import { message } from 'ant-design-vue'
import IdentityValidation from './components/IdentityValidation.vue'
import PersonalDataForm from './components/PersonalDataForm.vue'
import AdditionalDataForm from './components/AdditionalDataForm.vue'
import ProgramAndDocs from './components/ProgramAndDocs.vue'
import SuccessMessage from './components/SuccessMessage.vue'
import NavigationButtons from './components/NavigationButtons.vue'
import VerificationModal from './components/VerificationModal.vue'
import Titulo from './components/titulo.vue'
import { usePreinscripcion } from '@/composables/usePreinscripcion'

const props = defineProps(['procceso_seleccionado'])

const {
  formState,
  codigo_aleatorio,
  formRef,

  formPreinscripcion,
  datospersonales,
  datos_transversales,
  datos_preinscripcion,
  pagina_pre,
  modalcarrerasprevias,
  loading,
  open,
  checkbox1,
  postulante_inscrito,
  participa,
  buscarResidencia,
  residencias,
  buscarNacimiento,
  ubigeosNacimiento,
  condiciones_lengua,
  opciones_pertenencia_cultural,
  lenguas_indigenas,
  pueblos_indigenas,
  programas,
  tipo_docs,
  estados_civil,
  sexos,
  dniInput,
  nombresInput,
  pimerapellidoInput,
  celularInput,
  correoInput,
  validateFechaNacimiento,
  validateCodigoSecreto,
  validateCelular,
  validateCorreo,
  getCodigoAleatorio,
  getDatosPersonales,
  saveDatosPersonales,
  getProgramas,
  validateDocuments,
  descargaReglamento,
  onSelectNacimiento,
  onSelectResidencias,
  setFormRef,
  setFormPreinscripcion,
  setFormDatosTransversales,
  saveAdditionalData,
  consultaInscripcion,
  getPasoRegistrado,
  getDataPrisma,
  submit,
} = usePreinscripcion(props)

const handlePrevious = () => {
  if (pagina_pre.value === 1) {
    pagina_pre.value = 0
  } else if (pagina_pre.value === 2) {
    pagina_pre.value = 1
  } else if (pagina_pre.value === 6) {
    pagina_pre.value = 2
  }
}

const handleNext = async () => {
  if (pagina_pre.value === 1) {
    await saveDatosPersonales()
  } else if (pagina_pre.value === 2) {
    await saveAdditionalData()
  }
}


const abrirModalDatos = async () => {
  try {
    console.log('Intentando abrir modal...')
    const validDocs = await validateDocuments()

    if (!validDocs) {
      return
    }
    open.value = true

  } catch (error) {
    console.error('Error al abrir el modal de datos:', error)
    message.error('Ocurrió un error al verificar los documentos.')
  }
}


</script>


<style scoped>
.modal-content::-webkit-scrollbar {
  width: 6px;
}
.modal-content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}
.modal-content::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}
.modal-content::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}

.step-enter-active,
.step-leave-active {
  transition: all 0.3s ease;
}
.step-enter-from,
.step-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

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
