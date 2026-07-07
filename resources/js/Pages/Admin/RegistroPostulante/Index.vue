<template>
  <Head title="Registro de Postulante" />

  <AuthenticatedLayout>
    <div class="bg-white overflow-hidden shadow-xl rounded-2xl" style="min-height: calc(100vh - 103px);">

      <!-- Header -->
      <div class="border-b border-gray-100 px-8 py-6 bg-white rounded-t-2xl">
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Registro de Postulante</h1>
        <p class="text-sm text-gray-500 mt-1">Registre o edite un postulante con todos los datos de la preinscripción</p>
      </div>

      <div class="px-6 md:px-8 py-6">

        <!-- ── Barra superior: Proceso + Búsqueda DNI ──────────── -->
        <div class="mb-6 flex flex-col md:flex-row gap-4 items-start md:items-end">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Proceso *</label>
            <a-select
              v-model:value="procesoSeleccionado"
              placeholder="Seleccionar proceso..."
              style="width: 100%;"
              size="large"
              :options="procesos.map(p => ({ value: p.id, label: p.nombre }))"
            />
          </div>
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar por DNI (editar existente)</label>
            <a-input-search
              v-model:value="datospersonales.nro_doc"
              placeholder="Ingrese DNI (8 dígitos)"
              :maxlength="datospersonales.tipo_doc === 1 ? 8 : 12"
              size="large"
              :loading="buscando"
              @search="loadByDni"
            />
          </div>
          <a-button size="large" @click="limpiar">Limpiar</a-button>
        </div>

        <a-alert
          v-if="esEdicion"
          message="Modo edición — Los cambios se actualizarán sobre el registro existente."
          type="info"
          show-icon
          class="mb-6"
        />

        <!-- ── Formulario por secciones ────────────────────────── -->
        <a-collapse v-model:activeKey="activeTabs" :bordered="false" style="background: transparent;">

          <!-- ═══ 1. Datos Personales ═══ -->
          <a-collapse-panel key="1" :header="panelHeader('1', 'Datos Personales')" style="background: #f9fafb; border-radius: 12px; margin-bottom: 12px; border: 1px solid #e5e7eb; overflow: hidden;">
            <a-form :model="datospersonales" layout="vertical">
              <a-row :gutter="[16, 8]">
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Tipo Doc" name="tipo_doc">
                    <a-select v-model:value="datospersonales.tipo_doc" :options="tipo_docs" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="N° Documento *" name="nro_doc" :rules="[{ required: true, message: 'N° documento requerido' }]">
                    <a-input v-model:value="datospersonales.nro_doc" :maxlength="datospersonales.tipo_doc === 1 ? 8 : 12" placeholder="Número de documento" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Ap. Paterno *" name="primer_apellido" :rules="[{ required: true, message: 'Apellido requerido' }]">
                    <a-input v-model:value="datospersonales.primer_apellido" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Ap. Materno">
                    <a-input v-model:value="datospersonales.segundo_apellido" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Ap. Casada">
                    <a-input v-model:value="datospersonales.apellido_casada" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Nombres *" name="nombres" :rules="[{ required: true, message: 'Nombres requeridos' }]">
                    <a-input v-model:value="datospersonales.nombres" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Sexo *" name="sexo" :rules="[{ required: true, message: 'Sexo requerido' }]">
                    <a-select v-model:value="datospersonales.sexo" :options="sexos" placeholder="Seleccionar" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Estado Civil">
                    <a-select v-model:value="datospersonales.estado_civil" :options="estados_civil" placeholder="Seleccionar" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="F. Nacimiento *">
                    <a-date-picker v-model:value="datospersonales.fec_nacimiento" format="DD/MM/YYYY" style="width: 100%;" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Correo *" name="correo" :rules="[{ required: true, message: 'Correo requerido' }, { type: 'email', message: 'Correo inválido' }]">
                    <a-input v-model:value="datospersonales.correo" placeholder="correo@ejemplo.com" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Celular *" name="celular" :rules="[{ required: true, message: 'Celular requerido' }]">
                    <a-input v-model:value="datospersonales.celular" :maxlength="9" placeholder="999999999" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Ubigeo Nacimiento *">
                    <a-select
                      v-model:value="ubigeoNacSeleccionado"
                      show-search
                      placeholder="Buscar ubigeo..."
                      :options="ubigeoNacOptions"
                      :field-names="{ value: 'key', label: 'value' }"
                      :filter-option="false"
                      @search="(term) => getUbigeos(term, ubigeoNacOptions)"
                      @dropdownVisibleChange="(open) => { if (open && !ubigeoNacOptions.length) getUbigeos('', ubigeoNacOptions) }"
                      @change="onSelectUbigeoNac"
                    />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Dirección">
                    <a-input v-model:value="datospersonales.direccion" />
                  </a-form-item>
                </a-col>
              </a-row>
            </a-form>
          </a-collapse-panel>

          <!-- ═══ 2. Datos de Residencia ═══ -->
          <a-collapse-panel key="2" :header="panelHeader('2', 'Datos de Residencia')" style="background: #f9fafb; border-radius: 12px; margin-bottom: 12px; border: 1px solid #e5e7eb; overflow: hidden;">
            <a-form :model="datosresidencia" layout="vertical">
              <a-row :gutter="[16, 8]">
                <a-col :xs="24" :sm="12" :md="8">
                  <a-form-item label="Ubigeo Residencia *">
                    <a-select
                      v-model:value="ubigeoResSeleccionado"
                      show-search
                      placeholder="Buscar ubigeo..."
                      :options="ubigeoResOptions"
                      :field-names="{ value: 'key', label: 'value' }"
                      :filter-option="false"
                      @search="(term) => getUbigeos(term, ubigeoResOptions)"
                      @dropdownVisibleChange="(open) => { if (open && !ubigeoResOptions.length) getUbigeos('', ubigeoResOptions) }"
                      @change="onSelectUbigeoRes"
                    />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="8">
                  <a-form-item label="Dirección">
                    <a-input v-model:value="datosresidencia.direccion" placeholder="Dirección de residencia" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="8">
                  <a-form-item label="País">
                    <a-input :value="datosresidencia.pais === 125 ? 'Perú' : 'Extranjero'" disabled />
                  </a-form-item>
                </a-col>
              </a-row>
            </a-form>
          </a-collapse-panel>

          <!-- ═══ 3. Datos del Colegio ═══ -->
          <a-collapse-panel key="3" :header="panelHeader('3', 'Datos del Colegio')" style="background: #f9fafb; border-radius: 12px; margin-bottom: 12px; border: 1px solid #e5e7eb; overflow: hidden;">
            <a-form :model="datoscolegio" layout="vertical">
              <a-row :gutter="[16, 8]">
                <a-col :xs="24" :sm="12" :md="8">
                  <a-form-item label="Ubigeo del Colegio">
                    <a-select
                      v-model:value="ubigeoColeSeleccionado"
                      show-search
                      placeholder="Buscar ubigeo..."
                      :options="ubigeoColeOptions"
                      :field-names="{ value: 'key', label: 'value' }"
                      :filter-option="false"
                      @search="(term) => getUbigeos(term, ubigeoColeOptions)"
                      @dropdownVisibleChange="(open) => { if (open && !ubigeoColeOptions.length) getUbigeos('', ubigeoColeOptions) }"
                      @change="onSelectUbigeoCole"
                    />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="8">
                  <a-form-item label="Colegio">
                    <a-select
                      v-model:value="datospersonales.id_colegio"
                      placeholder="Seleccionar colegio"
                      :options="colegios"
                      :field-names="{ value: 'value', label: 'label' }"
                      :disabled="!colegios.length"
                      show-search
                      :filter-option="filterColegio"
                    />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="8">
                  <a-form-item label="Año de Egreso">
                    <a-select v-model:value="datospersonales.anio_egreso" placeholder="Seleccionar año">
                      <a-select-option v-for="y in aniosEgreso" :key="y" :value="y">{{ y }}</a-select-option>
                    </a-select>
                  </a-form-item>
                </a-col>
              </a-row>
            </a-form>
          </a-collapse-panel>

          <!-- ═══ 4. Datos del Padre ═══ -->
          <a-collapse-panel key="4" :header="panelHeader('4', 'Datos del Padre / Tutor')" style="background: #f9fafb; border-radius: 12px; margin-bottom: 12px; border: 1px solid #e5e7eb; overflow: hidden;">
            <a-form :model="datospadre" layout="vertical">
              <a-row :gutter="[16, 8]">
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="DNI">
                    <a-input v-model:value="datospadre.dni" :maxlength="8" placeholder="DNI del padre" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Ap. Paterno">
                    <a-input v-model:value="datospadre.paterno" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Ap. Materno">
                    <a-input v-model:value="datospadre.materno" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Nombres">
                    <a-input v-model:value="datospadre.nombres" />
                  </a-form-item>
                </a-col>
              </a-row>
            </a-form>
          </a-collapse-panel>

          <!-- ═══ 5. Datos de la Madre ═══ -->
          <a-collapse-panel key="5" :header="panelHeader('5', 'Datos de la Madre')" style="background: #f9fafb; border-radius: 12px; margin-bottom: 12px; border: 1px solid #e5e7eb; overflow: hidden;">
            <a-form :model="datosmadre" layout="vertical">
              <a-row :gutter="[16, 8]">
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="DNI">
                    <a-input v-model:value="datosmadre.dni" :maxlength="8" placeholder="DNI de la madre" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Ap. Paterno">
                    <a-input v-model:value="datosmadre.paterno" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Ap. Materno">
                    <a-input v-model:value="datosmadre.materno" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Nombres">
                    <a-input v-model:value="datosmadre.nombres" />
                  </a-form-item>
                </a-col>
              </a-row>
            </a-form>
          </a-collapse-panel>

          <!-- ═══ 6. Datos Adicionales (Identidad Cultural) ═══ -->
          <a-collapse-panel key="6" :header="panelHeader('6', 'Datos Adicionales (SUNEDU)')" style="background: #f9fafb; border-radius: 12px; margin-bottom: 12px; border: 1px solid #e5e7eb; overflow: hidden;">
            <a-form :model="datos_transversales" layout="vertical">
              <a-row :gutter="[16, 8]">
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="¿Tiene discapacidad?">
                    <a-select v-model:value="datos_transversales.discapacidad" placeholder="Seleccionar">
                      <a-select-option :value="1">Sí</a-select-option>
                      <a-select-option :value="0">No</a-select-option>
                    </a-select>
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Tipo de discapacidad">
                    <a-select
                      v-model:value="datos_transversales.tipo_discapacidad"
                      :options="tipos_discapacidad"
                      :disabled="datos_transversales.discapacidad !== 1"
                      placeholder="Seleccionar"
                    />
                  </a-form-item>
                </a-col>
                <a-col v-if="datos_transversales.tipo_discapacidad === 12" :xs="24" :sm="12" :md="12">
                  <a-form-item label="Especifique">
                    <a-input v-model:value="datos_transversales.tipo_discapacidad_otro" placeholder="Describa la discapacidad" />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="¿Se identifica con alguna lengua indígena?">
                    <a-select
                      v-model:value="datos_transversales.id_condicion_lengua"
                      :options="condiciones_lengua"
                      :field-names="{ value: 'id', label: 'descripcion' }"
                      placeholder="Seleccionar"
                    />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Lengua indígena">
                    <a-select
                      v-model:value="datos_transversales.id_lengua_indigena"
                      :options="lenguas_indigenas"
                      :field-names="{ value: 'id', label: 'descripcion' }"
                      :disabled="datos_transversales.id_condicion_lengua !== SI_LENGUA_ID"
                      placeholder="Seleccionar"
                    />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="¿Pertenece a algún pueblo indígena?">
                    <a-select
                      v-model:value="datos_transversales.id_pertenencia_cultural"
                      :options="opciones_pertenencia_cultural"
                      :field-names="{ value: 'id', label: 'descripcion' }"
                      placeholder="Seleccionar"
                    />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="6">
                  <a-form-item label="Pueblo indígena">
                    <a-select
                      v-model:value="datos_transversales.id_pueblo_indigena"
                      :options="pueblos_indigenas"
                      :field-names="{ value: 'id', label: 'descripcion' }"
                      :disabled="datos_transversales.id_pertenencia_cultural !== SI_PUEBLO_ID"
                      placeholder="Seleccionar"
                    />
                  </a-form-item>
                </a-col>
              </a-row>
            </a-form>
          </a-collapse-panel>

          <!-- ═══ 7. Datos de Postulación ═══ -->
          <a-collapse-panel key="7" :header="panelHeader('7', 'Datos de Postulación')" style="background: #f9fafb; border-radius: 12px; margin-bottom: 12px; border: 1px solid #e5e7eb; overflow: hidden;">
            <a-form :model="datos_preinscripcion" layout="vertical">
              <a-row :gutter="[16, 8]">
                <a-col :xs="24" :sm="12" :md="8">
                  <a-form-item label="Modalidad">
                    <a-select
                      v-model:value="datos_preinscripcion.modalidad"
                      :options="modalidades"
                      :field-names="{ value: 'value', label: 'label' }"
                      placeholder="Seleccionar modalidad"
                    />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="8">
                  <a-form-item label="Programa">
                    <a-select
                      v-model:value="datos_preinscripcion.programa"
                      :options="programas"
                      :field-names="{ value: 'value', label: 'label' }"
                      placeholder="Seleccionar programa"
                      show-search
                      :filter-option="filterColegio"
                    />
                  </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="8">
                  <a-form-item label="Código de matrícula (CEPREUNA)">
                    <a-input v-model:value="datos_preinscripcion.observacion" placeholder="Código de matrícula" />
                  </a-form-item>
                </a-col>
                <template v-if="![1, 2, 3].includes(datos_preinscripcion.modalidad)">
                  <a-col :xs="24" :sm="12" :md="8">
                    <a-form-item label="Tipo de certificado">
                      <a-select v-model:value="datos_preinscripcion.tipo_certificado" placeholder="Seleccionar">
                        <a-select-option value="CERTIFICADO BLANCO">CERTIFICADO BLANCO</a-select-option>
                        <a-select-option value="CERTIFICADO AMARILLO">CERTIFICADO AMARILLO</a-select-option>
                        <a-select-option value="CONSTANCIA DE ESTUDIOS">CONSTANCIA DE ESTUDIOS</a-select-option>
                      </a-select>
                    </a-form-item>
                  </a-col>
                  <a-col :xs="24" :sm="12" :md="8">
                    <a-form-item label="Código de certificado">
                      <a-input v-model:value="datos_preinscripcion.codigo_certificado" placeholder="Código" />
                    </a-form-item>
                  </a-col>
                </template>
                <a-col v-if="datos_preinscripcion.programa === 38 || datos_preinscripcion.programa === 16" :xs="24" :sm="12" :md="8">
                  <a-form-item label="Código de examen médico">
                    <a-input v-model:value="datos_preinscripcion.codigo_medico" placeholder="Código" />
                  </a-form-item>
                </a-col>
              </a-row>
            </a-form>
          </a-collapse-panel>

        </a-collapse>

        <!-- ── Botones de acción ─────────────────────────────── -->
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
          <button @click="limpiar" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
            Limpiar
          </button>
          <button
            @click="saveAll"
            :disabled="guardando"
            class="px-6 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
          >
            {{ guardando ? 'Guardando...' : (esEdicion ? 'Actualizar Postulante' : 'Registrar Postulante') }}
          </button>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import {
  CheckCircleOutlined,
  UserOutlined,
  EnvironmentOutlined,
  BankOutlined,
  ManOutlined,
  WomanOutlined,
  SolutionOutlined,
  FormOutlined,
} from '@ant-design/icons-vue'
import { useRegistroPostulanteAdmin } from '@/composables/useRegistroPostulanteAdmin'

