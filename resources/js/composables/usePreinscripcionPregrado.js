import { reactive, ref, watch, computed } from 'vue'
import { format } from 'date-fns'
import { es } from 'date-fns/locale'
import dayjs from 'dayjs'
import { notification } from 'ant-design-vue'

export const usePreinscripcionPregrado = (props) => {
  // ── Form refs ──────────────────────────────────────────────
  const formRef = ref()
  const formDatosPersonales = ref()
  const formDatosResidencia = ref()
  const formDatosColegio = ref()
  const formDatosPadre = ref()
  const formDatosMadre = ref()
  const formDatosTransversales = ref()
  const formPreinscripcion = ref()

  // ── Form state ──────────────────────────────────────────────
  const formState = reactive({ dni: '', codigo_secreto: '' })

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
    ubigeo: '',
    ubigeo_residencia: '',
  })

  const datosresidencia = reactive({
    pais: 125,
    ubigeo_res: null,
    dep: null,
    prov: null,
    dist: null,
    direccion: '',
  })

  const datoscolegio = reactive({
    id_colegio: null,
    egreso: null,
    pais: 125,
    ubigeo_cole: null,
    direccion: '',
    dep: null,
    prov: null,
    dist: null,
    colegio: '',
  })

  const datospadre = reactive({
    id: null,
    tipo_apoderado: 1,
    dni: null,
    nombres: null,
    paterno: null,
    materno: null,
  })

  const datosmadre = reactive({
    id: null,
    tipo_apoderado: 2,
    dni: null,
    nombres: null,
    paterno: null,
    materno: null,
  })

  const datos_transversales = reactive({
    discapacidad: null,
    tipo_discapacidad: '',
    id_condicion_lengua: null,
    id_lengua_indigena: null,
    id_pertenencia_cultural: null,
    id_pueblo_indigena: null,
  })

  const datos_preinscripcion = reactive({
    modalidad: null,
    programa: null,
    tipo_certificado: null,
    codigo_medico: null,
    codigo_certificado: null,
    observacion: null,
  })

  // ── UI state ───────────────────────────────────────────────
  const pagina_pre = ref(0)
  const avance = ref(0)
  const avance_current = ref(null)
  const id_pasos = ref(null)
  const examen = ref(0)
  const open = ref(false)
  const checkbox1 = ref(false)
  const activeKey = ref('1')
  const loading = ref(false)
  const modalcarrerasprevias = ref(false)
  const modalSancionado = ref(false)
  const modalcepreaviso = ref(false)
  const modalUbigeo = ref(false)
  const ejemplo = ref(false)
  const modalDatos = ref(true)
  const presionado = ref(0)
  const participa = ref(0)
  const postulante_inscrito = ref(0)
  const modalCargarDatos = ref(false)
  const datosPrevios = ref(null)
  const enviandoCodigo = ref(false)
  const verificandoCodigo = ref(false)
  const codigoVerificacion = ref('')
  const codigoEnviado = ref(false)
  const codigoExpirado = ref(false)
  const codigoError = ref('')
  const emailMasked = ref('')
  const countdownSegundos = ref(0)
  let countdownInterval = null
  const sancionado = ref(null)
  const postcepre = ref(null)
  const confirmacion = ref(null)
  const codigo_aleatorio = ref(null)
  const ultimopaso = ref(null)
  const pendingStep = ref(null)
  const nombrecolegiox = ref('')
  const temp_date = ref(null)
  const temp_area = ref(null)
  const college = ref(null)
  const participante = ref(null)
  const datacepre = ref(null)
  const traslado_interno = ref(false)
  const id_anterior = ref(null)
  const carrera_anterior = ref({})
  const bottom = ref(2)

  // ── Data lists ─────────────────────────────────────────────
  const departamentos = ref([])
  const departamentoscolegio = ref([])
  const provincias = ref([])
  const provinciasC = ref([])
  const distritos = ref([])
  const distritosC = ref([])
  const colegios = ref([])

  const buscarDep = ref('')
  const buscarDepC = ref('')
  const buscarProv = ref('')
  const buscarProvC = ref('')
  const buscarDist = ref('')
  const buscarDistC = ref('')

  const depseleccionado = ref('')
  const depseleccionadoC = ref('')
  const provseleccionada = ref(null)
  const provseleccionadaC = ref(null)
  const distseleccionado = ref('')
  const distseleccionadoC = ref('')

  // Nacimiento ubigeo
  const departamentosNac = ref([])
  const provinciasNac = ref([])
  const distritosNac = ref([])
  const buscarDepNac = ref('')
  const buscarProvNac = ref('')
  const buscarDistNac = ref('')
  const depseleccionadoNac = ref('')
  const provseleccionadaNac = ref(null)
  const distseleccionadoNac = ref('')

  // Unified ubigeo search (nacimiento + residencia)
  const ubigeoNacOptions = ref([])
  const ubigeoNacSeleccionado = ref(null)
  const ubigeoResOptions = ref([])
  const ubigeoResSeleccionado = ref(null)
  const ubigeoColeOptions = ref([])
  const ubigeoColeSeleccionado = ref(null)

  const modalidades = ref([])
  const programas = ref([])
  const carreras_previas = ref([])
  const anteriores = ref([])

  // ── Cultural data lists ────────────────────────────────────
  const condiciones_lengua = ref([])
  const opciones_pertenencia_cultural = ref([])
  const lenguas_indigenas = ref([])
  const pueblos_indigenas = ref([])

  // ── Constants ──────────────────────────────────────────────
  const tipo_docs = { 1: 'DNI', 2: 'PASAPORTE' }
  const estados_civil = { 1: 'SOLTERO', 2: 'CASADO', 3: 'VIUDO' }
  const sexos = { 1: 'MASCULINO', 2: 'FEMENINO' }
  const bio = ['04', '08', '15', '27', '28', '29']
  const ing = ['01', '02', '03', '05', '10', '22', '23', '24', '26', '30', '31', '32', '33', '34', '35', '36']
  const soc = ['06', '07', '09', '11', '12', '13', '14', '16', '17', '18', '20', '21', '25', '56']

  const PROCESO = () => props.procceso_seleccionado.id

  // ── Input sanitizers ───────────────────────────────────────
  const dniInput = (event) => { formState.dni = event.target.value.replace(/\D/g, '') }
  const ubigeoInput = (event) => { formState.ubigeo = event.target.value.replace(/\D/g, '') }
  const nombresInput = (event) => { datospersonales.nombres = event.target.value.replace(/[^A-Za-zÑñ\s]/g, '') }
  const pimerapellidoInput = (event) => { datospersonales.primerapellido = event.target.value.replace(/[^A-Za-zÑñ]/g, '') }
  const celularInput = (event) => { datospersonales.celular = event.target.value.replace(/\D/g, '') }

  // ── Validators ──────────────────────────────────────────────
  const validateFechaNacimiento = (rule, value) => {
    return new Promise((resolve, reject) => {
      if (!value) {
        reject(new Error('Por favor, selecciona tu fecha de nacimiento.'))
      } else {
        const fechaNacimiento = new Date(value)
        const fechaMinima = new Date()
        fechaMinima.setFullYear(fechaMinima.getFullYear() - 16)
        if (fechaNacimiento > fechaMinima) {
          reject(new Error('Debes tener al menos 16 años.'))
        } else {
          resolve()
        }
      }
    })
  }

  const validateCelular = (rule, value) => {
    return new Promise((resolve, reject) => {
      if (!value) {
        reject(new Error('Por favor, ingresa tu número de celular.'))
      } else {
        axios.post('/existe-celular', { celular: value, dni: formState.dni })
          .then(response => {
            if (response.data == true) {
              reject(new Error('Este número de celular ya está registrado.'))
            } else { resolve() }
          })
          .catch(() => reject(new Error('Error al verificar el número de celular.')))
      }
    })
  }

  const validateCorreo = (rule, value) => {
    return new Promise((resolve, reject) => {
      if (!value) {
        reject(new Error('Por favor, ingresa tu correo.'))
      } else {
        axios.post('/existe-correo', { email: value, dni: formState.dni })
          .then(response => {
            if (response.data == true) {
              reject(new Error('Este correo ya está registrado.'))
            } else { resolve() }
          })
          .catch(() => reject(new Error('Error al verificar correo.')))
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

  // ── Navigation ────────────────────────────────────────────
  const next = () => { pagina_pre.value++ }
  const prev = () => { pagina_pre.value-- }

  const abrirModalDatos = async () => {
    const values = await formPreinscripcion.value.validateFields()
    open.value = true
    cambiarFormato()
    getUbigeo()
    getApoderado()
    getApoderadoM()
    getColegios()
    getColegioSeleccionado()
  }

  const cancelarInscripcion = () => {
    modalcarrerasprevias.value = false
    location.reload(true)
  }

  const desactivar = () => {
    modalcarrerasprevias.value = false
    window.location.reload()
  }

  // ── Step tracking ──────────────────────────────────────────
  const getPasos = async () => {
    let res = await axios.post('/get-pasos-proceso', {
      postulante: datospersonales.id,
      proceso: PROCESO(),
    })
    if (res.data.datos.length > 0) {
      avance_current.value = res.data.datos[0].avance
      id_pasos.value = res.data.datos[0].id
      pagina_pre.value = res.data.datos[0].nro + 1
      avance.value = res.data.datos[0].avance
      modalcarrerasprevias.value = false
      loading.value = false
    } else {
      getSancionado()
    }
  }

  const savePasos = async (namex, num, avan) => {
    let res = await axios.post('/save-pasos-preinscripcion', {
      id: id_pasos.value,
      nombre: namex,
      nro: num,
      avance: avan,
      postulante: datospersonales.id,
      proceso: PROCESO(),
    })
    if (res.data.estado === true) {
      id_pasos.value = res.data.datos.id
      avance_current.value = avan
      await getPasos()
    }
  }

  const getPasoRegistrado = async () => {
    ultimopaso.value = null
    pendingStep.value = null
    loading.value = true
    try {
      let res = await axios.get('/get-paso-registrado/' + PROCESO() + '/' + formState.dni)
      if (res.data.estado == true) {
        ultimopaso.value = res.data.datos
        await getDatosPersonales2()
        loading.value = false
      } else {
        modalcarrerasprevias.value = true
        getSancionado()
      }
    } catch (error) {
      console.error('Error al obtener paso registrado:', error)
      loading.value = false
    }
  }

  const verificarDatosExistentes = async () => {
    try {
      let res = await axios.post('/get-postulante-datos-personales2', { nro_doc: formState.dni })
      if (res.data.datos && res.data.datos.length > 0) {
        datosPrevios.value = res.data.datos[0]
        // If email verification is disabled, load data and navigate directly
        if (res.data.requires_email_verification === false) {
          modalCargarDatos.value = false
          await cargarDatosYNavegar()
          return true
        }
        modalCargarDatos.value = true
        return true
      }
    } catch (error) {
      console.error('Error al verificar datos existentes:', error)
    }
    return false
  }

  const iniciarCountdown = () => {
    if (countdownInterval) clearInterval(countdownInterval)
    countdownSegundos.value = 180
    codigoExpirado.value = false
    countdownInterval = setInterval(() => {
      countdownSegundos.value--
      if (countdownSegundos.value <= 0) {
        clearInterval(countdownInterval)
        countdownInterval = null
        codigoExpirado.value = true
      }
    }, 1000)
  }

  const solicitarCodigoVerificacion = async () => {
    enviandoCodigo.value = true
    codigoError.value = ''
    try {
      const res = await axios.post('/enviar-codigo-verificacion-datos', { nro_doc: formState.dni })
      if (res.data.estado) {
        emailMasked.value = res.data.email
        codigoEnviado.value = true
        codigoExpirado.value = false
        codigoVerificacion.value = ''
        iniciarCountdown()
      } else {
        codigoError.value = res.data.mensaje || 'No se pudo enviar el código'
      }
    } catch (e) {
      codigoError.value = e.response?.data?.mensaje || 'No se pudo enviar el código'
    } finally {
      enviandoCodigo.value = false
    }
  }

  const verificarCodigoYCargar = async () => {
    verificandoCodigo.value = true
    codigoError.value = ''
    try {
      const res = await axios.post('/verificar-codigo-datos', {
        nro_doc: formState.dni,
        codigo: codigoVerificacion.value,
      })
      if (res.data.estado) {
        if (countdownInterval) { clearInterval(countdownInterval); countdownInterval = null }
        modalCargarDatos.value = false
        resetCodigoVerificacion()
        await cargarDatosYNavegar()
      } else {
        codigoError.value = res.data.mensaje || 'Código incorrecto'
      }
    } catch (e) {
      codigoError.value = e.response?.data?.mensaje || 'No se pudo verificar el código'
    } finally {
      verificandoCodigo.value = false
    }
  }

  const resetCodigoVerificacion = () => {
    if (countdownInterval) { clearInterval(countdownInterval); countdownInterval = null }
    codigoEnviado.value = false
    codigoExpirado.value = false
    codigoVerificacion.value = ''
    codigoError.value = ''
    emailMasked.value = ''
    countdownSegundos.value = 0
  }

  const setCodigoVerificacion = (val) => { codigoVerificacion.value = val }
  const setCodigoError = (val) => { codigoError.value = val }

  const aceptarCargarDatos = async () => {
    if (datosPrevios.value) {
      const d = datosPrevios.value
      datospersonales.id = d.id
      datospersonales.primerapellido = d.primer_apellido
      datospersonales.segundo_apellido = d.segundo_apellido
      datospersonales.nombres = d.nombres
      datospersonales.estado_civil = d.estado_civil
      datospersonales.sexo = d.sexo
      datospersonales.correo = d.correo
      datospersonales.celular = d.celular
      if (d.fec_nacimiento) { datospersonales.fec_nacimiento = dayjs(d.fec_nacimiento) }
      formState.ubigeo = d.ubigeo
      datosresidencia.direccion = d.direccion

      // País Perú por defecto para DNI
      if (datospersonales.tipo_doc === 1) { datosresidencia.pais = 125 }
      datospersonales.ubigeo_residencia = d.ubigeo_residencia
      datospersonales.ubigeo = d.ubigeo

      // Cargar ubigeo de nacimiento en el select unificado
      if (d.ubigeo) {
        ubigeoNacSeleccionado.value = d.ubigeo
        if (d.nacimiento) {
          ubigeoNacOptions.value = [{ key: d.ubigeo, value: d.nacimiento }]
        }
      }
      // Cargar ubigeo de residencia en el select unificado
      if (d.ubigeo_residencia) {
        ubigeoResSeleccionado.value = d.ubigeo_residencia
        datosresidencia.ubigeo_res = d.ubigeo_residencia
        if (d.residencia) {
          ubigeoResOptions.value = [{ key: d.ubigeo_residencia, value: d.residencia }]
        }
      }
    }
    modalCargarDatos.value = false
    pagina_pre.value = 1
  }

  const rechazarCargarDatos = () => {
    resetCodigoVerificacion()
    datospersonales.id = null
    datospersonales.primerapellido = ''
    datospersonales.segundo_apellido = ''
    datospersonales.nombres = ''
    datospersonales.estado_civil = null
    datospersonales.sexo = null
    datospersonales.correo = ''
    datospersonales.celular = ''
    datospersonales.fec_nacimiento = ''
    datospersonales.ubigeo = ''
    datosresidencia.direccion = ''
    datosresidencia.dep = null
    datosresidencia.prov = null
    datosresidencia.dist = null
    datospersonales.ubigeo_residencia = ''
    ubigeoNacSeleccionado.value = null
    ubigeoResSeleccionado.value = null
    datosresidencia.ubigeo_res = null
    modalCargarDatos.value = false
    pagina_pre.value = 1
  }

  // Navigate to the correct step based on ultimopaso
  const navegarSegunPaso = async () => {
    if (!ultimopaso.value) return
    if (ultimopaso.value.nro == 6) {
      try {
        let res = await axios.get('/participa-proceso/' + PROCESO() + '/' + formState.dni)
        if (res.data.estado === true) {
          postulante_inscrito.value = 1
          pagina_pre.value = 7
        } else {
          pagina_pre.value = 6
        }
      } catch (error) {
        console.error('Error al verificar inscripción:', error)
        pagina_pre.value = 6
      }
    } else {
      pagina_pre.value = ultimopaso.value.nro
    }
  }

  // Load postulante data and navigate to last registered step
  const cargarDatosYNavegar = async () => {
    loading.value = true
    try {
      await getDatosPersonales2()
      let res = await axios.get('/get-paso-registrado/' + PROCESO() + '/' + formState.dni)
      if (res.data.estado == true) {
        ultimopaso.value = res.data.datos
        await navegarSegunPaso()
      } else {
        pagina_pre.value = 1
      }
    } catch (error) {
      console.error('Error al cargar datos y navegar:', error)
      pagina_pre.value = 1
    } finally {
      loading.value = false
    }
  }

  // ── Start postulation (called when user clicks "Iniciar Postulación") ──
  const iniciarPostulacion = async () => {
    loading.value = true
    try {
      // Always check for existing data — verification required to protect postulante data
      const tieneDatos = await verificarDatosExistentes()
      if (tieneDatos) {
        loading.value = false
        return // Modal is showing, wait for user to verify code
      }
      // No existing data — proceed with normal registration flow
      await getPasoRegistrado()
      if (ultimopaso.value) {
        await navegarSegunPaso()
        loading.value = false
        return
      }
      // If not registered, CarrerasPreviasModal is showing (started by getSancionado)
      // getSancionado() manages loading through its own flow
    } catch (error) {
      console.error('Error en iniciarPostulacion:', error)
      loading.value = false
    }
  }

  // ── Data loading: personal ─────────────────────────────────
  const getDatosPersonales = async () => {
    if (pagina_pre.value == 0) {
      const values = await formRef.value.validateFields()
    }
    let res = await axios.post('/get-postulante-datos-personales', { nro_doc: formState.dni, ubigeo: formState.ubigeo })
    if (res.data.datos && res.data.datos.length > 0) {
      const d = res.data.datos[0]
      datospersonales.id = d.id
      datospersonales.primerapellido = d.primer_apellido
      datospersonales.segundo_apellido = d.segundo_apellido
      datospersonales.nombres = d.nombres
      datospersonales.estado_civil = d.estado_civil
      datospersonales.sexo = d.sexo
      datospersonales.correo = d.correo
      datospersonales.celular = d.celular
      if (d.fec_nacimiento) { datospersonales.fec_nacimiento = dayjs(d.fec_nacimiento) }
      datospersonales.ubigeo = d.ubigeo
      datosresidencia.direccion = d.direccion

      // País Perú por defecto para DNI
      if (datospersonales.tipo_doc === 1) { datosresidencia.pais = 125 }
      datospersonales.ubigeo_residencia = d.ubigeo_residencia

      // Cargar ubigeo de nacimiento en el select unificado
      if (d.ubigeo) {
        ubigeoNacSeleccionado.value = d.ubigeo
        if (d.nacimiento) {
          ubigeoNacOptions.value = [{ key: d.ubigeo, value: d.nacimiento }]
        }
      }
      // Cargar ubigeo de residencia en el select unificado
      if (d.ubigeo_residencia) {
        ubigeoResSeleccionado.value = d.ubigeo_residencia
        datosresidencia.ubigeo_res = d.ubigeo_residencia
        if (d.residencia) {
          ubigeoResOptions.value = [{ key: d.ubigeo_residencia, value: d.residencia }]
        }
      }

      pagina_pre.value = 1
    } else {
      getDatosApi()
      pagina_pre.value = 1
    }
  }

  const getDatosPersonales2 = async () => {
    let res = await axios.post('/get-postulante-datos-personales2', { nro_doc: formState.dni })
    if (res.data.datos.length > 0) {
      const d = res.data.datos[0]
      datospersonales.id = d.id
      datospersonales.primerapellido = d.primer_apellido
      datospersonales.segundo_apellido = d.segundo_apellido
      datospersonales.nombres = d.nombres
      datospersonales.estado_civil = d.estado_civil
      datospersonales.sexo = d.sexo
      datospersonales.correo = d.correo
      datospersonales.celular = d.celular
      if (d.fec_nacimiento) { datospersonales.fec_nacimiento = dayjs(d.fec_nacimiento) }
      formState.ubigeo = d.ubigeo
      datosresidencia.direccion = d.direccion

      // País Perú por defecto para DNI
      if (datospersonales.tipo_doc === 1) { datosresidencia.pais = 125 }
      datospersonales.ubigeo_residencia = d.ubigeo_residencia
      datospersonales.ubigeo = d.ubigeo

      // Cargar ubigeo de nacimiento en el select unificado
      if (d.ubigeo) {
        ubigeoNacSeleccionado.value = d.ubigeo
        if (d.nacimiento) {
          ubigeoNacOptions.value = [{ key: d.ubigeo, value: d.nacimiento }]
        }
      }
      // Cargar ubigeo de residencia en el select unificado
      if (d.ubigeo_residencia) {
        ubigeoResSeleccionado.value = d.ubigeo_residencia
        datosresidencia.ubigeo_res = d.ubigeo_residencia
        if (d.residencia) {
          ubigeoResOptions.value = [{ key: d.ubigeo_residencia, value: d.residencia }]
        }
      }
    }
  }

  // ── External API (apiperu.dev) ────────────────────────────
  const getDatosApi = () => {
    const token = '70ab1cd1b9b452982370381fd0be605c85ddc8795aed972afca143fee05fde43'
    axios.get(`https://apiperu.dev/api/dni/${formState.dni}`, {
      headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` },
    })
      .then(response => {
        const data = response.data
        datospersonales.primerapellido = data.data.apellido_paterno
        datospersonales.segundo_apellido = data.data.apellido_materno
        datospersonales.nombres = data.data.nombres
      })
      .catch(error => console.error(error))
    saveDNI()
  }

  const getPadreApi = () => {
    const token = '70ab1cd1b9b452982370381fd0be605c85ddc8795aed972afca143fee05fde43'
    axios.get(`https://apiperu.dev/api/dni/${datospadre.dni}`, {
      headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` },
    })
      .then(response => {
        const data = response.data
        datospadre.paterno = data.data.apellido_paterno
        datospadre.materno = data.data.apellido_materno
        datospadre.nombres = data.data.nombres
      })
      .catch(error => console.error(error))
  }

  const getMadreApi = () => {
    const token = '70ab1cd1b9b452982370381fd0be605c85ddc8795aed972afca143fee05fde43'
    axios.get(`https://apiperu.dev/api/dni/${datosmadre.dni}`, {
      headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` },
    })
      .then(response => {
        const data = response.data
        datosmadre.paterno = data.data.apellido_paterno
        datosmadre.materno = data.data.apellido_materno
        datosmadre.nombres = data.data.nombres
      })
      .catch(error => console.error(error))
  }

  // ── Save methods ───────────────────────────────────────────
  const saveDNI = async () => {
    let res = await axios.post('/save-postulante-dni', {
      tipo_doc: datospersonales.tipo_doc,
      nro_doc: formState.dni,
      ubigeo_nacimiento: formState.ubigeo,
      paterno: datospersonales.primerapellido,
      materno: datospersonales.segundo_apellido,
      nombres: datospersonales.nombres,
    })
    getDatosPersonales2()
  }

  const saveDatosPersonales = async () => {
    try {
      const values = await formDatosPersonales.value.validateFields()
      let res = await axios.post('/save-postulante', {
        tipo_doc: datospersonales.tipo_doc,
        nro_doc: formState.dni,
        id: datospersonales.id,
        primer_apellido: datospersonales.primerapellido,
        segundo_apellido: datospersonales.segundo_apellido,
        nombres: datospersonales.nombres,
        correo: datospersonales.correo,
        celular: datospersonales.celular,
        sexo: datospersonales.sexo,
        estado_civil: datospersonales.estado_civil,
        fec_nacimiento: format(new Date(datospersonales.fec_nacimiento), 'yyyy-MM-dd'),
        ubigeo_nacimiento: datospersonales.ubigeo,
        direccion: datosresidencia.direccion,
      })

      if (res.data.datos && res.data.datos.id) {
        datospersonales.id = res.data.datos.id
      }
      if (avance_current.value < 16) {
        await savePasos('Registro de datos personales', 1, 16)
      } else {
        next()
      }
    } catch (error) {
      console.error('Error al guardar datos personales:', error)
    }
  }

  const saveDatosResidencia = async () => {
    try {
      const values = await formDatosResidencia.value.validateFields()
      let res = await axios.post('/save-postulante-residencia', {
        id: datospersonales.id,
        ubigeo_residencia: datospersonales.ubigeo_residencia,
        direccion: datosresidencia.direccion,
      })
      if (avance_current.value < 32) {
        await savePasos('Registro de datos de residencia', 2, 32)
      } else {
        next()
      }
    } catch (error) {
      console.error('Error al guardar datos de residencia:', error)
    }
  }

  const savecolegio = async () => {
    try {
      const values = await formDatosColegio.value.validateFields()
      let ac = 'si'
      if (avance_current.value >= 48) { ac = 'no' }
      const response = await axios.post('/save-postulante-colegio', {
        id: datospersonales.id,
        anio_egreso: datoscolegio.egreso,
        colegio: datoscolegio.id_colegio,
        actualizar: ac,
        proceso: PROCESO(),
      })
      if (avance_current.value < 48) {
        await savePasos('Registro de datos del colegio', 3, 48)
      } else {
        next()
      }
    } catch (error) {
      console.error('Error al guardar datos del colegio:', error)
    }
  }

  const saveApoderado = async () => {
    try {
      const values = await formDatosPadre.value.validateFields()
      let avn = 65
      let ac = 'si'
      if (avance_current.value >= 65) { ac = 'no' }
      if (datospadre.tipo_apoderado === 3) { avn = 80 }
      const response = await axios.post('/save-postulante-apoderado', {
        id: datospadre.id,
        tipo_apoderado: datospadre.tipo_apoderado,
        dni: datospadre.dni,
        nombres: datospadre.nombres,
        paterno: datospadre.paterno,
        materno: datospadre.materno,
        id_postulante: datospersonales.id,
        actualizar: ac,
        proceso: PROCESO(),
        name: 'Registro de datos del padre o tutor',
        nro: 4,
        avance: avn,
      })
      if (avance_current.value < avn) {
        await savePasos('Registro de datos del padre o tutor', 4, avn)
      } else {
        next()
      }
    } catch (error) {
      console.error('Error al guardar datos del padre:', error)
    }
  }

  const saveApoderadoM = async () => {
    try {
      const values = await formDatosMadre.value.validateFields()
      let avn = 80
      let ac = 'si'
      if (avance_current.value >= 80) { ac = 'no' }
      const response = await axios.post('/save-postulante-apoderado', {
        id: datosmadre.id,
        tipo_apoderado: datosmadre.tipo_apoderado,
        dni: datosmadre.dni,
        nombres: datosmadre.nombres,
        paterno: datosmadre.paterno,
        materno: datosmadre.materno,
        id_postulante: datospersonales.id,
        actualizar: ac,
        proceso: PROCESO(),
        name: 'Registro de datos de la madre',
        nro: 5,
        avance: avn,
      })
      if (avance_current.value < avn) {
        await savePasos('Registro de datos de la madre', 5, avn)
      } else {
        next()
      }
    } catch (error) {
      console.error('Error al guardar datos de la madre:', error)
    }
  }

  const saveAdditionalData = async () => {
    try {
      await formDatosTransversales.value.validateFields()
      let res = await axios.post('/save-postulante-adicional', {
        id_postulante: datospersonales.id,
        id_proceso: PROCESO(),
        discapacidad: datos_transversales.discapacidad,
        tipo_discapacidad: datos_transversales.tipo_discapacidad,
        id_condicion_lengua: datos_transversales.id_condicion_lengua,
        id_lengua_indigena: datos_transversales.id_lengua_indigena,
        id_pertenencia_cultural: datos_transversales.id_pertenencia_cultural,
        id_pueblo_indigena: datos_transversales.id_pueblo_indigena,
      })
      if (res.data.estado === true) {
        if (avance_current.value < 85) {
          await savePasos('Registro de datos adicionales', 6, 85)
        } else {
          next()
        }
      }
    } catch (error) {
      console.error(error)
    }
  }

  const submit = async () => {
    let fd = new FormData()
    fd.append('dni', formState.dni)
    fd.append('modalidad', datos_preinscripcion.modalidad)
    fd.append('programa', datos_preinscripcion.programa)
    fd.append('tipo_certificado', datos_preinscripcion.tipo_certificado)
    fd.append('codigo_certificado', datos_preinscripcion.codigo_certificado)
    fd.append('codigo_medico', datos_preinscripcion.codigo_medico)
    fd.append('observacion', datos_preinscripcion.observacion)
    fd.append('id_postulante', datospersonales.id)
    fd.append('id_proceso', PROCESO())
    fd.append('id_anterior', id_anterior.value)
    await axios.post('/save-pre-inscripcion', fd).then(res => {
      if (avance_current.value < 100) {
        savePasos('Registro de datos preinscripcion', 7, 110)
      } else {
        next()
      }
      notificacion('success', 'Éxito', res.data.menssje)
    }).catch(err => console.log(err))
    open.value = false
  }

  // ── Ubigeo helpers (residence) ─────────────────────────────
  const getDepartamentos = async () => {
    let res = await axios.post('/get-departamentos-codigo?page=', { term: buscarDep.value })
    departamentos.value = res.data.datos.data
  }

  const getProvincias = async () => {
    let res = await axios.post('/get-provincias-codigo?page=', { departamento: depseleccionado.value })
    provincias.value = res.data.datos
  }

  const getDistritos = async () => {
    let res = await axios.post('/get-distritos-codigo?page=', {
      departamento: depseleccionado.value,
      provincia: provseleccionada.value,
    })
    distritos.value = res.data.datos
  }

  const onSelectDepartamentos = (value, option) => {
    depseleccionado.value = option.key
    getProvincias()
  }

  const onSelectProvincias = (value, option) => {
    provseleccionada.value = option.key
    getDistritos()
  }

  const onSelectDistritos = (value, option) => {
    distseleccionado.value = option.key
  }

  // ── Ubigeo helpers (nacimiento) ────────────────────────────
  const getDepartamentosNac = async () => {
    let res = await axios.post('/get-departamentos-codigo?page=', { term: buscarDepNac.value })
    departamentosNac.value = res.data.datos.data
  }

  const getProvinciasNac = async () => {
    let res = await axios.post('/get-provincias-codigo?page=', { departamento: depseleccionadoNac.value })
    provinciasNac.value = res.data.datos
  }

  const getDistritosNac = async () => {
    let res = await axios.post('/get-distritos-codigo?page=', {
      departamento: depseleccionadoNac.value,
      provincia: provseleccionadaNac.value,
    })
    distritosNac.value = res.data.datos
  }

  const onSelectDepartamentosNac = (value, option) => {
    depseleccionadoNac.value = value
    datospersonales.ubigeo = value
    getProvinciasNac()
  }

  const onSelectProvinciasNac = (value, option) => {
    provseleccionadaNac.value = value
    datospersonales.ubigeo = depseleccionadoNac.value + value
    getDistritosNac()
  }

  const onSelectDistritosNac = (value, option) => {
    distseleccionadoNac.value = value
    datospersonales.ubigeo = depseleccionadoNac.value + provseleccionadaNac.value + value
  }

  // ── Unified ubigeo search (nacimiento) ─────────────────────
  const getUbigeosNac = async (term) => {
    let res = await axios.post('/get-ubigeo', { term: term || '' })
    ubigeoNacOptions.value = res.data.datos.data
  }

  const onSelectUbigeoNac = (value) => {
    ubigeoNacSeleccionado.value = value
    datospersonales.ubigeo = value
  }

  // ── Unified ubigeo search (residencia) ─────────────────────
  const getUbigeosRes = async (term) => {
    let res = await axios.post('/get-ubigeo', { term: term || '' })
    ubigeoResOptions.value = res.data.datos.data
  }

  const onSelectUbigeoRes = (value) => {
    ubigeoResSeleccionado.value = value
    datospersonales.ubigeo_residencia = value
    datosresidencia.ubigeo_res = value
  }

  // ── Unified ubigeo search (colegio) ────────────────────────
  const getUbigeosCole = async (term) => {
    let res = await axios.post('/get-ubigeo', { term: term || '' })
    const newOptions = res.data.datos.data
    if (ubigeoColeSeleccionado.value) {
      const exists = newOptions.some(o => o.key === ubigeoColeSeleccionado.value)
      if (!exists) {
        const current = ubigeoColeOptions.value.find(o => o.key === ubigeoColeSeleccionado.value)
        if (current) newOptions.unshift(current)
      }
    }
    ubigeoColeOptions.value = newOptions
  }

  const onSelectUbigeoCole = (value) => {
    ubigeoColeSeleccionado.value = value
    datoscolegio.ubigeo_cole = value
    colegios.value = []
    datoscolegio.id_colegio = null
    datoscolegio.direccion = ''
    if (value) {
      getColegios()
    }
  }

  const onSelectColegio = (val) => {
    datoscolegio.id_colegio = val
    const selected = colegios.value.find(c => c.value === val)
    datoscolegio.direccion = selected ? (selected.direccion || '') : ''
  }

  // ── Ubigeo helpers (school) ────────────────────────────────
  const getDepartamentosColegio = async () => {
    let res = await axios.post('/get-departamentos-codigo?page=', { term: buscarDepC.value })
    departamentoscolegio.value = res.data.datos.data
  }

  const getProvinciasColegio = async () => {
    let res = await axios.post('/get-provincias-codigo?page=', { departamento: depseleccionadoC.value })
    provinciasC.value = res.data.datos
  }

  const getDistritosColegio = async () => {
    let res = await axios.post('/get-distritos-codigo?page=', {
      departamento: depseleccionadoC.value,
      provincia: provseleccionadaC.value,
    })
    distritosC.value = res.data.datos
  }

  const onSelectDepartamentosC = (value, option) => {
    depseleccionadoC.value = option.key
    getProvinciasColegio()
    provseleccionadaC.value = null
    datoscolegio.prov = null
    datoscolegio.dist = null
    distritosC.value = []
    datoscolegio.colegio = null
    colegios.value = []
  }

  const onSelectProvinciasC = (value, option) => {
    provseleccionadaC.value = option.key
    datoscolegio.dist = null
    getDistritosColegio()
    datoscolegio.colegio = null
    colegios.value = []
  }

  const onSelectDistritosC = (value, option) => {
    distseleccionadoC.value = option.key
    datoscolegio.colegio = null
    getColegios()
  }

  const getColegios = async () => {
    if (!ubigeoColeSeleccionado.value) return
    const res = await axios.post('/get-colegio-distrito', {
      ubigeo_cole: ubigeoColeSeleccionado.value,
    })
    colegios.value = res.data.datos
  }

  const getUbigeo = async () => {
    const res = await axios.post('/get-ubigeo-colegio', { id_postulante: datospersonales.id })
    if (res.data && res.data.length > 0) {
      const d = res.data[0]
      datoscolegio.egreso = d.egreso
      datoscolegio.id_colegio = d.value
      datoscolegio.direccion = d.direccion || ''
      nombrecolegiox.value = d.label
      if (d.ubigeo) {
        ubigeoColeSeleccionado.value = d.ubigeo
        datoscolegio.ubigeo_cole = d.ubigeo
        ubigeoColeOptions.value = [{ key: d.ubigeo, value: d.ubigeo + ' - ' + (d.departamento || '') + '/' + (d.provincia || '') + '/' + (d.distrito || '') }]
        await getColegios()
      }
    }
  }

  // ── Apoderado helpers ─────────────────────────────────────
  const getApoderado = async () => {
    const res = await axios.post('/get-apoderado', { id_postulante: datospersonales.id, tipo: 1 })
    if (res.data.datos.length !== 0) {
      const d = res.data.datos[0]
      datospadre.id = d.id
      datospadre.tipo_apoderado = d.tipo_apoderado
      datospadre.dni = d.nro_documento
      datospadre.nombres = d.nombres
      datospadre.paterno = d.paterno
      datospadre.materno = d.materno
    }
  }

  const getApoderadoM = async () => {
    const res = await axios.post('/get-apoderado', { id_postulante: datospersonales.id, tipo: 2 })
    if (res.data.datos.length !== 0) {
      const d = res.data.datos[0]
      datosmadre.id = d.id
      datosmadre.dni = d.nro_documento
      datosmadre.nombres = d.nombres
      datosmadre.paterno = d.paterno
      datosmadre.materno = d.materno
    }
  }

  const getApoderadoDNI = async (tipo) => {
    if (tipo == 1) {
      let res = await axios.post('/get-apoderado-dni', { dni: datospadre.dni })
      if (res.data.estado === true) {
        datospadre.dni = res.data.datos.dni
        datospadre.nombres = res.data.datos.nombres
        datospadre.paterno = res.data.datos.paterno
        datospadre.materno = res.data.datos.materno
      } else {
        getPadreApi()
      }
    } else {
      let res = await axios.post('/get-apoderado-dni', { dni: datosmadre.dni })
      if (res.data.estado === true) {
        datosmadre.dni = res.data.datos.dni
        datosmadre.nombres = res.data.datos.nombres
        datosmadre.paterno = res.data.datos.paterno
        datosmadre.materno = res.data.datos.materno
      } else {
        getMadreApi()
      }
    }
  }

  // ── Cultural data helpers ──────────────────────────────────
  const getCondicionesLengua = async () => {
    const response = await axios.get('/get-condiciones-lengua-segundas')
    if (response.data) condiciones_lengua.value = response.data
  }

  const getOptionsPertenenciaCultural = async () => {
    const response = await axios.get('/get-pertenencia-cultural-segundas')
    if (response.data) opciones_pertenencia_cultural.value = response.data
  }

  const getLenguasIndigenas = async () => {
    const response = await axios.get('/get-lengua-segundas')
    if (response.data) lenguas_indigenas.value = response.data
  }

  const getPueblosIndigenas = async () => {
    const response = await axios.get('/get-pueblos-indigenes-segundas')
    if (response.data) pueblos_indigenas.value = response.data
  }

  const getInformacionAdicional = async () => {
    if (!datospersonales.id) return
    axios
      .get('/get-identidad-cultural/' + datospersonales.id + '/' + PROCESO())
      .then(response => {
        datos_transversales.discapacidad = response.data.discapacidad
        datos_transversales.tipo_discapacidad = response.data.tipo_discapacidad
        datos_transversales.id_condicion_lengua = response.data.id_condicion_lengua
        datos_transversales.id_lengua_indigena = response.data.id_lengua_indigena
        datos_transversales.id_pertenencia_cultural = response.data.id_pertenencia_cultural
        datos_transversales.id_pueblo_indigena = response.data.id_pueblo_indigena
      })
      .catch(error => console.error('Error al obtener información cultural:', error))
  }

  // ── Modalidades & programas ───────────────────────────────
  const getModalidades = async () => {
    let res = await axios.get('/get-select-modalidad-proceso/' + PROCESO())
    modalidades.value = res.data.datos
  }

  const getProgramas = async () => {
    let res = await axios.post('/get-select-programas-proceso', {
      id_modalidad: datos_preinscripcion.modalidad,
      id_proceso: PROCESO(),
    })
    programas.value = res.data.datos
  }

  const getProgramasArea = async () => {
    let res = await axios.post('/get-select-programas-proceso-area', {
      id_modalidad: datos_preinscripcion.modalidad,
      id_proceso: PROCESO(),
      area: temp_area.value,
    })
    programas.value = res.data.datos
  }

  const getAreaCodigo = async () => {
    let res = await axios.get('/get-area-by-codigo/' + datos_preinscripcion.observacion)
    temp_area.value = res.data.datos.area
    await getProgramasArea()
  }

  // ── Captcha ───────────────────────────────────────────────
  const getCodigoAleatorio = async () => {
    let res = await axios.get('/generar-captcha')
    codigo_aleatorio.value = res.data.captcha
  }

  // ── Sancionado & inscripción checks ───────────────────────
  const getSancionado = async () => {
    participa.value = 0
    try {
      let res = await axios.get('/get-sancionado/' + formState.dni + '/' + PROCESO())
      sancionado.value = res.data.datos
      if (sancionado.value != null) {
        datacepre.value = []
        loading.value = false
        modalSancionado.value = true
        anteriores.value = []
        return
      } else {
        await consultaInscripcion()
      }
    } catch (error) {
      console.error('Error al obtener datos de sancionado', error)
      loading.value = false
    }
  }

  const getParticipanteCepre = async () => {
    datacepre.value = []
    try {
      let res = await axios.get('/get-participante-cepre/' + formState.dni)
      if (res.data.estado === true) {
        datacepre.value = res.data.datos
        datospersonales.nombres = datacepre.value.nombres
        datospersonales.primerapellido = datacepre.value.paterno
        datospersonales.segundo_apellido = datacepre.value.materno
        datospersonales.sexo = datacepre.value.sexo
        datospersonales.ubigeo_residencia = datacepre.value.codigo_distrito
        datoscolegio.egreso = datacepre.value.anio_egreso
        participa.value = 1
        getDataPrisma()
        getDatosPersonales2()
      } else {
        loading.value = false
        modalSancionado.value = true
        return
      }
    } catch (error) {
      console.error('Error al obtener datos del participante', error)
      loading.value = false
    }
  }

  const consultaInscripcion = async () => {
    postulante_inscrito.value = 0
    try {
      let res = await axios.get('/participa-proceso/' + PROCESO() + '/' + formState.dni)
      if (res.data.estado === true) {
        postulante_inscrito.value = 1
        modalcarrerasprevias.value = false
        loading.value = false
        pagina_pre.value = 7
      } else {
        if (props.procceso_seleccionado.id_modalidad_proceso == 2) {
          await getParticipanteCepre()
          participa.value = 1
        } else {
          await getDataPrisma()
          getDatosPersonales2()
          participa.value = 1
        }
      }
    } catch (error) {
      console.error('Error al obtener datos del participante', error)
      loading.value = false
    }
  }

  const consultaInscripcion2 = async () => {
    postulante_inscrito.value = 0
    try {
      let res = await axios.get('/participa-proceso/' + PROCESO() + '/' + formState.dni)
      if (res.data.estado === true) {
        pagina_pre.value = 7
      } else {
        pagina_pre.value = 6
      }
    } catch (error) {
      console.error('Error al obtener datos del participante', error)
    }
  }

  // ── Carreras previas ──────────────────────────────────────
  const getDataPrisma = async () => {
    participante.value = null
    try {
      const response = await axios.get('/get-data-prisma/' + formState.dni)
      if (response.data.estado === true) {
        participante.value = response.data.datos
      }
    } catch (error) {
      console.error('Error al obtener datos de PRISMA:', error)
    }
    getCarrerasPrevias()
  }

  const getCarrerasPrevias = async () => {
    try {
      const response = await axios.post('/get-carreras-previas', {
        participante: participante.value,
        formState: formState.dni,
      })
      if (response.data.mensaje === 'No tiene carreras previas') {
        const data = response.data.anteriores
        anteriores.value = []
        loading.value = data.loading
        modalSancionado.value = data.modalSancionado
        confirmacion.value = data.confirmacion
      } else {
        loading.value = false
        anteriores.value = response.data.anteriores
        modalSancionado.value = false
        confirmacion.value = false
      }
    } catch (error) {
      console.error('Error fetching data: ', error)
      loading.value = false
    }
  }

  const registrarPrevias = async () => {
    confirmacion.value = null
    axios.post('/registrar-carreras-previas', { carreras: selectedItems.value, dni: formState.dni })
      .then(response => {
        confirmacion.value = response.data.estado
        modalcarrerasprevias.value = false
      })
      .catch(error => {
        if (error.response) {
          console.error('Error de servidor:', error.response.data)
        } else if (error.request) {
          console.error('Error de red:', error.request)
        } else {
          console.error('Error de configuración:', error.message)
        }
      })
  }

  const toggleSelection = (item) => {
    item.selected = !item.selected
  }

  const selectedItems = computed(() => {
    if (anteriores.value) {
      return anteriores.value.filter(item => item.selected)
    }
  })

  const toggleSelection2 = (item) => {
    item.selected = !item.selected
    if (item.selected == true) {
      id_anterior.value = item.id
      carrera_anterior.value = item
    } else {
      id_anterior.value = null
      carrera_anterior.value = {}
    }
  }

  const getCarrerasPreviasPostulacion = async () => {
    const response = await axios.get('/carreras-previas/' + formState.dni)
    carreras_previas.value = response.data.datos
  }

  // ── UI helpers ────────────────────────────────────────────
  const notificacion = (type, titulo, mensaje) => {
    notification[type]({ message: titulo, description: mensaje })
  }

  const cambiarFormato = () => {
    const fecha = datospersonales.fec_nacimiento.$d
    const formattedDate = format(fecha, "dd 'de' MMMM 'del' yyyy", { locale: es })
    temp_date.value = formattedDate
  }

  const getColegioSeleccionado = () => {
    college.value = colegios.value.find(item => item.value === datoscolegio.id_colegio)
  }

  const getDocs = async () => {
    if (datospersonales.tipo_doc === 1) {
      window.open('/pdf-solicitud/' + PROCESO() + '/' + formState.dni, '_blank')
    } else {
      window.open('/pdf-solicitud-extranjeros/' + PROCESO() + '/' + formState.dni, '_blank')
    }
  }

  const irDiagnostico = async () => {
    if (datospersonales.tipo_doc !== 1) return
    try {
      const response = await axios.get(
        'https://admision.unap.edu.pe/login-postulante?dni=',
        { params: { dni: formState.dni }, headers: { 'Accept': 'application/json' } }
      )
      if (response.data?.url) {
        window.open(response.data.url)
      } else if (typeof response.data === 'string' && response.data.startsWith('http')) {
        window.open(response.data, '_blank')
      } else {
        console.error('Respuesta inesperada:', response.data)
      }
    } catch (error) {
      console.error('Error:', error)
      const fallbackUrl = `https://admision.unap.edu.pe/login-postulante?dni=${formState.dni}`
      window.open(fallbackUrl, '_blank')
    }
  }

  const descargaReglamento = async () => {
    try {
      const response = await axios.get('/descargar-reglamento/' + PROCESO(), { responseType: 'blob' })
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

  // ── Form ref setters ──────────────────────────────────────
  const setFormRef = (ref) => { formRef.value = ref }
  const setFormDatosPersonales = (ref) => { formDatosPersonales.value = ref }
  const setFormDatosResidencia = (ref) => { formDatosResidencia.value = ref }
  const setFormDatosColegio = (ref) => { formDatosColegio.value = ref }
  const setFormDatosPadre = (ref) => { formDatosPadre.value = ref }
  const setFormDatosMadre = (ref) => { formDatosMadre.value = ref }
  const setFormDatosTransversales = (ref) => { formDatosTransversales.value = ref }
  const setFormPreinscripcion = (ref) => { formPreinscripcion.value = ref }

  // ── Watchers ───────────────────────────────────────────────
  watch(buscarDep, () => { getDepartamentos() })
  watch(buscarDepC, () => { getDepartamentosColegio() })
  watch(buscarProv, () => { getProvincias() })
  watch(buscarProvC, () => { getProvinciasColegio() })

  watch(() => datospadre.dni, (newValue) => {
    if (newValue && newValue.length == 8) { getApoderadoDNI(1) }
  })

  watch(() => datosmadre.dni, (newValue) => {
    if (newValue && newValue.length == 8) { getApoderadoDNI(2) }
  })

  watch(() => datos_preinscripcion.tipo_certificado, (newValue) => {
    if (newValue === 'CERTIFICADO AMARILLO') { activeKey.value = '1' }
    if (newValue === 'CERTIFICADO BLANCO') { activeKey.value = '2' }
    if (newValue === 'CONSTANCIA DE ESTUDIOS') { activeKey.value = '3' }
    ejemplo.value = true
  })

  watch(() => formState.dni, (newValue) => {
    if (newValue && newValue.length == 8) {
      // Reset verification state — verification runs only when user clicks "Iniciar Postulación"
      ultimopaso.value = null
      pendingStep.value = null
      loading.value = false
      if (selectedItems) { selectedItems.value = [] }
      datacepre.value = []
      anteriores.value = []
    }
  })

  watch(() => datos_preinscripcion.modalidad, () => {
    datos_preinscripcion.programa = null
    getProgramas()
  })

  watch(() => datos_preinscripcion.observacion, (newValue) => {
    datos_preinscripcion.programa = null
    if (newValue && (newValue.length == 6 || newValue.length == 0)) {
      getAreaCodigo()
    }
  })

  watch(pagina_pre, (newValue) => {
    if (pagina_pre.value === 2) {
      getDatosPersonales2()
      getDepartamentos()
    }
    if (newValue === 3) {
      getDepartamentosColegio()
      getUbigeo()
    }
    if (newValue === 4) {
      getApoderado()
    }
    if (newValue === 5) {
      if (datospersonales.id) {
        getApoderadoM()
      }
    }
    if (newValue === 6) {
      getCondicionesLengua()
      getOptionsPertenenciaCultural()
      getLenguasIndigenas()
      getPueblosIndigenas()
      getInformacionAdicional()
    }
    if (newValue === 7) {
      getModalidades()
    }
  })

  // ── Init ──────────────────────────────────────────────────
  getCodigoAleatorio()

  // ── Return ─────────────────────────────────────────────────
  return {
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
    modalcepreaviso,
    modalUbigeo,
    ejemplo,
    modalDatos,
    presionado,
    participa,
    postulante_inscrito,
    sancionado,
    postcepre,
    confirmacion,
    codigo_aleatorio,
    ultimopaso,
    nombrecolegiox,
    temp_date,
    temp_area,
    college,
    participante,
    datacepre,
    traslado_interno,
    id_anterior,
    carrera_anterior,
    bottom,
    carreras_previas,
    anteriores,
    modalCargarDatos,
    datosPrevios,
    pendingStep,

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

    // Selection refs
    depseleccionado,
    depseleccionadoC,
    provseleccionada,
    provseleccionadaC,
    distseleccionado,
    distseleccionadoC,

    // Cultural data lists
    condiciones_lengua,
    opciones_pertenencia_cultural,
    lenguas_indigenas,
    pueblos_indigenas,

    // Constants
    tipo_docs,
    estados_civil,
    sexos,
    bio,
    ing,
    soc,

    // Form refs
    formRef,
    formDatosPersonales,
    formDatosResidencia,
    formDatosColegio,
    formDatosPadre,
    formDatosMadre,
    formDatosTransversales,
    formPreinscripcion,

    // Input sanitizers
    dniInput,
    ubigeoInput,
    nombresInput,
    pimerapellidoInput,
    celularInput,

    // Validators
    validateFechaNacimiento,
    validateCelular,
    validateCorreo,
    validateCodigoSecreto,

    // Navigation
    next,
    prev,
    abrirModalDatos,
    cancelarInscripcion,
    desactivar,

    // Step tracking
    savePasos,
    getPasos,
    getPasoRegistrado,

    // Data loading
    getDatosPersonales,
    getDatosPersonales2,
    getCodigoAleatorio,
    getModalidades,
    getProgramas,
    getProgramasArea,
    getAreaCodigo,

    // Save methods
    saveDNI,
    saveDatosPersonales,
    saveDatosResidencia,
    savecolegio,
    saveApoderado,
    saveApoderadoM,
    saveAdditionalData,
    submit,

    // Ubigeo helpers
    getDepartamentos,
    getProvincias,
    getDistritos,
    getDepartamentosColegio,
    getProvinciasColegio,
    getDistritosColegio,
    getColegios,
    getUbigeo,
    onSelectDepartamentos,
    onSelectProvincias,
    onSelectDistritos,
    onSelectDepartamentosC,
    onSelectProvinciasC,
    onSelectDistritosC,

    // Ubigeo nacimiento
    departamentosNac,
    provinciasNac,
    distritosNac,
    buscarDepNac,
    buscarProvNac,
    buscarDistNac,
    depseleccionadoNac,
    provseleccionadaNac,
    distseleccionadoNac,
    getDepartamentosNac,
    getProvinciasNac,
    getDistritosNac,
    onSelectDepartamentosNac,
    onSelectProvinciasNac,
    onSelectDistritosNac,

    // Unified ubigeo search
    ubigeoNacOptions,
    ubigeoNacSeleccionado,
    ubigeoResOptions,
    ubigeoResSeleccionado,
    getUbigeosNac,
    onSelectUbigeoNac,
    getUbigeosRes,
    onSelectUbigeoRes,

    // Unified ubigeo search (colegio)
    ubigeoColeOptions,
    ubigeoColeSeleccionado,
    getUbigeosCole,
    onSelectUbigeoCole,
    onSelectColegio,

    // Apoderado helpers
    getApoderado,
    getApoderadoM,
    getApoderadoDNI,

    // Cultural data helpers
    getCondicionesLengua,
    getOptionsPertenenciaCultural,
    getLenguasIndigenas,
    getPueblosIndigenas,
    getInformacionAdicional,

    // Carreras previas
    getDataPrisma,
    getCarrerasPrevias,
    getCarrerasPreviasPostulacion,
    registrarPrevias,
    toggleSelection,
    toggleSelection2,
    selectedItems,

    // Verification
    getSancionado,
    getParticipanteCepre,
    consultaInscripcion,
    consultaInscripcion2,
    verificarDatosExistentes,
    aceptarCargarDatos,
    rechazarCargarDatos,
    iniciarPostulacion,

    // Code verification
    enviandoCodigo,
    verificandoCodigo,
    codigoVerificacion,
    codigoEnviado,
    codigoExpirado,
    codigoError,
    emailMasked,
    countdownSegundos,
    solicitarCodigoVerificacion,
    verificarCodigoYCargar,
    resetCodigoVerificacion,
    setCodigoVerificacion,
    setCodigoError,

    // External API
    getDatosApi,
    getPadreApi,
    getMadreApi,

    // UI helpers
    notificacion,
    cambiarFormato,
    getColegioSeleccionado,
    getDocs,
    irDiagnostico,
    descargaReglamento,

    // Form ref setters
    setFormRef,
    setFormDatosPersonales,
    setFormDatosResidencia,
    setFormDatosColegio,
    setFormDatosPadre,
    setFormDatosMadre,
    setFormDatosTransversales,
    setFormPreinscripcion,
  }
}
