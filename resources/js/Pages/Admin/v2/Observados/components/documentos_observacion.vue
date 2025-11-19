<template>
  <div>
    <a-button 
      type="primary" 
      @click="showManager = true"
      class="mb-4"
    >
      Gestionar Documentos
    </a-button>

    <a-modal 
      v-model:open="showManager" 
      title="Gestión de Documentos" 
      width="95%"
      :footer="null"
      :styles="{ maxWidth: '1400px' }"
    >
      <div class="crud-container">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full">
            <a-input
              v-model:value="searchText"
              placeholder="Buscar por nombre o tipo..."
              :style="{ width: '100%', maxWidth: '300px' }"
              size="large"
            />
            <div class="flex gap-2">
              <a-button 
                type="primary" 
                @click="openUploadModal" 
                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700"
                size="large"
              >
                Subir Documento
              </a-button>
            </div>
          </div>
        </div>

        <a-table 
          :columns="columns" 
          :data-source="filteredDocumentos"
          :loading="loading"
          :pagination="pagination"
          row-key="id"
          :scroll="{ x: 600 }"
          class="custom-table"
          size="middle"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'nombre'">
              <div class="flex items-center gap-2">
                <span class="font-medium text-gray-800 cursor-pointer hover:text-blue-600" 
                      @click="verDocumento(record)">
                  {{ record.nombre }}
                </span>
              </div>
            </template>
            <template v-if="column.key === 'tipo'">
              <a-tag 
                :color="getTipoColor(record.tipo)"
                class="font-semibold px-3 py-1 rounded-full"
              >
                {{ record.tipo.toUpperCase() }}
              </a-tag>
            </template>
            <template v-if="column.key === 'fecha_subida'">
              <div class="text-gray-600 font-medium">
                {{ formatFecha(record.fecha_subida) }}
              </div>
            </template>
            <template v-if="column.key === 'actions'">
             <a-space size="middle">
                <a-tooltip title="Ver documento">
                  <a-button 
                    type="link" 
                    size="small" 
                    @click="verDocumento(record)"
                    class="text-blue-600 hover:text-blue-800 p-0"
                  >
                    <eye-outlined />
                  </a-button>
                </a-tooltip>
                
                <a-tooltip title="Descargar">
                  <a-button 
                    type="link" 
                    size="small" 
                    @click="descargarDocumento(record)"
                    class="text-green-600 hover:text-green-800 p-0"
                  >
                    <download-outlined />
                  </a-button>
                </a-tooltip>
                
                <a-tooltip title="Eliminar">
                  <a-popconfirm
                    title="¿Estás seguro de eliminar este documento?"
                    ok-text="Sí"
                    cancel-text="No"
                    @confirm="deleteDocumento(record.id)"
                  >
                    <a-button 
                      type="link" 
                      danger 
                      size="small"
                      class="hover:text-red-700 p-0"
                    >
                      <delete-outlined />
                    </a-button>
                  </a-popconfirm>
                </a-tooltip>
              </a-space>
            </template>
          </template>
        </a-table>

        <!-- Modal para subir documento -->
        <a-modal 
          v-model:open="showUploadModal" 
          title="Subir Nuevo Documento"
          @cancel="handleCancel"
          width="90%"
          :styles="{ maxWidth: '800px' }"
          :maskClosable="false"
          :footer="null"
        >
          <a-form 
            ref="formRef"
            :model="formState"
            :rules="rules"
            layout="vertical"
            class="space-y-4"
            @submit="handleUpload"
          >
            <a-row :gutter="16">
              <a-col :xs="24" :sm="12">
                <a-form-item label="Nombre del documento" name="nombre">
                  <a-input 
                    v-model:value="formState.nombre" 
                    placeholder="Ingrese el nombre del documento"
                    size="large"
                  />
                </a-form-item>
              </a-col>
              
              <a-col :xs="24" :sm="12">
                <a-form-item label="Tipo de documento" name="tipo">
                  <a-select
                    v-model:value="formState.tipo"
                    placeholder="Seleccione el tipo de documento"
                    size="large"
                  >
                    <a-select-option value="general">General</a-select-option>
                    <a-select-option value="contrato">Contrato</a-select-option>
                    <a-select-option value="informe">Informe</a-select-option>
                    <a-select-option value="formulario">Formulario</a-select-option>
                    <a-select-option value="oficio">Oficio</a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>
            </a-row>

            <a-form-item 
              label="Seleccionar PDF" 
              name="archivo"
              :validate-status="fileError ? 'error' : ''"
              :help="fileError"
            >
              <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors">
                <input
                  type="file"
                  ref="fileInput"
                  @change="handleFileSelect"
                  accept=".pdf"
                  class="hidden"
                />
                
                <div v-if="!selectedFile" class="cursor-pointer" @click="$refs.fileInput.click()">
                  <div class="text-lg font-medium text-gray-600">Click para seleccionar PDF</div>
                  <div class="text-sm text-gray-500 mt-1">Máximo 10MB</div>
                </div>
                
                <div v-else class="text-left">
                  <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                      <file-text-outlined class="text-blue-500 text-xl" />
                      <div>
                        <div class="font-medium text-gray-800">{{ selectedFile.name }}</div>
                        <div class="text-sm text-gray-500">{{ formatFileSize(selectedFile.size) }}</div>
                      </div>
                    </div>
                    <a-button type="link" @click="removeFile" class="text-red-500">
                      <delete-outlined /> Eliminar
                    </a-button>
                  </div>
                  
                  <div class="mt-4">
                    <div class="text-sm font-medium text-gray-700 mb-2">Vista previa:</div>
                    <div class="border rounded-lg p-4 bg-gray-50 max-h-80 overflow-y-auto">
                      <iframe
                        v-if="previewUrl"
                        :src="previewUrl"
                        class="w-full h-64 border-0"
                        frameborder="0"
                      ></iframe>
                      <div v-else class="text-center text-gray-500 py-8">
                        <a-spin size="large" />
                        <div class="mt-2">Cargando vista previa...</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </a-form-item>
          </a-form>
          
          <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
            <a-button @click="handleCancel" size="large">
              Cancelar
            </a-button>
            <a-button 
              type="primary" 
              @click="handleUpload"
              :loading="submitting"
              :disabled="!selectedFile"
              size="large"
              class="bg-blue-600 hover:bg-blue-700"
            >
              <upload-outlined /> Subir Documento
            </a-button>
          </div>
        </a-modal>

        <!-- Modal para ver PDF -->
        <a-modal 
          v-model:open="showPdfModal" 
          :title="`Documento: ${currentDocument?.nombre || ''}`"
          width="95%"
          :styles="{ maxWidth: '1200px', top: '20px' }"
          :footer="null"
          :maskClosable="true"
          class="pdf-modal"
        >
          <div class="pdf-viewer-container">
            <div class="flex justify-between items-center mb-4 p-4 bg-gray-50 rounded-lg">
              <div class="font-semibold text-lg text-gray-800">
                {{ currentDocument?.nombre }} - {{ currentDocument?.tipo?.toUpperCase() }}
              </div>
              <a-space>
                <a-button 
                  @click="descargarDocumento(currentDocument)" 
                  icon="download"
                  type="primary"
                  class="bg-green-600 hover:bg-green-700"
                >
                  Descargar
                </a-button>
                <a-button @click="showPdfModal = false">
                  Cerrar
                </a-button>
              </a-space>
            </div>
            
            <div class="pdf-frame-container">
              <iframe
                v-if="pdfUrl"
                :src="pdfUrl"
                class="pdf-iframe"
                frameborder="0"
              ></iframe>
              <div v-else class="flex justify-center items-center h-96">
                <div class="text-center">
                  <a-spin size="large" />
                  <div class="mt-4 text-gray-600">Cargando documento...</div>
                </div>
              </div>
            </div>
          </div>
        </a-modal>
      </div>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { message } from 'ant-design-vue'