const {
  // State
  datospersonales, datosresidencia, datoscolegio,
  datospadre, datosmadre, datos_transversales, datos_preinscripcion,
  // UI
  guardando, buscando, procesoSeleccionado, esEdicion,
  procesos, modalidades, programas, colegios,
  ubigeoNacOptions, ubigeoNacSeleccionado,
  ubigeoResOptions, ubigeoResSeleccionado,
  ubigeoColeOptions, ubigeoColeSeleccionado,
  condiciones_lengua, opciones_pertenencia_cultural,
  lenguas_indigenas, pueblos_indigenas,
  // Constants
  tipo_docs, estados_civil, sexos, tipos_discapacidad, aniosEgreso,
  SI_LENGUA_ID, SI_PUEBLO_ID,
  // Validators
  validateCelular, validateCorreo,
  // Data loading
  loadProcesos, loadByDni, loadCulturalData,
  // Ubigeo
  getUbigeos, onSelectUbigeoNac, onSelectUbigeoRes, onSelectUbigeoCole,
  // Save
  saveAll, limpiar,
} = useRegistroPostulanteAdmin()

const activeTabs = ref(['1'])

const panelHeader = (key, title) => title

const filterColegio = (input, option) => {
  return option.label?.toLowerCase().includes(input.toLowerCase())
}

onMounted(() => {
  loadProcesos()
  loadCulturalData()
})
</script>

<style scoped>
:deep(.ant-collapse-header) {
  font-weight: 600 !important;
  font-size: 1rem !important;
}

:deep(.ant-collapse-content-box) {
  padding: 16px !important;
}

:deep(.ant-input),
:deep(.ant-select-selector),
:deep(.ant-picker) {
  border-radius: 6px !important;
}
</style>
