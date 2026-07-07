import { reactive, ref, watch } from 'vue'
import { notification } from 'ant-design-vue'
import dayjs from 'dayjs'
import { format } from 'date-fns'
import axios from 'axios'

const API_PERU_TOKEN = '70ab1cd1b9b452982370381fd0be605c85ddc8795aed972afca143fee05fde43'

const SI_LENGUA_ID = '820ddc3b-dad9-4fce-8d58-942d1c46c4ab'
const SI_PUEBLO_ID = '47de46ba-be45-41a6-a542-6f88fcd4653c'

export const useRegistroPostulanteAdmin = () => {

  // ── Form refs ──────────────────────────────────────────────
  const formPersonal = ref()
  const formResidencia = ref()
  const formColegio = ref()
  const formPadre = ref()
  const formMadre = ref()
  const formAdicional = ref()
  const formPostulacion = ref()

  // ── Postulante ─────────────────────────────────────────────
  const datospersonales = reactive({
    id: null,
    tipo_doc: 1,
    nro_doc: '',
    primer_apellido: '',
    segundo_apellido: '',
    apellido_casada: '',
    nombres: '',
    sexo: null,
    estado_civil: null,
    fec_nacimiento: '',
    correo: '',
    celular: '',
    ubigeo_nacimiento: '',
    ubigeo_residencia: '',
    direccion: '',
    anio_egreso: null,
    id_colegio: null,
    observaciones: '',
  })

  // ── Residencia ─────────────────────────────────────────────
  const datosresidencia = reactive({
    pais: 125,
    ubigeo_res: null,
    direccion: '',
    dep: null,
    prov: null,
    dist: null,
  })

  // ── Colegio ────────────────────────────────────────────────
  const datoscolegio = reactive({
    id_colegio: null,
    egreso: null,
    ubigeo_cole: null,
  })

  // ── Apoderados ─────────────────────────────────────────────
  const datospadre = reactive({
    id: null,
    tipo_apoderado: 1,
    dni: '',
    nombres: '',
    paterno: '',
    materno: '',
  })

  const datosmadre = reactive({
    id: null,
    tipo_apoderado: 2,
    dni: '',
    nombres: '',
    paterno: '',
    materno: '',
  })

  // ── Datos adicionales / identidad cultural ─────────────────
  const datos_transversales = reactive({
    discapacidad: null,
    tipo_discapacidad: '',
    tipo_discapacidad_otro: '',
    id_condicion_lengua: null,
    id_lengua_indigena: null,
    id_pertenencia_cultural: null,
    id_pueblo_indigena: null,
  })

  // ── Postulación ────────────────────────────────────────────
  const datos_preinscripcion = reactive({
    modalidad: null,
    programa: null,
    tipo_certificado: null,
    codigo_certificado: null,
    codigo_medico: null,
    observacion: null,
  })

  // ── UI state ──────────────────────────────────────────────
  const loading = ref(false)
  const guardando = ref(false)
  const buscando = ref(false)
  const procesoSeleccionado = ref(null)
  const esEdicion = ref(false)

  // ── Data lists ────────────────────────────────────────────
  const procesos = ref([])
  const modalidades = ref([])
  const programas = ref([])
  const colegios = ref([])

  // ── Ubigeo search ─────────────────────────────────────────
  const ubigeoNacOptions = ref([])
  const ubigeoNacSeleccionado = ref(null)
  const ubigeoResOptions = ref([])
  const ubigeoResSeleccionado = ref(null)
  const ubigeoColeOptions = ref([])
  const ubigeoColeSeleccionado = ref(null)

  // ── Cultural data lists ────────────────────────────────────
  const condiciones_lengua = ref([])
  const opciones_pertenencia_cultural = ref([])
  const lenguas_indigenas = ref([])
  const pueblos_indigenas = ref([])

  // ── Constants ──────────────────────────────────────────────
  const tipo_docs = [
    { value: 1, label: 'DNI' },
    { value: 3, label: 'Carné Ext.' },
  ]
  const estados_civil = [
    { value: 1, label: 'Soltero' },
    { value: 2, label: 'Casado' },
    { value: 3, label: 'Viudo' },
    { value: 4, label: 'Divorciado' },
  ]
  const sexos = [
    { value: '1', label: 'Masculino' },
    { value: '2', label: 'Femenino' },
  ]
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

  const aniosEgreso = (() => {
    const current = new Date().getFullYear()
    const years = []
    for (let y = current; y >= current - 10; y--) years.push(y)
    return years
  })()

  // ── Helpers ────────────────────────────────────────────────
  const notificacion = (type, titulo, mensaje) => {
    notification[type]({ message: titulo, description: mensaje })
  }

  const PROCESO = () => procesoSeleccionado.value

  // ── Validators ──────────────────────────────────────────────
  const validateCelular = (rule, value) => {
    return new Promise((resolve, reject) => {
      if (!value) return reject(new Error('Ingrese el número de celular.'))
      if (value.length < 9) return reject(new Error('Mínimo 9 dígitos.'))
      axios.post('/existe-celular', { celular: value, dni: datospersonales.nro_doc })
        .then(r => r.data ? reject(new Error('Celular ya registrado.')) : resolve())
        .catch(() => reject(new Error('Error al verificar celular.')))
    })
  }

  const validateCorreo = (rule, value) => {
    return new Promise((resolve, reject) => {
      if (!value) return reject(new Error('Ingrese el correo.'))
      axios.post('/existe-correo', { email: value, dni: datospersonales.nro_doc })
        .then(r => r.data ? reject(new Error('Correo ya registrado.')) : resolve())
        .catch(() => reject(new Error('Error al verificar correo.')))
    })
  }

  // ── Data loading: procesos ──────────────────────────────────
  const loadProcesos = async () => {
    try {
      const res = await axios.get('/admin/configuracion-citacion/procesos')
      procesos.value = res.data.datos || []
    } catch {
      try {
        const res = await axios.get('/admin/get-select-procesos')
        procesos.value = res.data.datos || []
      } catch { procesos.value = [] }
    }
  }

  // ── DNI auto-complete (apiperu.dev) ─────────────────────────
  const buscarDniApi = async (dni, target) => {
    if (!dni || dni.length !== 8) return
    try {
      const res = await axios.get(`https://apiperu.dev/api/dni/${dni}`, {
        headers: { Accept: 'application/json', Authorization: `Bearer ${API_PERU_TOKEN}` },
      })
      if (res.data?.data) {
        const d = res.data.data
        if (target === 'postulante') {
          datospersonales.primer_apellido = d.apellido_paterno || ''
          datospersonales.segundo_apellido = d.apellido_materno || ''
          datospersonales.nombres = d.nombres || ''
        } else if (target === 'padre') {
          datospadre.paterno = d.apellido_paterno || ''
          datospadre.materno = d.apellido_materno || ''
          datospadre.nombres = d.nombres || ''
        } else if (target === 'madre') {
          datosmadre.paterno = d.apellido_paterno || ''
          datosmadre.materno = d.apellido_materno || ''
          datosmadre.nombres = d.nombres || ''
        }
      }
    } catch (e) { /* silent — admin can fill manually */ }
  }

  // ── Ubigeo search ──────────────────────────────────────────
  let ubigeoSearchTimer = null

  const getUbigeos = async (term, optionsRef) => {
    if (ubigeoSearchTimer) clearTimeout(ubigeoSearchTimer)
    ubigeoSearchTimer = setTimeout(async () => {
      try {
        const res = await axios.post('/get-ubigeo', { term: term || '' })
        const opts = res.data.datos?.data || []
        optionsRef.value = opts.slice(0, 10)
      } catch (e) {
        console.error('Error al buscar ubigeo:', e)
      }
    }, 250)
  }

  const onSelectUbigeoNac = (value) => {
    ubigeoNacSeleccionado.value = value
    datospersonales.ubigeo_nacimiento = value
  }

  const onSelectUbigeoRes = (value) => {
    ubigeoResSeleccionado.value = value
    datospersonales.ubigeo_residencia = value
    datosresidencia.ubigeo_res = value
    const opt = ubigeoResOptions.value.find(o => o.key === value)
    if (opt) {
      const parts = opt.value.split('-')
      if (parts.length > 1) {
        const names = parts[1].split('/')
        datosresidencia.dep = names[0] || null
        datosresidencia.prov = names[1] || null
        datosresidencia.dist = names[2] || null
      }
    }
  }

  const onSelectUbigeoCole = (value) => {
    ubigeoColeSeleccionado.value = value
    datoscolegio.ubigeo_cole = value
    colegios.value = []
    datoscolegio.id_colegio = null
    if (value) loadColegios()
  }

  // ── Colegios ───────────────────────────────────────────────
  const loadColegios = async () => {
    if (!ubigeoColeSeleccionado.value) return
    const res = await axios.post('/get-colegio-distrito', { ubigeo_cole: ubigeoColeSeleccionado.value })
    colegios.value = res.data.datos || []
  }

  // ── Modalidades & programas ────────────────────────────────
  const loadModalidades = async () => {
    if (!procesoSeleccionado.value) return
    const res = await axios.get('/get-select-modalidad-proceso/' + procesoSeleccionado.value)
    modalidades.value = res.data.datos || []
  }

  const loadProgramas = async () => {
    if (!procesoSeleccionado.value || !datos_preinscripcion.modalidad) return
    const res = await axios.post('/get-select-programas-proceso', {
      id_modalidad: datos_preinscripcion.modalidad,
      id_proceso: procesoSeleccionado.value,
    })
    programas.value = res.data.datos || []
  }

  // ── Cultural data loading ───────────────────────────────────
  const loadCulturalData = async () => {
    try {
      const [c, p, l, pi] = await Promise.all([
        axios.get('/admin/get-condiciones-lengua-segundas'),
        axios.get('/admin/get-pertenencia-cultural-segundas'),
        axios.get('/admin/get-lengua-segundas'),
        axios.get('/admin/get-pueblos-indigenes-segundas'),
      ])
      condiciones_lengua.value = c.data || []
      opciones_pertenencia_cultural.value = p.data || []
      lenguas_indigenas.value = l.data || []
      pueblos_indigenas.value = pi.data || []
    } catch (e) {
      console.error('Error al cargar datos culturales:', e)
    }
  }

  // ── Load existing postulante by DNI ────────────────────────
  const loadByDni = async () => {
    if (!datospersonales.nro_doc || datospersonales.nro_doc.length < 8) {
      notificacion('warning', 'DNI requerido', 'Ingrese un DNI válido (8 dígitos).')
      return
    }
    if (!procesoSeleccionado.value) {
      notificacion('warning', 'Proceso requerido', 'Seleccione un proceso primero.')
      return
    }

    buscando.value = true
    try {
      const res = await axios.post('/get-postulante-datos-personales2', { nro_doc: datospersonales.nro_doc })
      if (res.data.datos && res.data.datos.length > 0) {
        const d = res.data.datos[0]
        esEdicion.value = true
        datospersonales.id = d.id
        datospersonales.tipo_doc = d.tipo_doc || 1
        datospersonales.nro_doc = d.nro_doc
        datospersonales.primer_apellido = d.primer_apellido || ''
        datospersonales.segundo_apellido = d.segundo_apellido || ''
        datospersonales.apellido_casada = d.apellido_casada || ''
        datospersonales.nombres = d.nombres || ''
        datospersonales.sexo = d.sexo
        datospersonales.estado_civil = d.estado_civil
        datospersonales.correo = d.correo || ''
        datospersonales.celular = d.celular || ''
        datospersonales.fec_nacimiento = d.fec_nacimiento ? dayjs(d.fec_nacimiento) : ''
        datospersonales.ubigeo_nacimiento = d.ubigeo || ''
        datospersonales.ubigeo_residencia = d.ubigeo_residencia || ''
        datospersonales.direccion = d.direccion || ''
        datospersonales.anio_egreso = d.egreso || null
        datospersonales.id_colegio = d.id_colegio || null
        datospersonales.observaciones = d.observaciones || ''

        // Ubigeo selects
        if (d.ubigeo) {
          ubigeoNacSeleccionado.value = d.ubigeo
          ubigeoNacOptions.value = d.nacimiento
            ? [{ key: d.ubigeo, value: d.nacimiento }]
            : []
        }
        if (d.ubigeo_residencia) {
          ubigeoResSeleccionado.value = d.ubigeo_residencia
          ubigeoResOptions.value = d.residencia
            ? [{ key: d.ubigeo_residencia, value: d.residencia }]
            : []
          datosresidencia.ubigeo_res = d.ubigeo_residencia
          if (d.residencia) {
            const parts = d.residencia.split('-')
            if (parts.length > 1) {
              const names = parts[1].split('/')
              datosresidencia.dep = names[0] || null
              datosresidencia.prov = names[1] || null
              datosresidencia.dist = names[2] || null
            }
          }
        }

        // Load apoderados
        await loadApoderados()

        // Load identidad cultural
        await loadIdentidadCultural()

        // Load colegio ubigeo via postulante
        if (datospersonales.id) {
          try {
            const coleRes = await axios.post('/get-ubigeo-colegio', { id_postulante: datospersonales.id })
            if (Array.isArray(coleRes.data) && coleRes.data.length > 0) {
              const cole = coleRes.data[0]
              ubigeoColeSeleccionado.value = cole.ubigeo
              datoscolegio.ubigeo_cole = cole.ubigeo
              datoscolegio.egreso = cole.egreso || null
              if (cole.egreso) datospersonales.anio_egreso = cole.egreso
              if (cole.value) datospersonales.id_colegio = cole.value
              ubigeoColeOptions.value = cole.departamento
                ? [{ key: cole.ubigeo, value: `${cole.ubigeo}-${cole.departamento}/${cole.provincia}/${cole.distrito}` }]
                : []
              await loadColegios()
            }
          } catch { /* ignore */ }
        }

        // Load modalidades
        await loadModalidades()

        notificacion('info', 'Postulante encontrado', 'Datos cargados. Puede editar y guardar los cambios.')
      } else {
        // Not found — try API Peru
        await buscarDniApi(datospersonales.nro_doc, 'postulante')
        esEdicion.value = false
        if (datospersonales.nombres) {
          notificacion('info', 'DNI no registrado', 'Datos traídos de RENIEC. Complete el formulario.')
        } else {
          notificacion('info', 'DNI no registrado', 'Postulante nuevo. Complete el formulario.')
        }
      }
    } catch (e) {
      console.error('Error al buscar postulante:', e)
      notificacion('error', 'Error', 'No se pudo buscar el postulante.')
    } finally {
      buscando.value = false
    }
  }

  const loadApoderados = async () => {
    if (!datospersonales.id) return
    try {
      const [resP, resM] = await Promise.all([
        axios.post('/get-apoderado', { id_postulante: datospersonales.id, tipo: 1 }),
        axios.post('/get-apoderado', { id_postulante: datospersonales.id, tipo: 2 }),
      ])
      if (resP.data.datos?.length > 0) {
        const d = resP.data.datos[0]
        datospadre.id = d.id
        datospadre.dni = d.nro_documento
        datospadre.nombres = d.nombres
        datospadre.paterno = d.paterno
        datospadre.materno = d.materno
      }
      if (resM.data.datos?.length > 0) {
        const d = resM.data.datos[0]
        datosmadre.id = d.id
        datosmadre.dni = d.nro_documento
        datosmadre.nombres = d.nombres
        datosmadre.paterno = d.paterno
        datosmadre.materno = d.materno
      }
    } catch (e) {
      console.error('Error al cargar apoderados:', e)
    }
  }

  const loadIdentidadCultural = async () => {
    if (!datospersonales.id || !procesoSeleccionado.value) return
    try {
      const res = await axios.get(`/admin/get-identidad-cultural/${datospersonales.id}/${procesoSeleccionado.value}`)
      if (res.data) {
        datos_transversales.discapacidad = res.data.discapacidad
        datos_transversales.tipo_discapacidad = res.data.tipo_discapacidad
        datos_transversales.tipo_discapacidad_otro = res.data.tipo_discapacidad_otro || ''
        datos_transversales.id_condicion_lengua = res.data.id_condicion_lengua
        datos_transversales.id_lengua_indigena = res.data.id_lengua_indigena
        datos_transversales.id_pertenencia_cultural = res.data.id_pertenencia_cultural
        datos_transversales.id_pueblo_indigena = res.data.id_pueblo_indigena
      }
    } catch (e) {
      // 404 is fine — no existing cultural data
    }
  }

  // ── Save methods ───────────────────────────────────────────
  const savePersonal = async () => {
    const fecNac = datospersonales.fec_nacimiento
      ? format(new Date(datospersonales.fec_nacimiento), 'yyyy-MM-dd')
      : null
    const res = await axios.post('/admin/save-postulante-admin', {
      id: datospersonales.id,
      tipo_doc: datospersonales.tipo_doc,
      nro_doc: datospersonales.nro_doc,
      primer_apellido: datospersonales.primer_apellido,
      segundo_apellido: datospersonales.segundo_apellido,
      apellido_casada: datospersonales.apellido_casada,
      nombres: datospersonales.nombres,
      sexo: datospersonales.sexo,
      fec_nacimiento: fecNac,
      ubigeo_nacimiento: datospersonales.ubigeo_nacimiento,
      ubigeo_residencia: datospersonales.ubigeo_residencia,
      celular: datospersonales.celular,
      correo: datospersonales.correo,
      estado_civil: datospersonales.estado_civil,
      direccion: datospersonales.direccion,
      egreso: datospersonales.anio_egreso,
      observaciones: datospersonales.observaciones,
      colegio: datospersonales.id_colegio,
    })
    if (res.data.estado === true && res.data.datos?.id) {
      datospersonales.id = res.data.datos.id
    }
    return res.data
  }

  const saveResidencia = async () => {
    if (!datospersonales.id) return
    const res = await axios.post('/save-postulante-residencia', {
      id: datospersonales.id,
      ubigeo_residencia: datospersonales.ubigeo_residencia,
      direccion: datosresidencia.direccion || datospersonales.direccion,
      pais: datosresidencia.pais,
    })
    return res.data
  }

  const saveColegio = async () => {
    if (!datospersonales.id) return
    const res = await axios.post('/save-postulante-colegio', {
      id: datospersonales.id,
      anio_egreso: datospersonales.anio_egreso,
      colegio: datospersonales.id_colegio,
      proceso: procesoSeleccionado.value,
    })
    return res.data
  }

  const saveApoderado = async (data) => {
    if (!datospersonales.id) return
    const res = await axios.post('/save-postulante-apoderado', {
      id: data.id,
      tipo_apoderado: data.tipo_apoderado,
      dni: data.dni,
      nombres: data.nombres,
      paterno: data.paterno,
      materno: data.materno,
      id_postulante: datospersonales.id,
    })
    if (res.data.estado === true && res.data.datos?.id) {
      data.id = res.data.datos.id
    }
    return res.data
  }

  const saveAdicional = async () => {
    if (!datospersonales.id || !procesoSeleccionado.value) return
    const res = await axios.post('/admin/save-postulante-adicional', {
      id_postulante: datospersonales.id,
      id_proceso: procesoSeleccionado.value,
      discapacidad: datos_transversales.discapacidad,
      tipo_discapacidad: datos_transversales.tipo_discapacidad,
      tipo_discapacidad_otro: datos_transversales.tipo_discapacidad_otro,
      id_condicion_lengua: datos_transversales.id_condicion_lengua,
      id_lengua_indigena: datos_transversales.id_lengua_indigena,
      id_pertenencia_cultural: datos_transversales.id_pertenencia_cultural,
      id_pueblo_indigena: datos_transversales.id_pueblo_indigena,
    })
    return res.data
  }

  const savePreinscripcion = async () => {
    if (!datospersonales.id || !procesoSeleccionado.value) return
    const fd = new FormData()
    fd.append('dni', datospersonales.nro_doc)
    fd.append('modalidad', datos_preinscripcion.modalidad)
    fd.append('programa', datos_preinscripcion.programa)
    fd.append('tipo_certificado', datos_preinscripcion.tipo_certificado)
    fd.append('codigo_certificado', datos_preinscripcion.codigo_certificado)
    fd.append('codigo_medico', datos_preinscripcion.codigo_medico)
    fd.append('observacion', datos_preinscripcion.observacion)
    fd.append('id_postulante', datospersonales.id)
    fd.append('id_proceso', procesoSeleccionado.value)
    fd.append('id_anterior', 'null')
    const res = await axios.post('/save-pre-inscripcion', fd)
    return res.data
  }

  const savePasos = async (nombre, nro, avance) => {
    if (!datospersonales.id || !procesoSeleccionado.value) return
    await axios.post('/save-pasos-preinscripcion', {
      id: null,
      nombre,
      nro,
      avance,
      postulante: datospersonales.id,
      proceso: procesoSeleccionado.value,
    })
  }

  // ── Save all (sequential) ──────────────────────────────────
  const saveAll = async () => {
    if (!procesoSeleccionado.value) {
      notificacion('warning', 'Proceso requerido', 'Seleccione un proceso antes de guardar.')
      return
    }
    if (!datospersonales.nro_doc || !datospersonales.nombres || !datospersonales.primer_apellido) {
      notificacion('warning', 'Datos incompletos', 'Complete los campos obligatorios de datos personales.')
      return
    }

    guardando.value = true
    const errores = []

    try {
      // 1. Datos personales
      const r1 = await savePersonal()
      if (!r1.estado) errores.push('Datos personales')

      // 2. Residencia
      if (datospersonales.ubigeo_residencia) {
        try { await saveResidencia() } catch { errores.push('Residencia') }
      }

      // 3. Colegio
      if (datospersonales.anio_egreso || datospersonales.id_colegio) {
        try { await saveColegio() } catch { errores.push('Colegio') }
      }

      // 4. Padre
      if (datospadre.dni || datospadre.nombres) {
        try { await saveApoderado(datospadre) } catch { errores.push('Padre') }
      }

      // 5. Madre
      if (datosmadre.dni || datosmadre.nombres) {
        try { await saveApoderado(datosmadre) } catch { errores.push('Madre') }
      }

      // 6. Datos adicionales
      if (datos_transversales.discapacidad !== null) {
        try { await saveAdicional() } catch { errores.push('Datos adicionales') }
      }

      // 7. Postulación
      if (datos_preinscripcion.modalidad && datos_preinscripcion.programa) {
        try { await savePreinscripcion() } catch { errores.push('Postulación') }
      }

      // 8. Registrar pasos
      const pasos = [
        ['Registro de datos personales', 1, 16],
        ['Registro de datos de residencia', 2, 32],
        ['Registro de datos del colegio', 3, 48],
        ['Registro de datos del padre', 4, 65],
        ['Registro de datos de la madre', 5, 80],
        ['Registro de datos adicionales', 6, 85],
        ['Registro de datos de postulación', 7, 110],
      ]
      for (const [nombre, nro, avance] of pasos) {
        try { await savePasos(nombre, nro, avance) } catch { /* paso ya registrado */ }
      }

      if (errores.length === 0) {
        notificacion('success', 'Registro exitoso', `${datospersonales.nombres} ${datospersonales.primer_apellido} registrado correctamente.`)
        limpiar()
      } else {
        notificacion('warning', 'Registro parcial', `Se guardó con errores en: ${errores.join(', ')}.`)
      }
    } catch (e) {
      console.error('Error en saveAll:', e)
      notificacion('error', 'Error', e.response?.data?.mensaje || 'No se pudo completar el registro.')
    } finally {
      guardando.value = false
    }
  }

  // ── Limpiar ────────────────────────────────────────────────
  const limpiar = () => {
    Object.assign(datospersonales, {
      id: null, tipo_doc: 1, nro_doc: '', primer_apellido: '', segundo_apellido: '',
      apellido_casada: '', nombres: '', sexo: null, estado_civil: null,
      fec_nacimiento: '', correo: '', celular: '', ubigeo_nacimiento: '',
      ubigeo_residencia: '', direccion: '', anio_egreso: null, id_colegio: null, observaciones: '',
    })
    Object.assign(datosresidencia, { pais: 125, ubigeo_res: null, direccion: '', dep: null, prov: null, dist: null })
    Object.assign(datoscolegio, { id_colegio: null, egreso: null, ubigeo_cole: null })
    Object.assign(datospadre, { id: null, tipo_apoderado: 1, dni: '', nombres: '', paterno: '', materno: '' })
    Object.assign(datosmadre, { id: null, tipo_apoderado: 2, dni: '', nombres: '', paterno: '', materno: '' })
    Object.assign(datos_transversales, {
      discapacidad: null, tipo_discapacidad: '', tipo_discapacidad_otro: '',
      id_condicion_lengua: null, id_lengua_indigena: null, id_pertenencia_cultural: null, id_pueblo_indigena: null,
    })
    Object.assign(datos_preinscripcion, {
      modalidad: null, programa: null, tipo_certificado: null, codigo_certificado: null,
      codigo_medico: null, observacion: null,
    })
    ubigeoNacSeleccionado.value = null
    ubigeoResSeleccionado.value = null
    ubigeoColeSeleccionado.value = null
    ubigeoNacOptions.value = []
    ubigeoResOptions.value = []
    ubigeoColeOptions.value = []
    colegios.value = []
    modalidades.value = []
    programas.value = []
    esEdicion.value = false
  }

  // ── Watchers ───────────────────────────────────────────────
  watch(() => datospersonales.nro_doc, (val) => {
    if (val && val.length === 8 && !esEdicion.value) {
      buscarDniApi(val, 'postulante')
    }
  })

  watch(() => datospadre.dni, (val) => {
    if (val && val.length === 8) buscarDniApi(val, 'padre')
  })

  watch(() => datosmadre.dni, (val) => {
    if (val && val.length === 8) buscarDniApi(val, 'madre')
  })

  watch(() => datos_preinscripcion.modalidad, () => {
    datos_preinscripcion.programa = null
    if (datos_preinscripcion.modalidad && procesoSeleccionado.value) {
      loadProgramas()
    }
  })

  watch(() => procesoSeleccionado.value, () => {
    if (procesoSeleccionado.value) {
      loadModalidades()
    }
  })

  return {
    // Form refs
    formPersonal, formResidencia, formColegio,
    formPadre, formMadre, formAdicional, formPostulacion,

    // State
    datospersonales, datosresidencia, datoscolegio,
    datospadre, datosmadre, datos_transversales, datos_preinscripcion,

    // UI
    loading, guardando, buscando, procesoSeleccionado, esEdicion,
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
    loadProcesos, loadByDni, loadModalidades, loadProgramas,
    loadCulturalData, buscarDniApi,

    // Ubigeo
    getUbigeos, onSelectUbigeoNac, onSelectUbigeoRes, onSelectUbigeoCole,
    loadColegios,

    // Save
    saveAll, limpiar,
  }
}
