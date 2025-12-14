<template>
<Head title="Participantes"/>    

<div class="p-4" style="width:100%; background:white; border-radius:8px;">

    <!-- BOTONES -->
    <div class="flex justify-between mb-2">
        <div class="flex gap-2">
            <label
                for="fileInput"
                class="inline-block bg-[var(--primary-color)] text-white px-4 py-1 rounded-md cursor-pointer"
                style="height: 32px;"
            >
                Seleccionar archivo
            </label>
            <input id="fileInput" type="file" @change="handleFileUpload" style="display:none;" />
        </div>

        <div class="flex gap-2">
            <a-button type="primary" @click="abrirModalNuevo">Nuevo</a-button>
            <a-button @click="descargarTemplate" style="background: green; color: white;">
                Descargar ejemplo
            </a-button>
            <a-input v-model:value="buscar" placeholder="Buscar" style="max-width:230px">
                <template #prefix><search-outlined /></template>
            </a-input>
        </div>
    </div>

    <!-- MODAL EXCEL -->
    <a-modal v-model:open="verParticipantes" title="Participantes del proceso" width="94%">
        <a-table
            :dataSource="excelData"
            :columns="columnsExcel"
            rowKey="dni"
            size="small"
            :pagination="false"
        >
            <template #bodyCell="{ column, index }">
                <template v-if="column.dataIndex === 'nro'">
                    {{ index + 1 }}
                </template>
            </template>
        </a-table>

        <template #footer>
            <a-button type="primary" @click="subirResultados">
                Subir
            </a-button>
        </template>
    </a-modal>

    <!-- TABLA PRINCIPAL -->
    <a-table
        :dataSource="participantes"
        :columns="columns"
        size="small"
        :pagination="false"
        :scroll="{ y: 'calc(100vh - 310px)' }"
    >
        <template #bodyCell="{ column, index, record }">
            <template v-if="column.dataIndex === 'nro'">
                {{ index + 1 }}
            </template>

            <template v-if="column.dataIndex === 'acciones'">
                <a-space>
                    <a-button type="text" @click="abrirModalEditar(record)">Edit</a-button>
                    <a-popconfirm title="¿Eliminar?" @confirm="eliminarParticipante(record)">
                        <a-button type="text" danger>Delete</a-button>
                    </a-popconfirm>
                </a-space>
            </template>
        </template>
    </a-table>

    <!-- MODAL CRUD -->
    <a-modal v-model:open="showModal" :title="modalTitle" @ok="guardarParticipante">
        <a-form layout="vertical">
            <a-form-item label="DNI"><a-input v-model:value="form.dni" /></a-form-item>
            <a-form-item label="Ap. Paterno"><a-input v-model:value="form.paterno" /></a-form-item>
            <a-form-item label="Ap. Materno"><a-input v-model:value="form.materno" /></a-form-item>
            <a-form-item label="Nombres"><a-input v-model:value="form.nombres" /></a-form-item>
            <a-form-item label="Unidad"><a-input v-model:value="form.unidad" /></a-form-item>
            <a-form-item label="Cod Examen"><a-input v-model:value="form.cod_examen" /></a-form-item>
        </a-form>
    </a-modal>

</div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, reactive, watch, defineProps } from 'vue'
import * as XLSX from 'xlsx'
import axios from 'axios'
import { message } from 'ant-design-vue'
import { SearchOutlined } from '@ant-design/icons-vue'

const props = defineProps(['proceso'])

const participantes = ref([])
const excelData = ref([])
const buscar = ref('')
const verParticipantes = ref(false)

const showModal = ref(false)
const modalTitle = ref('Nuevo Participante')

const form = reactive({
    id: null,
    dni: '',
    paterno: '',
    materno: '',
    nombres: '',
    unidad: '',
    cod_examen: ''
})

const columnsExcel = [
    { title: 'N°', dataIndex: 'nro', width: 50 },
    { title: 'DNI', dataIndex: 'dni' },
    { title: 'Ap. Paterno', dataIndex: 'paterno' },
    { title: 'Ap. Materno', dataIndex: 'materno' },
    { title: 'Nombres', dataIndex: 'nombres' },
    { title: 'Unidad', dataIndex: 'unidad' },
    { title: 'Cod Examen', dataIndex: 'cod_examen' }
]

const columns = [
    { title: 'N°', dataIndex: 'nro', width: 50 },
    { title: 'DNI', dataIndex: 'dni' },
    { title: 'Ap. Paterno', dataIndex: 'paterno' },
    { title: 'Ap. Materno', dataIndex: 'materno' },
    { title: 'Nombres', dataIndex: 'nombres' },
    { title: 'Unidad', dataIndex: 'unidad' },
    { title: 'Cod Examen', dataIndex: 'cod_examen' },
    { title: 'Acciones', dataIndex: 'acciones', width: 150 }
]

/* ================= CRUD ================= */

const getParticipantes = async () => {
    const res = await axios.post('/get-participantes-externo', {
        term: buscar.value,
        proceso: props.proceso
    })
    participantes.value = res.data.datos.data
}

watch(buscar, () => {
    clearTimeout(window.t)
    window.t = setTimeout(getParticipantes, 300)
})

getParticipantes()

const abrirModalNuevo = () => {
    modalTitle.value = 'Nuevo Participante'
    Object.assign(form, {
        id: null, dni:'', paterno:'', materno:'', nombres:'', unidad:'', cod_examen:''
    })
    showModal.value = true
}

const abrirModalEditar = (p) => {
    modalTitle.value = 'Editar Participante'
    Object.assign(form, p)
    showModal.value = true
}

const guardarParticipante = async () => {
    form.id_proceso = props.proceso
    if (form.id) {
        await axios.put(`/participantes/${form.id}`, form)
    } else {
        await axios.post('/participantes', form)
    }
    message.success('Guardado')
    showModal.value = false
    getParticipantes()
}

const eliminarParticipante = async (p) => {
    await axios.delete(`/api/participantes/${p.id}`)
    message.success('Eliminado')
    getParticipantes()
}

/* ================= EXCEL ================= */

const handleFileUpload = (e) => {
    const file = e.target.files[0]
    const reader = new FileReader()

    reader.onload = (ev) => {
        const wb = XLSX.read(new Uint8Array(ev.target.result), { type: 'array' })
        const ws = wb.Sheets[wb.SheetNames[0]]
        const data = XLSX.utils.sheet_to_json(ws, { header: 1 })

        const headers = data[0]

        excelData.value = data.slice(1).map(row => {
            let obj = {}
            headers.forEach((h, i) => {
                const key = h
                    .toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .replace(/ /g, '_')
                obj[key] = row[i]
            })
            return obj
        })

        verParticipantes.value = true
    }

    reader.readAsArrayBuffer(file)
}

const subirResultados = async () => {
    await axios.post('/subir-participantes-simulacro', {
        data: excelData.value,
        proceso: props.proceso
    })
    message.success('Subido')
    verParticipantes.value = false
    getParticipantes()
}

const descargarTemplate = async () => {
    const res = await axios.get('/descargar-template-participantes-simulacro', { responseType: 'blob' })
    const url = URL.createObjectURL(new Blob([res.data]))
    const a = document.createElement('a')
    a.href = url
    a.download = 'template.xlsx'
    a.click()
    URL.revokeObjectURL(url)
}
</script>
