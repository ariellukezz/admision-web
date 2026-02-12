<template>
<Head title="Procesos"/>
<AuthenticatedLayout>
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 md:p-6" style="height: calc(100vh - 100px);">

  <div class="flex flex-col lg:flex-row justify-between mb-6 gap-4">
    <div>
      <a-button
        type="primary"
        style="background: #476175; border: none; border-radius: 5px;"
        @click="showModalProceso"
        class="w-full lg:w-auto"
      >
        Nuevo Proceso
      </a-button>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
      <a-select
        ref="Nivel"
        :options="niveles"
        v-model:value="nivel"
        placeholder="Nivel"
        class="min-w-[140px]"
      />

      <a-input-search
        v-model:value="buscar"
        placeholder="Buscar procesos..."
        class="max-w-full"
        @search="getProcesos"
      >
        <template #prefix>
          <sin-icono/>
        </template>
      </a-input-search>
    </div>
  </div>

  <div class="overflow-x-auto">
    <a-table
      :columns="columnsProcesos"
      :data-source="procesos"
      :pagination="{ pageSize: 50 }"
      size="small"
      :scroll="{ x: 'max-content', y:'calc(100vh - 280px)' }"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.dataIndex === 'modalidad'">
          <a-tag
            :color="{
              'CEPREUNA': 'cyan',
              'GENERAL': 'orange',
              'EXTRAORDINARIO': 'pink'
            }[record.modalidad]"
          >
            {{ record.modalidad }}
          </a-tag>
        </template>

        <template v-if="column.dataIndex === 'estado'">
          <a-tag :color="record.estado == 1 ? 'green' : 'red'">
            {{ record.estado == 1 ? 'VIGENTE' : 'NO VIGENTE' }}
          </a-tag>
        </template>

        <template v-if="column.dataIndex === 'acciones'">
          <a-space size="small" class="flex flex-wrap">
            <a-tooltip title="Ver preinscripción">
              <a-button
                @click="irLink(record)"
                size="small"
                type="text"
                class="text-green-600 hover:text-green-800"
              >
                <template #icon>
                  <link-outlined />
                </template>
              </a-button>
            </a-tooltip>

            <a-tooltip title="Editar">
              <a-button
                @click="abrirEditar(record)"
                size="small"
                type="text"
                class="text-blue-600 hover:text-blue-800"
              >
                <template #icon>
                  <form-outlined />
                </template>
              </a-button>
            </a-tooltip>

            <a-tooltip title="Eliminar">
              <a-button
                @click="eliminar(record)"
                size="small"
                type="text"
                danger
              >
                <template #icon>
                  <delete-outlined />
                </template>
              </a-button>
            </a-tooltip>
          </a-space>
        </template>
      </template>
    </a-table>
  </div>

</div>

<a-modal
  v-model:open="visible"
  centered
  :title="proceso.id ? 'Editar Proceso' : 'Nuevo Proceso'"
  width="90%"
  :style="{ maxWidth: '900px' }"
  :footer="null"
