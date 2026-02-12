import { reactive, ref, watch, nextTick } from 'vue'
import { format } from 'date-fns'
import dayjs from 'dayjs'
import axios from 'axios'
import { message } from 'ant-design-vue'

export const usePreinscripcion = (props) => {

  const formState = reactive({ dni: '', codigo_secreto: '' })
  const codigo_aleatorio = ref(null)
  const formRef = ref()

  const datospersonales = reactive({
    id: null,
    tipo_doc: 1,
    primerapellido: '',
    segundo_apellido: '',
    nombres: '',
    estado_civil: null,
    sexo: null,
    correo: '',
    celular: '',
    fec_nacimiento: '',
    egreso: '',
    direccion: '',
    ubigeo_nacimiento: '',
    ubigeo_residencia: '',
    residencia: '',
    nacimiento: '',
  })

  const datos_transversales = reactive({
    discapacidad: null,
    tipo_discapacidad: '',
    id_postulante: 3,
    id_proceso: 1,
    id_pertenencia_cultural: null,
    id_pueblo_indigena: null,
    id_condicion_lengua: null,
    id_lengua_indigena: null,
  })

  const datos_preinscripcion = reactive({
    modalidad: null,
    programa: null,
    tipo_certificado: null,
    codigo_medico: null,
    codigo_certificado: null,
  })

  const formDatosPersonales = ref()
  const formPreinscripcion = ref()
  const formDatosTransversales = ref()

  const pagina_pre = ref(0)
  const modalcarrerasprevias = ref(false)
  const loading = ref(true)
  const open = ref(false)
  const checkbox1 = ref(false)
  const postulante_inscrito = ref(0)
  const participa = ref(0)
  const avance = ref(0)
  const avance_current = ref(null)

  const buscarResidencia = ref(null)
  const residencias = ref([])
  const buscarNacimiento = ref(null)
  const ubigeosNacimiento = ref([])

  const condiciones_lengua = ref([])
  const opciones_pertenencia_cultural = ref([])
  const lenguas_indigenas = ref([])
  const pueblos_indigenas = ref([])
  const programas = ref([])

  const tipo_docs = { 1: 'DNI', 2: 'PASAPORTE' }
  const estados_civil = { 1: 'SOLTERO', 2: 'CASADO', 3: 'VIUDO' }
  const sexos = { 1: 'MASCULINO', 2: 'FEMENINO' }

  const dniInput = (event) => { formState.dni = event.target.value.replace(/\D/g, '')}
  const nombresInput = (event) => { datospersonales.nombres = event.target.value.replace(/[^A-Za-z\s]/g, '')}
  const pimerapellidoInput = (event) => { datospersonales.primerapellido = event.target.value.replace(/[^A-Za-z]/g, '') }
  const celularInput = (event) => { datospersonales.celular = event.target.value.replace(/\D/g, '') }
  const correoInput = (event) => { datospersonales.correo = event.target.value}

  const validateFechaNacimiento = (rule, value) => {
    return new Promise((resolve, reject) => {
      if (!value) {
        reject(new Error('Por favor, selecciona tu fecha de nacimiento.'))
      } else {
        const fechaNacimiento = new Date(value)
        const fechaMinima = new Date()
        fechaMinima.setFullYear(fechaMinima.getFullYear() - 20)

        if (fechaNacimiento > fechaMinima) {
          reject(new Error('Debes tener al menos 20 años.'))
        } else {
          resolve()
        }
      }
    })
  }

  const validateCodigoSecreto = (rule, value) => {
    return new Promise((resolve, reject) => {
      if (!value) {
        reject(new Error('Por favor, ingresa el código secreto.'))
      } else if (codigo_aleatorio.value !== formState.codigo_secreto) {
        reject(new Error('El código ingresado no coincide.'))
      } else {
        resolve()
      }
    })
  }

  const validateCelular = (rule, value) => {
    return new Promise((resolve, reject) => {
      if (!value) {
        reject(new Error('Por favor, ingresa tu número de celular.'))
      } else {
        axios
          .post('/existe-celular', { celular: value, dni: formState.dni })
          .then((response) => {
            if (response.data == true) {
              reject(new Error('Este número de celular ya está registrado.'))
            } else {
              resolve()
            }
          })
          .catch((error) => {
            console.error('Error al verificar el número de celular:', error)
            reject(new Error('Error al verificar el número de celular.'))
          })
      }
    })
  }

  const validateCorreo = (rule, value) => {
    return new Promise((resolve, reject) => {
      if (!value) {
        reject(new Error('Por favor, ingresa tu correo.'))
      } else {
        axios
          .post('/existe-correo', { email: value, dni: formState.dni })
          .then((response) => {
            if (response.data == true) {
              reject(new Error('Este correo ya está registrado.'))
            } else {
              resolve()
            }
          })
          .catch((error) => {
            console.error('Error al verificar el correo:', error)
            reject(new Error('Error al verificar correo.'))
          })
      }
    })
  }

  const getCodigoAleatorio = async () => {
    let res = await axios.get('/generar-captcha')
    codigo_aleatorio.value = res.data.captcha
  }

  const getDatosPersonales = async () => {
    let res = await axios.post('/get-postulante-datos-personales2', {
      nro_doc: formState.dni,
    })
    if (res.data.datos.length > 0) {
      const data = res.data.datos[0]
      datospersonales.id = data.id
      datospersonales.primerapellido = data.primer_apellido
      datospersonales.segundo_apellido = data.segundo_apellido
      datospersonales.nombres = data.nombres
      datospersonales.estado_civil = data.estado_civil
      datospersonales.sexo = data.sexo
      datospersonales.direccion = data.direccion
      datospersonales.correo = data.correo
      datospersonales.celular = data.celular
      datospersonales.egreso = data.egreso
      datospersonales.residencia = data.residencia
      datospersonales.nacimiento = data.nacimiento

      if (data.fec_nacimiento) {
        const fechaStr = data.fec_nacimiento.trim()
        const fechaParsed = dayjs(fechaStr, 'YYYY-MM-DD', true)
        if (!fechaParsed.isValid()) {
          console.error('Fecha inválida:', fechaStr)
        } else {
          datospersonales.fec_nacimiento = fechaParsed
        }
      }

      formState.ubigeo = data.ubigeo
      datospersonales.ubigeo_nacimiento = data.ubigeo
      datospersonales.ubigeo_residencia = data.ubigeo_residencia
    }
  }

  const getUbigeosResidencia = async () => {
    axios
      .post('/get-ubigeo', { term: buscarResidencia.value })
      .then((response) => {
        residencias.value = response.data.datos.data
      })
      .catch((error) => {
        if (error.response) {
          console.error('Error de servidor:', error.response.data)
        } else if (error.request) {
          console.error('Error de red:', error.request)
        } else {
          console.error('Error de configuración:', error.message)
        }
      })
  }

  const getUbigeosNacimiento = async () => {
    axios
      .post('/get-ubigeo', { term: buscarNacimiento.value })
      .then((response) => {
        ubigeosNacimiento.value = response.data.datos.data
      })
      .catch((error) => {
        if (error.response) {
          console.error('Error de servidor:', error.response.data)
        } else if (error.request) {
          console.error('Error de red:', error.request)
        } else {
          console.error('Error de configuración:', error.message)
        }
      })
  }

  const getInformacionAdicional = async () => {

    axios
      .get('/get-identidad-cultural/'+datospersonales.id+'/'+props.procceso_seleccionado.id)
      .then((response) => {
        datos_transversales.discapacidad = response.data.discapacidad
        datos_transversales.tipo_discapacidad = response.data.tipo_discapacidad
        datos_transversales.id_condicion_lengua = response.data.id_condicion_lengua
        datos_transversales.id_lengua_indigena = response.data.id_lengua_indigena
        datos_transversales.id_pertenencia_cultural = response.data.id_pertenencia_cultural
        datos_transversales.id_pueblo_indigena = response.data.id_pueblo_indigena
      })
      .catch((error) => {
        if (error.response) {
          console.error('Error de servidor:', error.response.data)
        } else if (error.request) {
          console.error('Error de red:', error.request)
        } else {
          console.error('Error de configuración:', error.message)
        }
      })
  }

  const setFormRef = (formInstance) => {
    formRef.value = formInstance
  }

  const validateDiscapacidad = (rule, value) => {
    return new Promise((resolve, reject) => {
      if (value === null || value === undefined || value === '') {
        reject(new Error('¿Tiene discapacidad? es obligatorio'))
      } else {
        resolve()
      }
    })
  }

  const validateTipoDiscapacidad = (rule, value) => {
    return new Promise((resolve, reject) => {
      if ((datos_transversales.discapacidad === '1' || datos_transversales.discapacidad === 1) && !value) {
        reject(new Error('Tipo de discapacidad es obligatorio'))
      } else {
        resolve()
      }
    })
  }

  const validateIdCondicionLengua = (rule, value) => {
    return new Promise((resolve, reject) => {
      if (!value) {
        reject(new Error('¿Se identifica con alguna lengua indígena? es obligatorio'))
      } else {
        resolve()
      }
    })
  }

  const validateIdLenguaIndigena = (rule, value) => {
    return new Promise((resolve, reject) => {
      const noLenguaId = 'b57c729c-2c2e-4b07-9e01-d63ecf94ccc1'
      if (datos_transversales.id_condicion_lengua && datos_transversales.id_condicion_lengua !== noLenguaId && !value) {
        reject(new Error('Lengua indígena es obligatoria si se identifica con una'))
      } else {
        resolve()
      }
    })
  }

  const validateIdPertenenciaCultural = (rule, value) => {
    return new Promise((resolve, reject) => {
      if (!value) {
        reject(new Error('¿Pertenece a algún pueblo indígena? es obligatorio'))
      } else {
        resolve()
      }
    })
  }

  const validateIdPuebloIndigena = (rule, value) => {
    return new Promise((resolve, reject) => {
      const noPuebloId = '47de46ba-be45-41a6-a542-6f88fcd4653c'
      if (datos_transversales.id_pertenencia_cultural && datos_transversales.id_pertenencia_cultural !== noPuebloId && !value) {
        reject(new Error('Pueblo indígena es obligatorio si pertenece a uno'))
      } else {
        resolve()
      }
    })
  }

  const setFormDatosTransversales = (formInstance) => { formDatosTransversales.value = formInstance }
  const setFormPreinscripcion = (formInstance) => { formPreinscripcion.value = formInstance }
  const onSelectNacimiento = (value, option) => { datospersonales.ubigeo_nacimiento = option.key }
  const onSelectResidencias = (value, option) => { datospersonales.ubigeo_residencia = option.key }
  const saveDatosPersonales = async () => {
    await nextTick()

    if (!formRef.value) {
      message.error('Formulario no disponible')
      return
    }

    try {
      await formRef.value.validateFields(['ubigeo_nacimiento'])
      const values = await formRef.value.validateFields()

      let res = await axios.post('/save-postulante-segundas', {
        tipo_doc: datospersonales.tipo_doc,
        nro_doc: formState.dni,
        id: datospersonales.id,
        primer_apellido: datospersonales.primerapellido,
        segundo_apellido: datospersonales.segundo_apellido,
        nombres: datospersonales.nombres,
        egreso: datospersonales.egreso,
        correo: datospersonales.correo,
        celular: datospersonales.celular,
        sexo: datospersonales.sexo,
        estado_civil: datospersonales.estado_civil,
        fec_nacimiento: format(new Date(datospersonales.fec_nacimiento), 'yyyy-MM-dd'),
        ubigeo_nacimiento: datospersonales.ubigeo_nacimiento,
        ubigeo_residencia: datospersonales.ubigeo_residencia,
        direccion: datospersonales.direccion,
        proceso: props.procceso_seleccionado.id,
      })

      pagina_pre.value = 2
      if (res.data.estado === true) {
        message.success(res.data.mensaje)
        pagina_pre.value = 2
      }
    } catch (error) {
      console.error('Error validating form:', error)
      message.error('Por favor, completa todos los campos requeridos')
    }
  }

const saveAdditionalData = async () => {
  if (!formDatosTransversales.value || !formDatosTransversales.value.validateFields) {
    message.error('Formulario no disponible')
    return
  }

  try {
    await formDatosTransversales.value.validateFields()
    let res = await axios.post('/save-postulante-adicional', {
        id_postulante: datospersonales.id,
        id_proceso: props.procceso_seleccionado.id,
        discapacidad: datos_transversales.discapacidad,
        tipo_discapacidad: datos_transversales.tipo_discapacidad,
        id_condicion_lengua: datos_transversales.id_condicion_lengua,
        id_lengua_indigena : datos_transversales.id_lengua_indigena,
        id_pertenencia_cultural: datos_transversales.id_pertenencia_cultural,
        id_pueblo_indigena: datos_transversales.id_pueblo_indigena
    })
    if (res.data.estado === true){
        pagina_pre.value = 6
        message.success('Datos guardados correctamente')
    }
  } catch (error) {
    message.error('Por favor, completa los campos obligatorios')
  }
}

  const getProgramas = async () => {
    let res = await axios.post('/get-select-programas-proceso-segundas', {
      id_modalidad: 1,
      id_proceso: props.procceso_seleccionado.id,
    })
    programas.value = res.data.datos
  }

const validateDocuments = async () => {
  try {
    const response = await axios.get(
      '/verificar-documentos-preinscripcion/' +
        formState.dni +
        '/' +
        props.procceso_seleccionado.id
    )
    const data = response.data

    if (data.estado === true) {
      return true
    }

    if (data.missing?.length === 2) {
      message.error('Faltal el titulo, debe estar en PDF y pesar menos de 1MB.')
      return false
    }

    // if (data.missing?.includes(8)) {
    //   message.error('Falta subir foto.')
    //   return false
    // }

    if (data.missing?.includes(7)) {
      message.error('Falta subir título.')
      return false
    }
    return true

  } catch (error) {
    console.error('Error en la validación:', error)
    message.error('Error al validar documentos.')
    return false
  }
}

  const descargaReglamento = async () => {
    try {
      const response = await axios.get('/descargar-reglamento/' + props.procceso_seleccionado.id, {
        responseType: 'blob',
      })
      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', 'reglamento.pdf')
      document.body.appendChild(link)
      link.click()
    } catch (error) {
      console.error('Error al descargar el PDF', error)
    }
  }

  const getCondicionesLengua = async () => {
    const response = await axios.get('/get-condiciones-lengua-segundas')
    if (response.data) {
      condiciones_lengua.value = response.data
    }
  }

  const getOptionsPertenenciaCultural = async () => {
    const response = await axios.get('/get-pertenencia-cultural-segundas')
    if (response.data) {
      opciones_pertenencia_cultural.value = response.data
    }
  }

  const getLenguasIndigenas = async () => {
    const response = await axios.get('/get-lengua-segundas')
    if (response.data) {
      lenguas_indigenas.value = response.data
    }
  }

  const getPueblosIndigenas = async () => {
    const response = await axios.get('/get-pueblos-indigenes-segundas')
    if (response.data) {
      pueblos_indigenas.value = response.data
    }
  }

  const getSancionado = async () => {
    participa.value = 0
    try {
      let res = await axios.get(
        '/get-sancionado/' + formState.dni + '/' + props.procceso_seleccionado.id
      )
      return res.data.datos
    } catch (error) {
      console.error('Error al obtener datos de sancionado', error)
    }
  }

  const consultaInscripcion = async () => {
    postulante_inscrito.value = 0
    try {
      let res = await axios.get(
        '/participa-proceso/' + props.procceso_seleccionado.id + '/' + formState.dni
      )
      if (res.data.estado === true) {
        postulante_inscrito.value = 1
        modalcarrerasprevias.value = false
        loading.value = false
        pagina_pre.value = 7
      } else {
        participa.value = 1
        return res.data.estado
      }
    } catch (error) {
      console.error('Error al obtener datos del participante', error)
    }
  }

  const getPasoRegistrado = async () => {
    let res = await axios.get(
      '/get-paso-registrado/' + props.procceso_seleccionado.id + '/' + formState.dni
    )
    if (res.data.estado == true) {
      getDatosPersonales()
      if (res.data.datos.nro == 3) {
        // consultaInscripcion2()
      } else {
        pagina_pre.value = res.data.datos.nro + 1
      }
    } else {
      modalcarrerasprevias.value = true
      loading.value = true
      getSancionado()
    }
  }

  const getDataPrisma = async () => {
    const response = await axios.get('/get-data-prisma/' + formState.dni)
    if (response.data.estado === true) {
      const datos = response.data.datos
      formState.dni = datos.dni
      datospersonales.primerapellido = datos.paterno
      datospersonales.segundo_apellido = datos.materno
      datospersonales.nombres = datos.nombre
    }
    loading.value = false
  }

  const submit = async () => {
    let fd = new FormData()
    fd.append('dni', formState.dni)
    fd.append('modalidad', 1)
    fd.append('programa', datos_preinscripcion.programa)
    fd.append('tipo_certificado', datos_preinscripcion.tipo_certificado)
    fd.append('codigo_certificado', datos_preinscripcion.codigo_certificado)
    fd.append('codigo_medico', datos_preinscripcion.codigo_medico)
    fd.append('id_postulante', datospersonales.id)
    fd.append('id_proceso', props.procceso_seleccionado.id)

    await axios
      .post('/save-pre-inscripcion', fd)
      .then((res) => {
        message.success(res.data.mensaje)
        pagina_pre.value = 7
        postulante_inscrito.value = 1
      })
      .catch((err) => console.log(err))

    open.value = false
  }

  watch(buscarResidencia, (newValue) => {
    if (newValue && newValue.length >= 3) {
      getUbigeosResidencia()
    }
  })

  watch(buscarNacimiento, (newValue) => {
    if (newValue && newValue.length >= 3) {
      getUbigeosNacimiento()
    }
  })

  watch(
    () => formState.dni,
    (newValue) => {
      if (newValue.length == 8) {
        getPasoRegistrado()
      }
    }
  )

  watch(
  () => pagina_pre.value,
  (newValue) => {
    if (newValue === 2) {
      getCondicionesLengua()
      getOptionsPertenenciaCultural()
      getLenguasIndigenas()
      getPueblosIndigenas()
      getInformacionAdicional()
    }else{
      if(newValue === 6){
        console.log('Cargando programas...')
        getProgramas()
        // Verificar si el formulario se establece
        setTimeout(() => {
          console.log('formPreinscripcion después de montar:', formPreinscripcion.value)
        }, 100)
      }
    }

  },
  { immediate: true }
)

  getCodigoAleatorio()

  return {

    formState,
    formDatosTransversales,
    formPreinscripcion,
    codigo_aleatorio,
    formRef,
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
    avance,
    avance_current,
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
    validateDiscapacidad,
    validateTipoDiscapacidad,
    validateIdCondicionLengua,
    validateIdLenguaIndigena,
    validateIdPertenenciaCultural,
    validateIdPuebloIndigena,
    getCodigoAleatorio,
    getDatosPersonales,
    getInformacionAdicional,
    saveDatosPersonales,
    saveAdditionalData,
    getProgramas,
    validateDocuments,
    descargaReglamento,
    onSelectNacimiento,
    onSelectResidencias,
    setFormRef,
    setFormPreinscripcion,
    setFormDatosTransversales,
    consultaInscripcion,
    getPasoRegistrado,
    getDataPrisma,
    submit,
  }
}
