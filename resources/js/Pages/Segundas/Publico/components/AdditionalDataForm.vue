<template>
  <div class="bg-gray-50 px-3 py-4 sm:px-4 md:p-6">

    <div class="max-w-6xl mx-auto">
      <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Información adicional</h1>
            <p class="text-gray-600 mt-1">Complete todos los campos requeridos (*)</p>
          </div>
          <div class="hidden md:block">
            <span class="text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
              Paso 2 de 3
            </span>
          </div>
        </div>
      </div>
     </div>


    <div class="max-w-6xl mx-auto">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
        <a-form
          ref="formDatosTransversales"
          :model="datos_transversales"
          layout="vertical"
        >

          <section class="mb-10">
            <a-row :gutter="[16, 16]">
              <a-col :xs="24" :md="8">
                <a-form-item
                  label="¿Tiene discapacidad?"
                  name="discapacidad"
                  :rules="[{ required: true, message: 'Campo requerido' }]"
                >
                  <a-select
                    v-model:value="datos_transversales.discapacidad"
                    placeholder="Seleccionar"
                    size="large"
                  >
                    <a-select-option :value="1">Sí</a-select-option>
                    <a-select-option :value="0">No</a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>

              <a-col :xs="24" :md="16">
                <a-form-item
                  label="Tipo de discapacidad"
                  name="tipo_discapacidad"
                  :rules="
                    datos_transversales.discapacidad === 1
                      ? [{ required: true, message: 'Seleccione tipo de discapacidad' }]
                      : []
                  "
                >
                  <a-select
                    v-model:value="datos_transversales.tipo_discapacidad"
                    :options="tipos_discapacidad"
                    placeholder="Seleccionar"
                    size="large"
                    :disabled="datos_transversales.discapacidad !== 1"
                  />
                </a-form-item>
              </a-col>
            </a-row>
          </section>

          <section class="mb-10">
            <a-row :gutter="[16, 16]">
              <a-col :xs="24" :md="8">
                <a-form-item
                  label="¿Se identifica con alguna lengua indígena?"
                  name="id_condicion_lengua"
                  :rules="[{ required: true, message: 'Campo requerido' }]"
                >
                  <a-select
                    v-model:value="datos_transversales.id_condicion_lengua"
                    :options="condiciones_lengua"
                    :field-names="{ value: 'id', label: 'descripcion' }"
                    placeholder="Seleccionar"
                    size="large"
                  />
                </a-form-item>
              </a-col>

              <a-col :xs="24" :md="16">
                <a-form-item
                  label="Lengua indígena"
                  name="id_lengua_indigena"
                  :rules="
                    datos_transversales.id_condicion_lengua &&
                    datos_transversales.id_condicion_lengua === SI_LENGUA_ID
                      ? [{ required: true, message: 'Seleccione lengua indígena' }]
                      : []
                  "
                >
                  <a-select
                    v-model:value="datos_transversales.id_lengua_indigena"
                    :options="lenguas_indigenas"
                    :field-names="{ value: 'id', label: 'descripcion' }"
                    placeholder="Seleccionar"
                    size="large"
                    :disabled="
                      datos_transversales.id_condicion_lengua !== SI_LENGUA_ID
                    "
                  />
                </a-form-item>
              </a-col>
            </a-row>
          </section>

          <section>
            <a-row :gutter="[16, 16]">
              <a-col :xs="24" :md="8">
                <a-form-item
                  label="¿Pertenece a algún pueblo indígena?"
                  name="id_pertenencia_cultural"
                  :rules="[{ required: true, message: 'Campo requerido' }]"
                >
                  <a-select
                    v-model:value="datos_transversales.id_pertenencia_cultural"
                    :options="opciones_pertenencia_cultural"
                    :field-names="{ value: 'id', label: 'descripcion' }"
                    placeholder="Seleccionar"
                    size="large"
                  />
                </a-form-item>
              </a-col>

              <a-col :xs="24" :md="16">
                <a-form-item
                  label="Pueblo indígena"
                  name="id_pueblo_indigena"
                  :rules="
                    datos_transversales.id_pertenencia_cultural &&
                    datos_transversales.id_pertenencia_cultural === SI_PUEBLO_ID
                      ? [{ required: true, message: 'Seleccione pueblo indígena' }]
                      : []
                  "
                >
                  <a-select
                    v-model:value="datos_transversales.id_pueblo_indigena"
                    :options="pueblos_indigenas"
                    :field-names="{ value: 'id', label: 'descripcion' }"
                    placeholder="Seleccionar"
                    size="large"
                    :disabled="
                      datos_transversales.id_pertenencia_cultural !== SI_PUEBLO_ID
                    "
                  />
                </a-form-item>
              </a-col>
            </a-row>
          </section>

        </a-form>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, onMounted, nextTick } from 'vue'


const props = defineProps({
  datos_transversales: Object,
  setFormDatosTransversales: Function,
  condiciones_lengua: Array,
  lenguas_indigenas: Array,
  opciones_pertenencia_cultural: Array,
  pueblos_indigenas: Array,
})

const SI_LENGUA_ID = '820ddc3b-dad9-4fce-8d58-942d1c46c4ab'
const SI_PUEBLO_ID = '47de46ba-be45-41a6-a542-6f88fcd4653c'


const formDatosTransversales = ref(null)

onMounted(async () => {
  await nextTick()

  if (props.setFormDatosTransversales && formDatosTransversales.value) {
    props.setFormDatosTransversales(formDatosTransversales.value)
  }
})


const tipos_discapacidad = [
    { value: 1, label: "Discapacidad Motriz" },
    { value: 2, label: "Discapacidad Visual" },
    { value: 3, label: "Visual y Esquema Corporal" },
    { value: 4, label: "Disminuidos Visuales" },
    { value: 5, label: "Discapacidad Auditiva" },
    { value: 6, label: "Autismo" },
    { value: 7, label: "Discapacidad Mental" },
    { value: 8, label: "Parálisis Cerebral" },
    { value: 9, label: "Discapacidad Intelectual" },
    { value: 10, label: "Sordoceguera" },
    { value: 11, label: "No Cuenta con Información" },
    { value: 12, label: "Otros" },
    { value: 13, label: "Sindrome de Asperger" },
    { value: 14, label: "Hemiplejia no Identificada" },
    { value: 15, label: "Estenosis Congénita de la Válvula Aortica" },
    { value: 16, label: "Multidiscapacidad" },
    { value: 17, label: "Discapacidad Fisica" },
    { value: 18, label: "Transtorno del Espectro Autista" },
    { value: 19, label: "T. por Déficit de Atención con Hiperactividad" },
    { value: 20, label: "T. Especifico del Aprendizaje" },
    { value: 21, label: "T. Mentales y del Comportamiento" },
    { value: 22, label: "Enfermedades Raras" },
    { value: 23, label: "Talla Baja" },
    { value: 24, label: "Talento" },
    { value: 25, label: "Superdotación" }
];
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
</style>