>
  <a-form
    ref="formProceso"
    name="proceso"
    :model="proceso"
    :rules="formRules"
    layout="vertical"
    @finish="guardar"
  >
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <a-form-item
        label="Estado"
        name="estado"
        class="mb-0"
      >
        <a-switch
          v-model:checked="proceso.estado"
          checked-children="Activo"
          un-checked-children="Inactivo"
        />
      </a-form-item>

      <a-form-item
        label="Ciclo"
        name="ciclo"
        :rules="[{ required: true, message: 'Seleccione el ciclo' }]"
        class="mb-0"
      >
        <a-select
          v-model:value="proceso.ciclo"
          :options="ciclos"
          placeholder="Seleccionar ciclo"
          allow-clear
        />
      </a-form-item>

      <a-form-item
        label="Nivel"
        name="nivel"
        :rules="[{ required: true, message: 'Seleccione el nivel' }]"
        class="mb-0"
      >
        <a-select
          v-model:value="proceso.nivel"
          :options="niveles"
          placeholder="Seleccionar nivel"
          allow-clear
        />
      </a-form-item>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div class="space-y-4">
        <a-form-item
          label="Nombre"
          name="nombre"
          :rules="[{ required: true, message: 'Ingrese el nombre del proceso' }]"
        >
          <a-input
            v-model:value="proceso.nombre"
            placeholder="Nombre del proceso"
            allow-clear
          >
            <template #prefix>
              <sin-icono/>
            </template>
          </a-input>
        </a-form-item>

        <a-form-item label="N° Convocatoria" name="convocatoria">
          <a-input
            v-model:value="proceso.convocatoria"
            placeholder="Número de convocatoria"
            allow-clear
          />
        </a-form-item>

        <a-form-item
          label="Año"
          name="anio"
          :rules="[{ required: true, message: 'Ingrese el año' }]"
        >
          <a-input-number
            v-model:value="proceso.anio"
            :min="2000"
            :max="2100"
            class="w-full"
            placeholder="Año"
          />
        </a-form-item>

        <a-form-item
          label="Slug"
          name="slug"
          :rules="[{ required: true, message: 'Ingrese el slug' }]"
        >
          <a-input
            v-model:value="proceso.slug"
            placeholder="slug-proceso"
            allow-clear
          />
        </a-form-item>
      </div>

      <div class="space-y-4">
        <a-form-item
          label="Sede"
          name="sede"
          :rules="[{ required: true, message: 'Seleccione la sede' }]"
        >
          <a-select
            v-model:value="proceso.sede"
            :options="sedes"
            value="value"
            label="label"
            placeholder="Seleccionar sede"
            allow-clear
          />
        </a-form-item>

        <a-form-item
          label="Tipo de estudio"
          name="tipo"
          :rules="[{ required: true, message: 'Seleccione el tipo de estudio' }]"
        >
          <a-select
            v-model:value="proceso.tipo"
            :options="tipoProcesos"
            placeholder="Seleccionar tipo"
            allow-clear
          />
        </a-form-item>

        <a-form-item
          label="Modalidad de examen"
          name="modalidad"
          :rules="[{ required: true, message: 'Seleccione la modalidad' }]"
        >
          <a-select
            v-model:value="proceso.modalidad"
            :options="modalidades"
            placeholder="Seleccionar modalidad"
            allow-clear
          />
        </a-form-item>

        <a-form-item
          label="Fecha de examen"
          name="fec_examen"
          :rules="[{ required: true, message: 'Ingrese la fecha de examen' }]"
        >
          <a-input
            v-model:value="proceso.fec_examen"
            placeholder="Fecha de examen"
            allow-clear
          />
        </a-form-item>
      </div>

      <div class="space-y-4">
        <a-form-item label="URL de preinscripción">
          <a-input
            :value="baseUrl + '/' + (proceso.slug || 'slug') + '/preinscripcion'"
            disabled
            class="bg-gray-50"
          />
        </a-form-item>

        <a-form-item
          label="Código de proceso"
          name="cod_proceso"
          :rules="[{ required: true, message: 'Ingrese el código del proceso' }]"
        >
          <a-input
            v-model:value="proceso.cod_proceso"
            placeholder="Código del proceso"
            allow-clear
          />
        </a-form-item>

        <a-form-item label="Reglamento" name="id_reglamento">
          <a-select
            v-model:value="proceso.id_reglamento"
            :options="reglamentos"
            placeholder="Seleccionar reglamento"
            allow-clear
            show-search
            :filter-option="filterOption"
          />
        </a-form-item>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
      <a-form-item label="Fecha de inicio" name="f_inicio">
        <a-date-picker
          v-model:value="proceso.f_inicio"
          format="DD/MM/YYYY"
          placeholder="Fecha inicio"
          class="w-full"
        />
      </a-form-item>

      <a-form-item label="Fecha de fin" name="f_fin">
        <a-date-picker
          v-model:value="proceso.f_fin"
          format="DD/MM/YYYY"
          placeholder="Fecha fin"
          class="w-full"
        />
      </a-form-item>

      <a-form-item
        label="Día 1"
        name="dia_1"
        :rules="[{ required: true, message: 'Seleccione el día 1' }]"
      >
        <a-date-picker
          v-model:value="proceso.dia_1"
          format="DD/MM/YYYY"
          placeholder="Día 1"
          class="w-full"
        />
      </a-form-item>

      <a-form-item label="Día 2" name="dia_2">
        <a-date-picker
          v-model:value="proceso.dia_2"
          format="DD/MM/YYYY"
          placeholder="Día 2"
          class="w-full"
        />
      </a-form-item>
    </div>

    <div class="mt-6">
      <a-form-item label="Observaciones" name="observacion">
        <a-textarea
          v-model:value="proceso.observacion"
          placeholder="Ingrese observaciones adicionales..."
          :rows="3"
          show-count
          :maxlength="500"
        />
      </a-form-item>
    </div>

    <div class="flex justify-end gap-3 pt-4 border-t">
      <a-button @click="cancelar">
        Cancelar
      </a-button>
      <a-button
        type="primary"
        html-type="submit"
        style="background: #476175; border: none;"
        :loading="guardando"
      >
        {{ proceso.id ? 'Actualizar' : 'Guardar' }}
      </a-button>
    </div>
  </a-form>
