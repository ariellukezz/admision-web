<template>
  <a-modal
    :open="visible"
    @cancel="$emit('close')"
    centered
    :width="800"
    :footer="null"
    :closable="true"
  >
    <div class="p-1">
      <div class="border-b border-gray-200 pb-6 mb-6">
        <div class="flex items-center">
          <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg mr-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-900">Verificación Final de Datos</h2>
            <p class="text-gray-600 mt-1">Revise y confirme su información antes de enviar</p>
          </div>
        </div>
      </div>

      <div class="max-h-[60vh] overflow-y-auto pr-2">
        <!-- Datos Personales -->
        <div class="mb-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Datos Personales
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Tipo Documento</div>
              <div class="text-gray-900 font-medium">{{ tipo_docs[datospersonales.tipo_doc] || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">N° Documento</div>
              <div class="text-gray-900 font-medium">{{ formState.dni || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Primer Apellido</div>
              <div class="text-gray-900 font-medium">{{ datospersonales.primerapellido || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Segundo Apellido</div>
              <div class="text-gray-900 font-medium">{{ datospersonales.segundo_apellido || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Nombres</div>
              <div class="text-gray-900 font-medium">{{ datospersonales.nombres || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Estado Civil</div>
              <div class="text-gray-900 font-medium">{{ estados_civil[datospersonales.estado_civil] || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Sexo</div>
              <div class="text-gray-900 font-medium">{{ sexos[datospersonales.sexo] || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Correo</div>
              <div class="text-gray-900 font-medium">{{ datospersonales.correo || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Celular</div>
              <div class="text-gray-900 font-medium">{{ datospersonales.celular || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Fecha Nacimiento</div>
              <div class="text-gray-900 font-medium">{{ temp_date || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Lugar de Nacimiento</div>
              <div class="text-gray-900 font-medium">{{ datospersonales.nacimiento_label || datospersonales.ubigeo || 'No especificado' }}</div>
            </div>
          </div>
        </div>

        <!-- Datos de Residencia -->
        <div class="mb-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Datos de Residencia
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-if="datospersonales.ubigeo_residencia" class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Departamento</div>
              <div class="text-gray-900 font-medium">{{ datosresidencia.dep || 'No especificado' }}</div>
            </div>
            <div v-if="datospersonales.ubigeo_residencia" class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Provincia</div>
              <div class="text-gray-900 font-medium">{{ datosresidencia.prov || 'No especificado' }}</div>
            </div>
            <div v-if="datospersonales.ubigeo_residencia" class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Distrito</div>
              <div class="text-gray-900 font-medium">{{ datosresidencia.dist || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Dirección</div>
              <div class="text-gray-900 font-medium">{{ datosresidencia.direccion || 'No especificado' }}</div>
            </div>
          </div>
        </div>

        <!-- Datos del Colegio -->
        <div class="mb-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
            </svg>
            Datos del Colegio
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Año de Egreso</div>
              <div class="text-gray-900 font-medium">{{ datoscolegio.egreso || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Colegio</div>
              <div class="text-gray-900 font-medium">{{ nombrecolegiox || 'No especificado' }}</div>
            </div>
          </div>
        </div>

        <!-- Datos del Padre -->
        <div class="mb-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Datos del Padre
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">DNI</div>
              <div class="text-gray-900 font-medium">{{ datospadre.dni || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Nombres</div>
              <div class="text-gray-900 font-medium">{{ datospadre.nombres || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Primer Apellido</div>
              <div class="text-gray-900 font-medium">{{ datospadre.paterno || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Segundo Apellido</div>
              <div class="text-gray-900 font-medium">{{ datospadre.materno || 'No especificado' }}</div>
            </div>
          </div>
        </div>

        <!-- Datos de la Madre -->
        <div class="mb-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Datos de la Madre
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">DNI</div>
              <div class="text-gray-900 font-medium">{{ datosmadre.dni || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Nombres</div>
              <div class="text-gray-900 font-medium">{{ datosmadre.nombres || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Primer Apellido</div>
              <div class="text-gray-900 font-medium">{{ datosmadre.paterno || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Segundo Apellido</div>
              <div class="text-gray-900 font-medium">{{ datosmadre.materno || 'No especificado' }}</div>
            </div>
          </div>
        </div>

        <!-- Datos Adicionales (SUNEDU) -->
        <div class="mb-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Datos Adicionales (SUNEDU)
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">¿Tiene discapacidad?</div>
              <div class="text-gray-900 font-medium">{{ discapacidadLabel }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Tipo de discapacidad</div>
              <div class="text-gray-900 font-medium">{{ tipoDiscapacidadLabel }}</div>
            </div>
            <div v-if="datos_transversales?.tipo_discapacidad === 12 && datos_transversales?.tipo_discapacidad_otro" class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Especificación de discapacidad</div>
              <div class="text-gray-900 font-medium">{{ datos_transversales.tipo_discapacidad_otro }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">¿Se identifica con alguna lengua indígena?</div>
              <div class="text-gray-900 font-medium">{{ condicionLenguaLabel }}</div>
            </div>
            <div v-if="datos_transversales?.id_condicion_lengua === SI_LENGUA_ID" class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Lengua indígena</div>
              <div class="text-gray-900 font-medium">{{ lenguaIndigenaLabel }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">¿Pertenece a algún pueblo indígena?</div>
              <div class="text-gray-900 font-medium">{{ pertenenciaCulturalLabel }}</div>
            </div>
            <div v-if="datos_transversales?.id_pertenencia_cultural === SI_PUEBLO_ID" class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Pueblo indígena</div>
              <div class="text-gray-900 font-medium">{{ puebloIndigenaLabel }}</div>
            </div>
          </div>
        </div>

        <!-- Datos de Postulación -->
        <div class="mb-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Datos de Postulación
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Modalidad</div>
              <div class="text-gray-900 font-medium">{{ modalidadLabel }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Programa</div>
              <div class="text-gray-900 font-medium">{{ programaLabel }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Tipo de Certificado</div>
              <div class="text-gray-900 font-medium">{{ datos_preinscripcion.tipo_certificado || 'No especificado' }}</div>
            </div>
            <div v-if="datos_preinscripcion.programa === 38 || datos_preinscripcion.programa === 16" class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Código Ex. Médico</div>
              <div class="text-gray-900 font-medium">{{ datos_preinscripcion.codigo_medico || 'No especificado' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Código Certificado</div>
              <div class="text-gray-900 font-medium">{{ datos_preinscripcion.codigo_certificado || 'No especificado' }}</div>
            </div>
          </div>
        </div>

        <!-- Declaración Jurada -->
        <div class="mb-8">
          <div class="bg-blue-50 border border-blue-100 rounded-lg p-6">
            <a-checkbox
              :checked="checkbox1"
              @change="$emit('update:checkbox1', $event.target.checked)"
              class="w-full"
            >
              <div class="flex items-start">
                <div class="inline-flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg mr-4 flex-shrink-0">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                  </svg>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900 mb-1">Declaración Jurada</h4>
                  <p class="text-gray-600 text-sm">
                    Certifico bajo declaración jurada que toda la información proporcionada es veraz,
                    completa y corresponde fielmente a mis documentos oficiales.
                  </p>
                </div>
              </div>
            </a-checkbox>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="border-t border-gray-200 pt-6 mt-6">
        <div class="flex items-center justify-end gap-3">
          <a-button @click="$emit('close')" class="h-10 px-6 font-medium">
            Cancelar
          </a-button>
          <a-button
            type="primary"
            @click="$emit('submit')"
            :disabled="!checkbox1"
            class="h-10 px-6 font-medium"
          >
            Confirmar y Guardar
          </a-button>
        </div>
      </div>
    </div>
  </a-modal>
</template>

<script setup>
import { computed } from 'vue'

const SI_LENGUA_ID = '820ddc3b-dad9-4fce-8d58-942d1c46c4ab'
const SI_PUEBLO_ID = '47de46ba-be45-41a6-a542-6f88fcd4653c'

const props = defineProps({
  visible: Boolean,
  datospersonales: Object,
  datosresidencia: Object,
  datoscolegio: Object,
  datospadre: Object,
  datosmadre: Object,
  datos_transversales: Object,
  datos_preinscripcion: Object,
  checkbox1: Boolean,
  temp_date: [String, null],
  nombrecolegiox: String,
  formState: Object,
  tipo_docs: Object,
  estados_civil: Object,
  sexos: Object,
  programas: Array,
  condiciones_lengua: Array,
  lenguas_indigenas: Array,
  opciones_pertenencia_cultural: Array,
  pueblos_indigenas: Array,
})

defineEmits(['close', 'submit', 'update:checkbox1'])

const tipos_discapacidad = [
  { value: 1, label: 'Discapacidad Motriz' },
  { value: 2, label: 'Discapacidad Visual' },
  { value: 3, label: 'Visual y Esquema Corporal' },
  { value: 4, label: 'Disminuidos Visuales' },
  { value: 5, label: 'Discapacidad Auditiva' },
  { value: 6, label: 'Autismo' },
  { value: 7, label: 'Discapacidad Mental' },
  { value: 8, label: 'Parálisis Cerebral' },
  { value: 9, label: 'Discapacidad Intelectual' },
  { value: 10, label: 'Sordoceguera' },
  { value: 13, label: 'Sindrome de Asperger' },
  { value: 14, label: 'Hemiplejia no Identificada' },
  { value: 15, label: 'Estenosis Congénita de la Válvula Aortica' },
  { value: 16, label: 'Multidiscapacidad' },
  { value: 17, label: 'Discapacidad Fisica' },
  { value: 18, label: 'Transtorno del Espectro Autista' },
  { value: 19, label: 'T. por Déficit de Atención con Hiperactividad' },
  { value: 20, label: 'T. Especifico del Aprendizaje' },
  { value: 21, label: 'T. Mentales y del Comportamiento' },
  { value: 22, label: 'Enfermedades Raras' },
  { value: 23, label: 'Talla Baja' },
  { value: 24, label: 'Talento' },
  { value: 25, label: 'Superdotación' },
  { value: 12, label: 'Otros' },
]

const modalidadLabels = {
  9: 'CEPREUNA',
  8: 'EXAMEN GENERAL',
  7: 'CONADIS',
  1: 'TITULADOS Y GRADUADOS',
  2: 'TRASLADO INTERNO',
  3: 'TRASLADO EXTERNO',
  4: 'PRIMEROS PUESTOS',
  5: 'DEPORTISTAS DESTACADOS',
  6: 'BECAS (PRONABEC)',
  12: '(PIR) VÍCTIMAS DEL TERRORISMO',
}

const modalidadLabel = computed(() => {
  return modalidadLabels[props.datos_preinscripcion.modalidad] || 'No especificado'
})

const programaLabel = computed(() => {
  const programa = props.programas?.find(p => p.value === props.datos_preinscripcion.programa)
  return programa?.label || 'No especificado'
})

const discapacidadLabel = computed(() => {
  const val = props.datos_transversales?.discapacidad
  if (val === 1) return 'Sí'
  if (val === 0) return 'No'
  return 'No especificado'
})

const tipoDiscapacidadLabel = computed(() => {
  const val = props.datos_transversales?.tipo_discapacidad
  if (!val || val === 0 || val === '0' || val === '') return 'No especificado'
  const tipo = tipos_discapacidad.find(t => t.value === val)
  return tipo?.label || 'No especificado'
})

const condicionLenguaLabel = computed(() => {
  const id = props.datos_transversales?.id_condicion_lengua
  if (!id) return 'No especificado'
  const item = props.condiciones_lengua?.find(c => c.id === id)
  return item?.descripcion || 'No especificado'
})

const lenguaIndigenaLabel = computed(() => {
  const id = props.datos_transversales?.id_lengua_indigena
  if (!id) return 'No especificado'
  const item = props.lenguas_indigenas?.find(l => l.id === id)
  return item?.descripcion || 'No especificado'
})

const pertenenciaCulturalLabel = computed(() => {
  const id = props.datos_transversales?.id_pertenencia_cultural
  if (!id) return 'No especificado'
  const item = props.opciones_pertenencia_cultural?.find(o => o.id === id)
  return item?.descripcion || 'No especificado'
})

const puebloIndigenaLabel = computed(() => {
  const id = props.datos_transversales?.id_pueblo_indigena
  if (!id) return 'No especificado'
  const item = props.pueblos_indigenas?.find(p => p.id === id)
  return item?.descripcion || 'No especificado'
})
</script>

<style scoped>
:deep(.ant-btn-primary) {
  background-color: #2563eb !important;
  border-color: #2563eb !important;
}

:deep(.ant-btn-primary:hover) {
  background-color: #1d4ed8 !important;
  border-color: #1d4ed8 !important;
}

:deep(.ant-btn-primary:disabled) {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