import axios from 'axios'
import { EyeOutlined, DownloadOutlined, DeleteOutlined, FileTextOutlined, UploadOutlined } from '@ant-design/icons-vue'

const showManager = ref(false)
const showUploadModal = ref(false)
const showPdfModal = ref(false)
const loading = ref(false)
const submitting = ref(false)
const searchText = ref('')
const documentos = ref([])
const selectedFile = ref(null)
const previewUrl = ref('')
const fileInput = ref(null)
const fileError = ref('')
const currentDocument = ref(null)
const pdfUrl = ref('')

const formRef = ref()
const formState = reactive({
  nombre: '',
  tipo: 'general',
  archivo: null
})

const rules = {
  nombre: [
    { required: true, message: 'El nombre es obligatorio' },
    { min: 3, message: 'Mínimo 3 caracteres' }
  ],
  tipo: [{ required: true, message: 'El tipo es obligatorio' }]
}

const columns = [
  { title: 'ID', dataIndex: 'id', key: 'id', width: 70,align: 'center'},
  { title: 'NOMBRE', dataIndex: 'nombre', key: 'nombre', width: 240, ellipsis: true},
  { title: 'TIPO', dataIndex: 'tipo', key: 'tipo', width: 140,align: 'center'},
  { title: 'FECHA SUBIDA', dataIndex: 'fecha_subida', key: 'fecha_subida', width: 140,align: 'center'},
  { title: 'ACCIONES', key: 'actions', width: 120, align: 'center',fixed: 'right'}
]