</a-modal>

</AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { watch, ref, reactive } from 'vue';
import {
  FormOutlined,
  LinkOutlined,
  DeleteOutlined
} from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';
import dayjs from 'dayjs';

const baseUrl = window.location.origin;

const nivel = ref(null);
const buscar = ref("");
const sedes = ref([]);
const tipoProcesos = ref([]);
const procesos = ref([]);
const visible = ref(false);
const modalidades = ref([]);
const formProceso = ref(null);
const reglamentos = ref([]);
const guardando = ref(false);

const proceso = reactive({
  id: null,
  nombre: "",
  convocatoria: "",
  slug: "",
  sede: null,
  tipo: null,
  ciclo: null,
  modalidad: null,
  fec_examen: "",
  anio: new Date().getFullYear(),
  estado: true,
  f_inicio: null,
  f_fin: null,
  dia_1: null,
  dia_2: null,
  nivel: null,
  cod_proceso: "",
  id_reglamento: null,
  observacion: ""
});

const columnsProcesos = [
  { title: 'Nombre', dataIndex: 'nombre', key: 'nombre', ellipsis: true },
  { title: 'Sede', dataIndex: 'sede', key: 'sede', align: 'center', responsive: ['md']},
  { title: 'Tipo', dataIndex: 'tipo', key: 'tipo', align: 'center', width: '120px', responsive: ['lg']},
  { title: 'Modalidad', dataIndex: 'modalidad', key: 'modalidad', align: 'center', width: '140px'},
  { title: 'Año', dataIndex: 'anio', key: 'anio', align: 'center', width: '80px'},
  { title: 'Estado', dataIndex: 'estado', key: 'estado', align: 'center', width: '100px'},
  { title: 'Acciones', dataIndex: 'acciones', key: 'acciones', align: 'center', width: '120px', fixed: 'right'},
];

const niveles = ref([
  { value: 1, label: "Pre grado" },
  { value: 2, label: "Segunda especialidad" },
]);

const ciclos = ref([
  { value: "1", label: "I" },
  { value: "2", label: "II" },
]);

const showModalProceso = () => {
  resetProceso();
  visible.value = true;
};

