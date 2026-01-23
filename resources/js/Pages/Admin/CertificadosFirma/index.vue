<template>
<Head title="Credenciales de Firma"/>
<AuthenticatedLayout>
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
    <!-- Buscador y Botón -->
    <row class="flex justify-between mb-4">
        <div class="mr-3">
            <a-button type="primary" style="border-radius: 5px; background: #476175" @click="showModal">
                <template #icon><plus-outlined /></template>
                Nueva Credencial
            </a-button>
        </div>
        <div class="flex justify-between" style="position: relative;">
            <a-input type="text" placeholder="Buscar por DNI o Usuario"
                v-model:value="buscar"
                style="max-width: 300px; padding-left: 30px;"
                @pressEnter="getCredenciales" />
            <div class="mr-2" style="position: absolute; left: 8px; top: 3px;">
                <search-outlined />
            </div>
        </div>
    </row>

    <!-- Tabla -->
    <a-table
        :columns="columns"
        :data-source="credenciales"
        :pagination="false"
        size="small"
        :loading="loading"
    >
        <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'dni'">
                <a-tag color="blue">{{ record.dni }}</a-tag>
            </template>

            <template v-if="column.dataIndex === 'usuario'">
                <strong>{{ record.usuario }}</strong><br>
                <small class="text-gray-500">{{ record.email }}</small>
            </template>

            <template v-if="column.dataIndex === 'departamento'">
                {{ record.departamento }}
            </template>

            <template v-if="column.dataIndex === 'activo'">
                <a-tag :color="record.activo ? 'green' : 'red'">
                    {{ record.activo ? 'Activo' : 'Inactivo' }}
                </a-tag>
            </template>

            <template v-if="column.dataIndex === 'acciones'">
                <a-button type="text" @click="editar(record)"
                    style="color: #52c41a;" size="small">
                    <template #icon><edit-outlined /></template>
                </a-button>
                <a-button type="text" @click="eliminar(record)"
                    style="color: #ff4d4f;" size="small">
                    <template #icon><delete-outlined /></template>
                </a-button>
            </template>
        </template>
    </a-table>

    <!-- Paginación -->
    <div class="mt-4">
        <a-pagination
            v-model:current="pagina"
            :page-size="50"
            :total="total"
            @change="getCredenciales"
        />
    </div>
</div>

<!-- Modal -->
<a-modal v-model:visible="modalVisible" :title="credencialActual.id ? 'Editar Credencial' : 'Nueva Credencial'">
    <a-form :model="credencialActual" layout="vertical">
        <a-form-item label="DNI" required>
            <a-input v-model:value="credencialActual.dni"
                :disabled="!!credencialActual.id"
                placeholder="Ingrese DNI" />
        </a-form-item>

        <a-form-item label="Usuario" required>
            <a-input v-model:value="credencialActual.usuario"
                placeholder="Nombre completo" />
        </a-form-item>

        <a-form-item label="Departamento" required>
            <a-select v-model:value="credencialActual.departamento"
                placeholder="Seleccione departamento">
                <a-select-option value="ADMINISTRACIÓN">ADMINISTRACIÓN</a-select-option>
                <a-select-option value="ACADÉMICO">ACADÉMICO</a-select-option>
                <a-select-option value="TI">TECNOLOGÍAS DE INFORMACIÓN</a-select-option>
            </a-select>
        </a-form-item>

        <a-form-item label="Email" required>
            <a-input v-model:value="credencialActual.email"
                placeholder="correo@unap.edu.pe" />
        </a-form-item>

        <a-form-item label="Contraseña .p12" required>
            <a-input-password v-model:value="credencialActual.password_p12"
                placeholder="Mínimo 6 caracteres" />
        </a-form-item>
    </a-form>

    <template #footer>
        <a-button @click="modalVisible = false">Cancelar</a-button>
        <a-button type="primary" @click="guardar">
            {{ credencialActual.id ? 'Actualizar' : 'Guardar' }}
        </a-button>
    </template>
</a-modal>
</AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, reactive } from 'vue';
import { SearchOutlined, PlusOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const buscar = ref('');
const pagina = ref(1);
const total = ref(0);
const loading = ref(false);
const modalVisible = ref(false);

const credenciales = ref([]);
const credencialActual = reactive({
    id: null,
    dni: '',
    usuario: '',
    departamento: '',
    email: '',
    password_p12: '',
    activo: true
});

const columns = [
    { title: 'DNI', dataIndex: 'dni', width: 100 },
    { title: 'Usuario / Email', dataIndex: 'usuario' },
    { title: 'Departamento', dataIndex: 'departamento', width: 150 },
    { title: 'Estado', dataIndex: 'activo', width: 100, align: 'center' },
    { title: 'Acciones', dataIndex: 'acciones', width: 100, align: 'center' },
];

const getCredenciales = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/admin/certificados', {
            params: {
                page: pagina.value,
                search: buscar.value
            }
        });

        credenciales.value = response.data.data.data;
        total.value = response.data.data.total;
    } catch (error) {
        notification.error({
            message: 'Error',
            description: 'No se pudieron cargar las credenciales'
        });
    } finally {
        loading.value = false;
    }
};

const showModal = () => {
    Object.assign(credencialActual, {
        id: null,
        dni: '',
        usuario: '',
        departamento: '',
        email: '',
        password_p12: '',
        activo: true
    });
    modalVisible.value = true;
};

const editar = (record) => {
    Object.assign(credencialActual, record);
    credencialActual.password_p12 = ''; // Por seguridad no mostramos
    modalVisible.value = true;
};

const guardar = async () => {
    try {
        const url = credencialActual.id
            ? `/admin/certificados/${credencialActual.id}`
            : '/admin/certificados';

        const method = credencialActual.id ? 'put' : 'post';

        const response = await axios[method](url, credencialActual);

        if (response.data.success) {
            notification.success({
                message: 'Éxito',
                description: response.data.message
            });

            getCredenciales();
            modalVisible.value = false;
        }
    } catch (error) {
        notification.error({
            message: 'Error',
            description: error.response?.data?.message || 'Error guardando'
        });
    }
};

const eliminar = async (record) => {
    try {
        await axios.delete(`/admin/certificados/${record.id}`);
        notification.success({
            message: 'Éxito',
            description: 'Credencial eliminada'
        });
        getCredenciales();
    } catch (error) {
        notification.error({
            message: 'Error',
            description: 'Error eliminando'
        });
    }
};

getCredenciales();
</script>