const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0,
  showSizeChanger: true,
  showQuickJumper: true,
  showTotal: (total, range) => `${range[0]}-${range[1]} de ${total} documentos`
})

const filteredDocumentos = computed(() => {
  if (!searchText.value) return documentos.value
  
  return documentos.value.filter(doc => 
    doc.nombre.toLowerCase().includes(searchText.value.toLowerCase()) ||
    doc.tipo.toLowerCase().includes(searchText.value.toLowerCase())
  )
})

const fetchDocumentos = async () => {
  loading.value = true
  try {
    const response = await axios.get('https://servicios-admision.unap.edu.pe/api/v1/observados/documentos')
    documentos.value = response.data.data || response.data
    pagination.total = documentos.value.length
  } catch (error) {
    message.error('Error al cargar los documentos')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const verDocumento = async (documento) => {
  currentDocument.value = documento
  showPdfModal.value = true
  
  try {
    const response = await axios.get(
      `https://servicios-admision.unap.edu.pe/api/v1/observados/documentos/${documento.id}/descargar`,
      {
        responseType: 'arraybuffer'
      }
    )

    const blob = new Blob([response.data], { 
      type: 'application/pdf'
    })
    pdfUrl.value = URL.createObjectURL(blob)
    
  } catch (error) {
    console.error('❌ Error al cargar el documento:', error)
    message.error('Error al cargar el documento')
  }
}

const descargarDocumento = async (documento) => {
  try {
    const response = await fetch(`https://servicios-admision.unap.edu.pe/api/v1/observados/documentos/${documento.id}/descargar`)
    const pdfText = await response.text()
    
    const latin1String = unescape(encodeURIComponent(pdfText))
    const byteArray = new Uint8Array(latin1String.length)
    
    for (let i = 0; i < latin1String.length; i++) {
      byteArray[i] = latin1String.charCodeAt(i) & 0xff
    }
    
    const blob = new Blob([byteArray], { type: 'application/pdf' })
    const url = URL.createObjectURL(blob)
    
    const link = document.createElement('a')
    link.href = url
    link.download = `${documento.nombre}.pdf`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    
    setTimeout(() => URL.revokeObjectURL(url), 5000)
    
    message.success('PDF descargado correctamente')
    
  } catch (error) {
    console.error('Error:', error)
    message.error('Error al descargar el PDF')
  }
}

const openUploadModal = () => {
  Object.assign(formState, {
    nombre: '',
    tipo: 'general',
    archivo: null
  })
  selectedFile.value = null
  previewUrl.value = ''
  fileError.value = ''
  showUploadModal.value = true
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  fileError.value = ''
  
  if (!file) return

  if (file.type !== 'application/pdf') {
    fileError.value = 'Solo se permiten archivos PDF'
    return
  }

  const isLt10M = file.size / 1024 / 1024 < 10
  if (!isLt10M) {
    fileError.value = 'El archivo debe ser menor a 10MB'
    return
  }

  selectedFile.value = file
  formState.archivo = file
  generatePreview(file)
}

const generatePreview = (file) => {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
  }
  
  previewUrl.value = URL.createObjectURL(file)
}

const removeFile = () => {
  selectedFile.value = null
  formState.archivo = null
  fileError.value = ''
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = ''
  }
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const handleUpload = async () => {
  try {
    await formRef.value.validate()
    
    if (!selectedFile.value) {
      fileError.value = 'Seleccione un archivo PDF'
      return
    }

    if (fileError.value) {
      return
    }

    submitting.value = true
    
    const formData = new FormData()
    formData.append('nombre', formState.nombre)
    formData.append('tipo', formState.tipo)
    formData.append('archivo', selectedFile.value)

    const response = await axios.post('https://servicios-admision.unap.edu.pe/api/v1/observados/documentos', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      timeout: 30000
    })
    
    message.success('Documento subido correctamente')
    await fetchDocumentos()
    handleCancel()
  } catch (error) {
    if (error.errorFields) {
      if (!selectedFile.value) {
        fileError.value = 'Seleccione un archivo PDF'
      }
      return
    }
    
    if (error.response?.data?.error) {
      message.error(`${error.response.data.error}`)
    } else if (error.code === 'ECONNABORTED') {
      message.error('Tiempo de espera agotado. Intente nuevamente.')
    } else {
      message.error('Error al subir el documento')
    }
    console.error('Error detallado:', error)
  } finally {
    submitting.value = false
  }
}