const abrirEditar = (item) => {
  Object.assign(proceso, {
    id: item.id,
    nombre: item.nombre,
    slug: item.slug,
    sede: item.id_sede,
    convocatoria: item.convocatoria,
    nivel: item.nivel,
    ciclo: item.ciclo,
    fec_examen: item.fecha_examen,
    f_inicio: item.fec_inicio ? dayjs(item.fec_inicio) : null,
    f_fin: item.fec_fin ? dayjs(item.fec_fin) : null,
    dia_1: item.fec_1 ? dayjs(item.fec_1) : null,
    dia_2: item.fec_2 ? dayjs(item.fec_2) : null,
    tipo: item.id_tipo,
    id_reglamento: item.id_reglamento,
    cod_proceso: item.codigo_proceso,
    observacion: item.observacion,
    modalidad: item.id_modalidad,
    anio: item.anio,
    estado: item.estado == 1
  });
  visible.value = true;
};

const getProcesos = async () => {
  try {
    const res = await axios.post("/admin/procesos/get-procesos", {
      term: buscar.value,
      nivel: nivel.value
    });
    procesos.value = res.data.datos.data;
  } catch (error) {
    console.error("Error al obtener procesos:", error);
  }
};

const getSedes = async () => {
  try {
    const res = await axios.post("get-sedes");
    sedes.value = res.data.datos.data;
  } catch (error) {
    console.error("Error al obtener sedes:", error);
  }
};

const getTipos = async () => {
  try {
    const res = await axios.get("procesos/get-tipos");
    tipoProcesos.value = res.data.datos;
  } catch (error) {
    console.error("Error al obtener tipos:", error);
  }
};

const getModalidades = async () => {
  try {
    const res = await axios.get("procesos/get-modalidades");
    modalidades.value = res.data.datos
  } catch (error) {
    console.error("Error al obtener modalidades:", error);
  }
};

const getReglamentos = async () => {
  try {
    const res = await axios.get("get-select-reglamentos");
    reglamentos.value = res.data.datos
  } catch (error) {
    console.error("Error al obtener reglamentos:", error);
  }
};

const guardar = async () => {
  try {
    guardando.value = true;
    await formProceso.value.validate();
    const response = await axios.post("save-proceso", proceso);
    notificacion('success', response.data.titulo, response.data.mensaje);
    getProcesos();
    visible.value = false;
  } catch (error) {
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      Object.keys(errors).forEach(key => {
        notificacion('error', 'Error', errors[key][0]);
      });
    }
  } finally {
    guardando.value = false;
  }
};

const eliminar = (item) => {
  if (confirm(`¿Está seguro de eliminar el proceso "${item.nombre}"?`)) {
    axios.get("eliminar-proceso/" + item.id).then((result) => {
      getProcesos();
      notificacion('warning', 'PROCESO ELIMINADO', result.data.mensaje);
    });
  }
};

const notificacion = (type, titulo, mensaje) => {
  notification[type]({
    message: titulo,
    description: mensaje,
    placement: 'topRight'
  });
};

const cancelar = () => {
  visible.value = false;
};

const irLink = (record) => {
  window.open(baseUrl + "/" + record.slug + '/preinscripcion', '_blank');
};

const resetProceso = () => {
  Object.keys(proceso).forEach(key => {
    if (key === 'anio') {
      proceso[key] = new Date().getFullYear();
    } else if (key === 'estado') {
      proceso[key] = true;
    } else {
      proceso[key] = null;
    }
  });
  proceso.nombre = "";
  proceso.convocatoria = "";
  proceso.slug = "";
  proceso.fec_examen = "";
  proceso.cod_proceso = "";
  proceso.observacion = "";
};

const filterOption = (input, option) => {
  return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

let timeoutId;
watch(buscar, (newValue) => {
  clearTimeout(timeoutId);
  timeoutId = setTimeout(() => {
    getProcesos();
  }, 500);
});

watch(nivel, () => {
  getProcesos();
});

getProcesos();
getSedes();
getTipos();
getModalidades();
getReglamentos();
</script>