const deleteDocumento = async (id) => {
  try {
    await axios.delete(`https://servicios-admision.unap.edu.pe/api/v1/observados/documentos/${id}`)
    message.success('Documento eliminado correctamente')
    await fetchDocumentos()
  } catch (error) {
    message.error('Error al eliminar el documento')
    console.error(error)
  }
}

const getTipoColor = (tipo) => {
  const colors = {
    general: 'blue',
    contrato: 'green',
    informe: 'orange',
    formulario: 'purple',
    oficio: 'red'
  }
  return colors[tipo] || 'default'
}

const formatFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const handleCancel = () => {
  showUploadModal.value = false
  formRef.value?.resetFields()
  removeFile()
  fileError.value = ''
}

watch(showPdfModal, (newVal) => {
  if (!newVal && pdfUrl.value) {
    URL.revokeObjectURL(pdfUrl.value)
    pdfUrl.value = ''
    currentDocument.value = null
  }
})

watch(showManager, (newVal) => {
  if (newVal) {
    fetchDocumentos()
  }
})
</script>

<style scoped>
.crud-container {
  min-height: 500px;
}

.custom-table {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

:deep(.ant-table-thead > tr > th) {
  background-color: #f8fafc;
  font-weight: 600;
  color: #374151;
}

:deep(.ant-table-tbody > tr:hover > td) {
  background-color: #f3f4f6 !important;
}

.pdf-modal :deep(.ant-modal-body) {
  padding: 0;
}

.pdf-viewer-container {
  height: 80vh;
  display: flex;
  flex-direction: column;
}

.pdf-frame-container {
  flex: 1;
  background-color: #f5f5f5;
}

.pdf-iframe {
  width: 100%;
  height: 100%;
  border: none;
}
</style>