<?php
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostulanteRegistroController;
use App\Http\Controllers\PostulanteResultadoController;
use App\Http\Controllers\SimulacroController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\HuellaController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\VacantesController;
use App\Http\Controllers\TarifaController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\ProcesoController;
use App\Http\Controllers\FilialController;
use App\Http\Controllers\VerificacionFotosController;
use App\Http\Controllers\ModalidadController;
use App\Http\Controllers\AnioController;
use App\Http\Controllers\FirmaController;
use App\Http\Controllers\ApoderadoController;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\PostulanteController;
use App\Http\Controllers\PreinscripcionController;
use App\Http\Controllers\SeleccionDataController;
use App\Http\Controllers\ColegioController;
use App\Http\Controllers\PreguntaController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\DetalleExamenVocacionalController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\PostulanteDocumentoController;
use App\Http\Controllers\FirebaseConfigController;
use App\Http\Controllers\FotoController;
use App\Http\Controllers\PagoSimulacroController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\AdministrativoController;
use App\Http\Controllers\ReniecController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PruebasController;
use App\Http\Controllers\ResultadosController;
use App\Http\Controllers\CarpetaController;
use App\Http\Controllers\CertificadoFirmaController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\PonderacionController;
use App\Http\Controllers\AsignaturaController;
use App\Http\Controllers\ExcepcionesController;
use App\Http\Controllers\ProgramaProcesoController;
use App\Http\Controllers\SancionadoController;
use App\Http\Controllers\CepreController;
use App\Http\Controllers\PagosController;
use App\Http\Controllers\ValidacionController;
use App\Http\Controllers\PagoBancoController;
use App\Http\Controllers\CertificadoController;
use App\Http\Controllers\DocumentosResultadoController;
use App\Http\Controllers\PuntajeController;
use App\Http\Controllers\ControlBiometricoController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\DniController;
use App\Http\Controllers\DocumentoSegundaController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\ReglamentoController;
use App\Http\Controllers\RatioController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\RequisitoDocumentoController;
use App\Http\Controllers\TipoDocumentoController;
use App\Http\Controllers\RevisorDashboardController;
use App\Http\Controllers\RevisorPersonalController;
use App\Http\Controllers\RevisorNotificationController;
use App\Http\Controllers\PostulanteNotificationController;
use App\Http\Controllers\RevisorDocumentoController;
use App\Http\Controllers\ConfiguracionCitacionController;
use App\Http\Controllers\AdminDocumentoController;
use App\Http\Controllers\CarrerasPreviasController;
use App\Http\Controllers\ResumenBiometricoController;
use App\Http\Controllers\ResumenInscripcionesController;
use App\Http\Controllers\DescargarArchivosController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\Admin\SmtpAccountController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Segundas\IdentidadSegundaController;
use App\Http\Controllers\Segundas\PostulanteSegundaController;
use Inertia\Inertia;

Route::middleware('auth')->get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return redirect()->route('admin-dashboard');
})->middleware(['auth', 'verified','revisor','admin'])->name('dashboard');

Route::get('/preinscripcion/verification-setting', [SettingController::class, 'getPreinscripcionEmailVerification']);

Route::middleware('auth')->group(function () {
    Route::post('/fcm-token', [PostulanteDocumentoController::class, 'registrarFcmToken'])->name('fcm-token');
    Route::delete('/fcm-token', [PostulanteDocumentoController::class, 'eliminarFcmToken'])->name('fcm-token.delete');
    Route::get('/firebase-config', [FirebaseConfigController::class, 'config'])->name('firebase-config');
    Route::get('/firebase-messaging-sw.js', [FirebaseConfigController::class, 'serviceWorker'])->name('firebase-sw');
    Route::get('/perfil', fn () => Inertia::render('Perfil/perfil'))->name('admin-perfil');
    Route::get('/get-datos-perfil', [ProfileController::class, 'getDatosUsuario']);
    Route::post('/actualizar-datos-perfil', [ProfileController::class, 'actualizarDatosUsuario']);
    Route::post('/cambiar-contrasena-perfil', [ProfileController::class, 'cambiarContrasenaPerfil']);
    Route::post('/actualizar-estado-firma-perfil', [ProfileController::class, 'actualizarEstadoFirma']);
    Route::post('/subir-foto-perfil', [ProfileController::class, 'subirFotoPerfil']);
    Route::post('/crear-certificado-digital', [ProfileController::class, 'crearCertificadoDigital']);
    Route::get('/get-activity-log', [ProfileController::class, 'getActivityLog']);
    Route::get('/get-certificado-digital', [ProfileController::class, 'getCertificadoDigital']);

    Route::get('/about', fn () => Inertia::render('About'))->name('about');
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('cambiar-contra', [UsuarioController::class, 'cambiarContra']);

    Route::post('/select-programas', [ProgramaController::class, 'getSelectProgramas']);
    Route::post('/select-modalidades', [ModalidadController::class, 'getSelectModalidades']);
});

Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

Route::prefix('admin')->middleware('auth','admin')->group(function () {


    Route::get('/', fn () => Inertia::render('Admin/Dashboard/Index'));
    Route::get('dashboard', fn () => Inertia::render('Admin/Dashboard/Index'))->name('admin-dashboard');
    Route::get('get-preinscritos', [DashboardController::class, 'preinscritos']);
    Route::get('get-inscritos', [DashboardController::class, 'inscritos']);
    Route::get('get-mejores-inscriptores', [DashboardController::class, 'mInscriptores']);
    Route::post('get-mejores-inscriptores-dia', [DashboardController::class, 'mInscriptoresDia']);

    // Dashboard nuevos endpoints
    Route::get('dashboard/resumen-general', [DashboardController::class, 'resumenGeneral']);
    Route::get('dashboard/postulantes-por-area', [DashboardController::class, 'postulantesPorArea']);
    Route::get('dashboard/genero-por-area', [DashboardController::class, 'generoPorArea']);
    Route::get('dashboard/inscritos-por-programa', [DashboardController::class, 'inscritosPorPrograma']);
    Route::get('dashboard/timeline-inscripciones', [DashboardController::class, 'timelineInscripciones']);
    Route::get('dashboard/biometrico-resumen', [DashboardController::class, 'biometricoResumen']);
    Route::get('dashboard/modalidad-distribucion', [DashboardController::class, 'modalidadDistribucion']);
    Route::get('dashboard/tipo-colegio-distribucion', [DashboardController::class, 'tipoColegioDistribucion']);

    Route::resource('roles',RolController::class);
    Route::get('roles', fn () => Inertia::render('Roles/index'))->name('roles-index');
    Route::resource('usuarios',UsuarioController::class);
    Route::get('usuarios', fn () => Inertia::render('Usuarios/index'))->name('usuarios-index');

    Route::get('/get-permission', [RolController::class, 'getPermission'])->name('get');
    Route::get('/get-roles', [RolController::class, 'getRoles']);
    Route::post('/save-rol', [RolController::class, 'saveRol']);

    Route::post('/get-usuarios', [UsuarioController::class, 'getUsuarios']);
    Route::get('/get-roles-u', [UsuarioController::class, 'getRoles']);
    Route::post('/save-user',[UsuarioController::class, 'saveUsuario']);

    Route::get('/get-permisos', [UsuarioController::class, 'getPermisos']);

    Route::post('/inscripciones/get-postulantes', [InscripcionController::class, 'getPostulantes']);

    Route::get('/inscripciones/impresion', [InscripcionController::class, 'index'])->name('impresion-cepre');
    Route::get('/inscripciones/get-postulante-dni/{dni}', [InscripcionController::class, 'getPostulanteByDni']);
    Route::get('/inscripciones/get-apoderados-postulante/{dni}', [InscripcionController::class, 'getApoderados']);
    Route::get('/inscripciones/get-vouchers-postulante/{dni}', [InscripcionController::class, 'getVouchers']);
    Route::get('/inscripciones/get-documentos-postulante/{dni}', [InscripcionController::class, 'getDocumentos']);
    Route::get('/inscripciones/get-preinscripciones-postulante/{dni}', [InscripcionController::class, 'getPreinscipciones']);
    Route::get('/inscripciones/get-inscripciones-postulante/{dni}', [InscripcionController::class, 'getInscripciones']);
    Route::get('/pdf-inscripción/{dni}', [InscripcionController::class, 'pdfInscripcion']);
    Route::post('/inscripciones/inscribir', [InscripcionController::class, 'Inscribir']);

    Route::get('/inscripciones/descargar-lista', [InscripcionController::class, 'descargarListaExcel']);

    Route::post('/get-inscripciones-admin', [InscripcionController::class, 'getInscripcionesAdmin']);
    Route::post('/get-preinscripciones-admin', [PreinscripcionController::class, 'getPreinscripcionesAdmin']);
    Route::post('/actualizar-sexo-postulante', [PreinscripcionController::class, 'actualizarSexo']);
    Route::post('/actualizar-preinscripcion', [PreinscripcionController::class, 'Actualizar']);
    Route::post('/eliminar-preinscripcion', [PreinscripcionController::class, 'Eliminar']);

    Route::get('/procesos', [ProcesoController::class, 'index'])->name('proceso-index');
    Route::get('/eliminar-proceso/{id}', [ProcesoController::class, 'deleteProceso']);
    Route::get('/procesos/get-tipos', [ProcesoController::class, 'getTipoProceso']);
    Route::get('/procesos/get-modalidades', [ProcesoController::class, 'getModalidades']);
    Route::post('/procesos/get-procesos', [ProcesoController::class, 'getProcesos']);
    Route::post('/save-proceso', [ProcesoController::class, 'saveProceso']);
    Route::get('/get-select-procesos', [ProcesoController::class, 'getSelectProceso']);
    Route::post('/cambiar_proceso', [ProcesoController::class, 'cambiarProceso']);

    // REQUISITOS
    Route::get('/requisitos', [RequisitoDocumentoController::class, 'index'])->name('requisitos-index');
    Route::post('/requisitos/get-all', [RequisitoDocumentoController::class, 'getAll']);
    Route::post('/requisitos/save', [RequisitoDocumentoController::class, 'save']);
    Route::post('/requisitos/save-tipos-documento', [RequisitoDocumentoController::class, 'saveTiposDocumento']);
    Route::post('/requisitos/importar', [RequisitoDocumentoController::class, 'importarRequisitos']);
    Route::get('/requisitos/delete/{id}', [RequisitoDocumentoController::class, 'delete']);
    Route::post('/requisitos/get-by-proceso', [RequisitoDocumentoController::class, 'getByProceso']);
    Route::post('/requisitos/save-proceso-requisitos', [RequisitoDocumentoController::class, 'saveProcesoRequisitos']);
    Route::post('/requisitos/get-modalidades', [RequisitoDocumentoController::class, 'getModalidades']);
    Route::post('/requisitos/get-programas', [RequisitoDocumentoController::class, 'getProgramas']);
    Route::get('/requisitos/get-by-modalidad/{id_modalidad}', [RequisitoDocumentoController::class, 'getByModalidad']);
    Route::get('/requisitos/get-tipos-documento', [RequisitoDocumentoController::class, 'getTiposDocumento']);

    // TIPOS DE DOCUMENTO
    Route::get('/tipos-documento', [TipoDocumentoController::class, 'index'])->name('tipos-documento-index');
    Route::post('/tipos-documento/get-tipos-documento', [TipoDocumentoController::class, 'getTiposDocumento']);
    Route::post('/tipos-documento/save', [TipoDocumentoController::class, 'save']);
    Route::get('/tipos-documento/delete/{id}', [TipoDocumentoController::class, 'delete']);

    //PREINSCRIPCION
    Route::post('/get-postulante-datos-personales', [PostulanteController::class, 'getPostulanteXDni']);
    Route::post('/get-postulante-datos-personales2', [PostulanteController::class, 'getPostulanteXDni2']);
    Route::get('/inscripciones', fn () => Inertia::render('Admin/Inscripcion/index'))->name('admin-inscripciones');

    Route::post('/actualizar-inscripcion', [InscripcionController::class, 'Actualizar']);

    Route::get('/preinscripciones', fn () => Inertia::render('Admin/Preinscripcion/index'))->name('admin-preinscripciones');

    // Route::post('/save-programa', [ProgramaController::class, 'savePrograma']);
    // Route::post('/programas/get-programas', [ProgramaController::class, 'getProgramas']);
    // Route::get('/eliminar-programa/{id}', [ProgramaController::class, 'deletePrograma']);

    //fILIAL
    Route::get('/filial', [FilialController::class, 'index'])->name('filial-index');
    Route::post('/filiales/get-filiales', [FilialController::class, 'getFiliales']);
    Route::post('/save-filial', [FilialController::class, 'saveFilial']);
    Route::get('/eliminar-filial/{id}', [FilialController::class, 'deleteFilial']);

    //PROGRAMA
    Route::get('/programa', [ProgramaController::class, 'index'])->name('programa-index');
    Route::post('/save-programa', [ProgramaController::class, 'savePrograma']);
    Route::post('/programas/get-programas', [ProgramaController::class, 'getProgramas']);
    Route::get('/eliminar-programa/{id}', [ProgramaController::class, 'deletePrograma']);

    //APODERADOS
    Route::post('/get-apoderados-admin', [ApoderadoController::class, 'getApoderadoAdmin']);
    Route::post('/save-apoderados-admin', [ApoderadoController::class, 'saveApoderadoAdmin']);

    //MODALIDAD
    Route::get('/modalidad', [ModalidadController::class, 'index'])->name('modalidad-index');
    Route::post('/save-modalidad', [ModalidadController::class, 'saveModalidad']);
    Route::post('/modalidad/get-modalidades', [ModalidadController::class, 'getModalidades']);
    Route::post('/cambiar-estado-modalidad/{id}', [ModalidadController::class, 'cambiarEstado']);
    Route::get('/eliminar-modalidad/{id}', [ModalidadController::class, 'deleteModalidad']);
    Route::get('/get-modalidades-activas', [ModalidadController::class, 'getModalidadesActivas']);

    // AÑOS
    Route::get('/anios', [AnioController::class, 'index'])->name('anio-index');
    Route::post('/anio/get-anios', [AnioController::class, 'getAnios']);
    Route::post('/save-anios', [AnioController::class, 'saveAnio']);
    Route::get('/eliminar-anio/{id}', [AnioController::class, 'deleteAnio']);

    //PRE-INSCRIPCIONES
    Route::get('/pre-inscripcion', [PreinscripcionController::class, 'index'])->name('preincripcion-index');
    // Route::get('/pre-inscripcion', [PreinscripcionController::class, 'index'])->name('preincripcion-index');
    // Route::get('/pre-inscripcion', [PreinscripcionController::class, 'index'])->name('preincripcion-index');
    // Route::get('/pre-inscripcion', [PreinscripcionController::class, 'index'])->name('preincripcion-index');

    //GET DATA
    Route::get('/get-facultades', [SeleccionDataController::class, 'getFacultades']);
    Route::post('/get-sedes', [SeleccionDataController::class, 'getSedes']);
    Route::post('/get-departamentos', [SeleccionDataController::class, 'getDepartamento']);
    Route::get('/get-provincia-x-departamento/{cod}', [SeleccionDataController::class, 'getProvinciasPorDepartamento']);
    Route::post('/pre-inscripcion/get-comprobantes', [SeleccionDataController::class, 'getComprobanteByDni']);

    Route::post('/get-departamentos-codigo', [SeleccionDataController::class, 'getDepartamentoCodigo']);
    Route::post('/get-provincias-codigo', [SeleccionDataController::class, 'getProvinciasCodigo']);

    Route::post('/get-distritos-codigo', [SeleccionDataController::class, 'getDistritosCodigo']);

    Route::get('/foto-inscripcion', fn () => Inertia::render('Foto/foto'))->name('admin-foto-inscripcion');
    Route::get('/foto-biometrico', fn () => Inertia::render('Foto/fotobiometrico'))->name('admin-foto-biometrico');
    Route::post('/guardar-foto-inscripcion', [FotoController::class, 'guardarFotoInscripcion']);
    Route::post('/guardar-foto-biometrico', [FotoController::class, 'guardarFotoBiometrico']);

    Route::get('/vocacional', fn () => Inertia::render('Admin/Vocacional/index'))->name('admin-reporte');
    Route::get('/resultados-vocacional', [PreguntaController::class, 'getResultado']);

    Route::get('/apoderados', fn () => Inertia::render('Admin/Apoderados/index'))->name('admin-apoderado-index');
    Route::get('/postulante', fn () => Inertia::render('Admin/Postulante/index'))->name('admin-postulante-index');
    Route::get('/colegio', fn () => Inertia::render('Admin/Colegio/index'))->name('admin-colegio-index');
    Route::get('/documento', fn () => Inertia::render('Admin/Documentos/index'))->name('admin-documento-index');
    Route::post('/get-colegios-admin', [ColegioController::class, 'getColegiosAdmin']);
    Route::post('/get-documentos-admin', [DocumentoController::class, 'getDocumentosAdmin']);
    Route::post('/save-documento', [DocumentoController::class, 'saveDocumentoAdmin']);
    Route::get('/documento/download/{id}', [DocumentoController::class, 'downloadDocumento']);
    Route::post('/documento/verify/{id}', [DocumentoController::class, 'verifyDocumento']);
    Route::post('/documento/update/{id}', [DocumentoController::class, 'updateDocumento']);
    Route::delete('/documento/delete/{id}', [DocumentoController::class, 'deleteDocumento']);

    Route::post('/get-postulantes-admin', [PostulanteController::class, 'getPostulantesAdmin']);
  #  Route::post('/get-participantes-vocacional', [vocacionalController::class, 'participantesVocacional']);

    Route::post('/save-postulante-admin', [PostulanteController::class, 'savePostulanteAdmin']);
    Route::post('/vincular-postulante-usuario', [PostulanteController::class, 'vincularUsuario']);
    Route::post('/buscar-usuario-dni', [PostulanteController::class, 'buscarUsuarioPorDni']);
    // Route::post('/modalidad/get-modalidades', [ModalidadController::class, 'getModalidades']);
    // Route::get('/eliminar-modalidad/{id}', [ModalidadController::class, 'deleteModalidad']);

    Route::get('/component', fn () => Inertia::render('Admin/Dashboard/components/reportes'));

    //REPORTES VARIOS
    Route::get('get-inscritos-genero-reporte', [DashboardController::class, 'reporteInscritosGenero']);
    Route::get('get-inscritos-edad-reporte', [DashboardController::class, 'reporteInscritosEdad']);
    Route::get('get-inscritos-residencia-reporte', [DashboardController::class, 'reporteInscritosResidencia']);
    Route::get('get-inscritos-procedencia-reporte', [DashboardController::class, 'reporteInscritosProcedencia']);
    Route::get('get-inscritos-egreso-reporte', [DashboardController::class, 'reporteInscritosEgreso']);
    Route::get('get-inscritos-discapacidad-reporte', [DashboardController::class, 'reporteInscritosDiscapacidad']);
    Route::get('get-inscritos-tipo-documento-reporte', [DashboardController::class, 'reporteInscritosTipoDocumento']);
    Route::get('get-inscritos-colegio-reporte', [DashboardController::class, 'reporteInscritosColegio']);
    Route::get('get-inscritos-procedencia-colegio-reporte', [DashboardController::class, 'reporteInscritosProcedenciaColegio']);
    Route::get('get-inscritos-tipo-colegio-reporte', [DashboardController::class, 'reporteInscritosTipoColegio']);

    //POSTULANTE
    Route::get('/perfil-postulante', fn () => Inertia::render('Admin/Postulante/Perfil'));
    Route::get('postulante-perfil/{dni}', [DashboardController::class, 'showPostulante']);
    Route::post('get-procesos', [DashboardController::class, 'getInsPostulante']);

    //CONTROL BIOMETRICO
    Route::get('/control-biometrico', fn () => Inertia::render('Admin/ControlBiometrico/Lista'))->name('admin-control-biometrico');
    Route::post('/get-control-posterior', [ControlBiometricoController::class, 'getControlPosterior']);

    //PARTICIPANTES
    Route::get('/participante-docente', fn () => Inertia::render('Admin/Participante/Docente'))->name('admin-participante-docente');
    Route::get('/participante-administrativo', fn () => Inertia::render('Admin/Participante/Administrativo'))->name('admin-participante-administrativo');
    Route::get('/participante-sorteo', fn () => Inertia::render('Admin/Participante/Sorteo'))->name('admin-participante-sorteo');

    Route::post('/save-docente', [DocenteController::class, 'saveDocente']);
    Route::post('/get-docentes', [DocenteController::class, 'getDocentes']);
    Route::post('/eliminar-docente', [DocenteController::class, 'deleteDocente']);
    Route::post('/actualizar-sexo-docente', [DocenteController::class, 'actualizarSexo']);

    Route::post('/save-administrativo', [AdministrativoController::class, 'saveAdministrativo']);
    Route::post('/get-administrativos', [AdministrativoController::class, 'getAdministrativos']);
    Route::post('/eliminar-administrativo', [AdministrativoController::class, 'deleteAdministrativo']);
    Route::post('/actualizar-sexo-administrativo', [AdministrativoController::class, 'actualizarSexo']);

    Route::get('/colegios', fn () => Inertia::render('Admin/Colegios/index'))->name('admin-colegios');
    Route::post('/get-colegios', [ColegioController::class, 'getColegios']);
    Route::post('/save', [ColegioController::class, 'save']);

    //Configuracion programa
    Route::get('/proceso-configuracion-programa', fn () => Inertia::render('Procesos/Configuracion/programas'));
    Route::get('/get-programas-proceso',[ProgramaProcesoController::class, 'getProgramaProceso']);


    //OBSERVADOS
    Route::get('/observados', fn () => Inertia::render('Admin/Observados/index'))->name('admin-observados');
    Route::post('/get-observados-lista', [SancionadoController::class, 'getObservadosLista']);
    Route::post('/save-observado', [SancionadoController::class, 'save']);

    //PAGOS
    Route::get('/pagos-banco', fn () => Inertia::render('Admin/Pagos/index'))->name('admin-pagos-banco');
    Route::post('/get-pagos-bn-admision', [PagoBancoController::class, 'getPagosAdmision']);
    //Route::post('/get-pagos-oti', [PagoBancoController::class, 'getPagosOTI']);
    //Route::post('/get-observados-lista', [SancionadoController::class, 'getObservadosLista']);
    //Route::post('/save-observado', [SancionadoController::class, 'save']);

    //VACANTES
    Route::get('/vacantes', fn () => Inertia::render('Admin/Vacantes/index'))->name('admin-vacantes');
    Route::post('/get-vacantes-admin', [VacantesController::class, 'getVacantes']);
    Route::post('/save-numero-vacantes', [VacantesController::class, 'saveNumeroVacantes']);
    Route::post('/delete-vacante', [VacantesController::class, 'eliminar']);

    //TARIFAS
    Route::get('/tarifas', fn () => Inertia::render('Tarifas/index'))->name('tarifa-index');
    Route::post('/tarifas/get-tarifas', [TarifaController::class, 'getTarifas']);
    Route::post('/save-tarifa', [TarifaController::class, 'saveTarifa']);
    Route::post('/cambiar-estado-tarifa/{id}', [TarifaController::class, 'cambiarEstado']);
    Route::get('/eliminar-tarifa/{id}', [TarifaController::class, 'deleteTarifa']);

    //RENIEC
    Route::get('/consulta-reniec', fn () => Inertia::render('Admin/Reniec/index'))->name('admin-consulta-reniec');
    Route::get('/get-datos-reniec/{dni}', [ReniecController::class, 'consultarReniecPorDni']);
    Route::post('/actualizar-lista-reniec', [ReniecController::class, 'consultarLista']);

    //ESTUDIOS ANTERIORES
    Route::get('/carreras-previas', fn () => Inertia::render('Admin/Estudios/carreras_previas'))->name('admin-carreras-previas');
    Route::post('/get-carreras-previas-registrado', [CarrerasPreviasController::class, 'getCarrerasPrevias']);
    Route::post('/save-carrera-previa', [CarrerasPreviasController::class, 'save']);
    //Route::post('/programas/get-programas', [ProgramaController::class, 'getProgramas']);
    Route::get('/eliminar-carrera-previa/{id}', [CarrerasPreviasController::class, 'delete']);


    Route::get('/get-select-programas-proceso-admin',[ProgramaProcesoController::class, 'getSelectProgramasProcesoAdmin']);

    //REGLAMENTOS
    Route::get('/reglamentos', fn () => Inertia::render('Admin/Reglamento/index'))->name('admin-reglamento');
    Route::get('/get-select-reglamentos', [ReglamentoController::class, 'getSelectReglamentos']);
    Route::post('/get-reglamentos', [ReglamentoController::class, 'getReglamentos']);
    Route::post('/save-reglamento', [ReglamentoController::class, 'saveReglamento']);
    Route::get('/eliminar-reglamento/{id}', [ReglamentoController::class, 'eliminarReglamento']);

    Route::get('/resumenes-inscripcion', fn () => Inertia::render('Admin/Resumenes/inscripciones'))->name('admin-resumenes-inscripcion');
    Route::get('/resumenes-general', fn () => Inertia::render('Admin/Resumenes/resumenGeneral'))->name('admin-resumenes-general');
    Route::get('/resumenes-inscripcion-programa-diario', fn () => Inertia::render('Admin/Resumenes/programaDiario'))->name('admin-resumenes-programa-diario');
    Route::get('/reporte-usuarios-diario', fn () => Inertia::render('Admin/Resumenes/usuarioDiario'))->name('admin-resumenes-usuario-diario');
    Route::post('/resumen-inscripciones', [ResumenInscripcionesController::class, 'resumenInscripciones']);
    Route::post('/reporte-programa', [ReporteController::class, 'reportePrograma'])->middleware('auth');
    Route::post('/reporte-programa-diario', [ReporteController::class, 'reporteProgramaDiario'])->middleware('auth');
    Route::post('/reporte-usuarios', [ReporteController::class, 'reporteUsuarios'])->middleware('auth');
    Route::post('/get-ratio', [RatioController::class, 'getRatio']);
    Route::get('/ratio', fn () => Inertia::render('Admin/Resumenes/ratio'))->name('admin-ratio');

    Route::get('/resumenes-biometrico', fn () => Inertia::render('Admin/Resumenes/biometrico'))->name('admin-resumenes-biometrico');
    Route::post('/resumen-biometrico', [ResumenBiometricoController::class, 'resumenBiometrico']);


    Route::get('/descargar-documentos', fn () => Inertia::render('Procesos/temp'));
    Route::post('/admin/descargar-documentos/prepare', [DescargarArchivosController::class, 'prepareDownload'])
        ->name('download.prepare');

    Route::get('/admin/descargar-documentos/status', [DescargarArchivosController::class, 'downloadStatus'])
        ->name('download.status');

    Route::get('/admin/descargar-documentos/download/{filename}', [DescargarArchivosController::class, 'downloadPreparedZip'])
        ->name('download.prepared');

    Route::get('/pdf-solicitud/{dni}', [PreinscripcionController::class, 'pdfsolicitudAdmin']);
    Route::get('/pdf-biometrio/{dni}', [IngresoController::class, 'pdfbiometrico2']);

    Route::get('/certificados-firma', fn () => Inertia::render('Admin/CertificadosFirma/index'))->name('admin-certificados-firma');




    Route::prefix('certificados')->group(function () {
        Route::get('/', [CertificadoFirmaController::class, 'index']);
        Route::post('/', [CertificadoFirmaController::class, 'store']);
        Route::get('/{id}', [CertificadoFirmaController::class, 'show']);
        Route::put('/{id}', [CertificadoFirmaController::class, 'update']);
        Route::delete('/{id}', [CertificadoFirmaController::class, 'destroy']);
    });

    Route::get('/pdf-biometrio-manual/{dni}', [IngresoController::class, 'pdfbiometricoManual']);


    // BACKUP BD
    Route::get('/backup', [BackupController::class, 'index']);
    Route::post('/backup/crear', [BackupController::class, 'crear']);
    Route::get('/backup/descargar/{filename}', [BackupController::class, 'descargar']);
    Route::post('/backup/restaurar', [BackupController::class, 'restaurar']);
    Route::post('/backup/eliminar', [BackupController::class, 'eliminar']);
    Route::get('/respaldo-bd', fn () => Inertia::render('Admin/Backup/index'))->name('admin-respaldo-bd');

    // Trazabilidad / Auditoría
    Route::get('/trazabilidad', fn () => Inertia::render('Admin/Trazabilidad/index'))->name('admin-trazabilidad');
    Route::get('/trazabilidad/data', [AuditTrailController::class, 'getAuditTrail']);
    Route::get('/trazabilidad/resumen', [AuditTrailController::class, 'getResumenAcciones']);

    // Configuración de Citación
    Route::get('/configuracion-citacion', fn () => Inertia::render('Admin/ConfiguracionCitacion/Index'))->name('admin.configuracion-citacion');
    Route::get('/configuracion-citacion/lista', [ConfiguracionCitacionController::class, 'index']);
    Route::post('/configuracion-citacion', [ConfiguracionCitacionController::class, 'store']);
    Route::put('/configuracion-citacion/{id}', [ConfiguracionCitacionController::class, 'update']);
    Route::delete('/configuracion-citacion/{id}', [ConfiguracionCitacionController::class, 'destroy']);
    Route::get('/configuracion-citacion/procesos', [ConfiguracionCitacionController::class, 'getProcesos']);
    Route::get('/configuracion-citacion/modalidades', [ConfiguracionCitacionController::class, 'getModalidades']);
    Route::get('/configuracion-citacion/programas', [ConfiguracionCitacionController::class, 'getProgramas']);

    // Validación masiva de documentos
    Route::get('/validacion-masiva', fn () => Inertia::render('Admin/ValidacionMasiva'))->name('admin-validacion-masiva');
    Route::get('/api/solicitudes-pendientes', [AdminDocumentoController::class, 'listarSolicitudesPendientes']);
    Route::post('/api/validacion-masiva', [AdminDocumentoController::class, 'validacionMasiva']);

    // ── RBAC: Permisos ──────────────────────────────────
    Route::get('/permisos', [PermisoController::class, 'index'])->name('admin-permisos');
    Route::get('/permisos/get-modulos', [PermisoController::class, 'getModulos']);
    Route::get('/permisos/get-permisos', [PermisoController::class, 'getPermisos']);
    Route::post('/permisos/save', [PermisoController::class, 'savePermiso']);
    Route::get('/permisos/delete/{id}', [PermisoController::class, 'deletePermiso']);
    Route::get('/permisos/get-roles', [PermisoController::class, 'getRoles']);
    Route::get('/permisos/rol/get', [PermisoController::class, 'getPermisosRol']);
    Route::post('/permisos/rol/save', [PermisoController::class, 'savePermisoRol']);
    Route::get('/permisos/get-usuarios', [PermisoController::class, 'getUsuarios']);
    Route::get('/permisos/usuario/get', [PermisoController::class, 'getPermisosUsuario']);
    Route::post('/permisos/usuario/save', [PermisoController::class, 'savePermisoUsuario']);
    Route::get('/permisos/get-acciones', [PermisoController::class, 'getAcciones']);
    Route::post('/permisos/accion/save', [PermisoController::class, 'saveAccion']);
    Route::get('/permisos/accion/delete/{id}', [PermisoController::class, 'deleteAccion']);

    // ── RBAC: Módulos ───────────────────────────────────
    Route::get('/modulos', [ModuloController::class, 'index'])->name('admin-modulos');
    Route::get('/modulos/get', [ModuloController::class, 'getModulos']);
    Route::get('/modulos/get-acciones', [ModuloController::class, 'getAcciones']);
    Route::post('/modulos/save', [ModuloController::class, 'saveModulo']);
    Route::get('/modulos/delete/{id}', [ModuloController::class, 'deleteModulo']);
    Route::post('/modulos/save-view', [ModuloController::class, 'saveView']);
    Route::get('/modulos/delete-view/{id}', [ModuloController::class, 'deleteView']);
    Route::post('/modulos/save-accion', [ModuloController::class, 'saveAccion']);
    Route::get('/modulos/delete-accion/{id}', [ModuloController::class, 'deleteAccion']);
    Route::post('/modulos/toggle-accion/{id}', [ModuloController::class, 'toggleAccion']);

    // ── SMTP Accounts ────────────────────────────────────
    Route::get('/smtp-accounts', fn () => Inertia::render('Admin/SmtpAccounts/Index'))->name('admin.smtp-accounts');
    Route::get('/smtp-accounts/lista', [SmtpAccountController::class, 'lista']);
    Route::post('/smtp-accounts', [SmtpAccountController::class, 'store']);
    Route::put('/smtp-accounts/{id}', [SmtpAccountController::class, 'update']);
    Route::delete('/smtp-accounts/{id}', [SmtpAccountController::class, 'destroy']);
    Route::post('/smtp-accounts/{id}/toggle', [SmtpAccountController::class, 'toggle']);
    Route::post('/smtp-accounts/{id}/default', [SmtpAccountController::class, 'setDefault']);
    Route::post('/smtp-accounts/{id}/test', [SmtpAccountController::class, 'testEmail']);

    // ── Settings ─────────────────────────────────────────
    Route::get('/settings/preinscripcion-email', [SettingController::class, 'getPreinscripcionEmailVerification']);
    Route::post('/settings/preinscripcion-email/toggle', [SettingController::class, 'togglePreinscripcionEmailVerification']);

    // ── Admin Postulante Registration ────────────────────
    Route::get('/registro-postulante', fn () => Inertia::render('Admin/RegistroPostulante/Index'))->name('admin.registro-postulante');

    // ── Cultural data routes (for admin RegistroPostulante) ──
    Route::get('/get-condiciones-lengua-segundas', [IdentidadSegundaController::class, 'getCondicionesLengua']);
    Route::get('/get-pertenencia-cultural-segundas', [IdentidadSegundaController::class, 'getPertenenciaCultural']);
    Route::get('/get-lengua-segundas', [IdentidadSegundaController::class, 'getLenguaIndigena']);
    Route::get('/get-pueblos-indigenes-segundas', [IdentidadSegundaController::class, 'getPueblosIndigenas']);
    Route::get('/get-identidad-cultural/{id_postulante}/{id_proceso}', [IdentidadSegundaController::class, 'getIdentidadCulturalByPostulanteProceso']);
    Route::post('/save-postulante-adicional', [PostulanteSegundaController::class, 'saveDataAdicional']);

});

#Route::post('/get-participantes-vocacional', [vocacionalController::class, 'participantesVocacional']);
require __DIR__.'/revisor.php';

Route::get('/examen-vocacional2', fn () => Inertia::render('Publico/exvocacional2'))->name('ex-vocacional2');
Route::post('/get-avance-postulante', [TestController::class, 'getAvancePostulante']);
Route::post('/get-avance-postulante2', [TestController::class, 'getAvancePostulante2']);

Route::post('/get-preguntas2', [PreguntaController::class, 'getPreguntas2']);
Route::post('/get-alternativas2', [PreguntaController::class, 'getAlternativas2']);

Route::get('/get-pre', [PreguntaController::class, 'getPreguntasPerfiles2']);

Route::prefix('simulacro')->group(function () {

    Route::post('/save-simulacro', [SimulacroController::class, 'saveSimulacro']);

    Route::middleware(['auth', 'simulacro'])->group(function () {
        Route::get('/', fn () => Inertia::render('Simulacro/Admin/index'))->name('simulacro-inicio');
        Route::get('/get-nro-participantes', [SimulacroController::class, 'postulantesRegistrados']);
        Route::get('/get-nro-inscritos', [SimulacroController::class, 'postulantesInscritos']);
        Route::get('/get-nro-pagos', [SimulacroController::class, 'pagosRegistrados']);


        //COLEGIOS
        Route::get('/colegios', fn () => Inertia::render('Simulacro/Colegios/index'))->name('simulacro-colegios');
        Route::post('/get-colegios', [ColegioController::class, 'getColegios']);
        Route::post('/save', [ColegioController::class, 'save']);

        //INSCRITOS
        Route::get('/inscripciones', fn () => Inertia::render('Simulacro/Admin/Inscripciones/index'))->name('simulacro-inscritos');
        Route::post('/get-inscritos-simulacro', [SimulacroController::class, 'getInscritosSimulacro']);

        //PARTICIPANTES
        Route::get('/participantes', fn () => Inertia::render('Simulacro/Admin/Participantes/index'))->name('simulacro-participantes');
        Route::post('/get-participantes-simulacro', [SimulacroController::class, 'getParticipantesSimulacro']);
        Route::post('/save-simulacro-participante', [SimulacroController::class, 'updateParticipante']);


        //ENTRADA
        Route::get('/entrada', fn () => Inertia::render('Simulacro/Entrada/index'));
        Route::post('/get-participante', [SimulacroController::class, 'getEntrada']);
        Route::post('/save-entrada', [SimulacroController::class, 'saveEntrada']);
        Route::post('/get-total-entrada', [SimulacroController::class, 'getTotalEntrada']);
        Route::post('/get-simulacro-ingreso', [SimulacroController::class, 'getIngresos']);


        //PAGOS
        Route::get('/pagos', fn () => Inertia::render('Simulacro/Admin/Pagos/index'))->name('simulacro-pagos');
        Route::get('/pagos-consulta', fn () => Inertia::render('Simulacro/Admin/Pagos/consulta'))->name('simulacro-consulta-pagos');
        Route::post('/get-pagos-simulacro', [PagoSimulacroController::class, 'getPagosSimulacro']);
        Route::post('/get-pagos-simulacro-consulta', [PagoSimulacroController::class, 'getPagosSimulacroConsulta']);

        //REPORTES
        Route::get('/postulantes-por-programas', [SimulacroController::class, 'postulantexPrograma']);
        Route::get('get-inscritos-genero-reporte', [SimulacroController::class, 'reporteInscritosGenero']);
        Route::get('get-inscritos-areas-reporte', [SimulacroController::class, 'reporteInscritosAreas']);


        //Route::get('/', fn () => Inertia::render('Simulacro/index'))->name('simulacros');
        Route::get('/simulacros', fn () => Inertia::render('Simulacro/Simulacros'))->name('simulacro-simulacros');
        Route::get('/calificacion', fn () => Inertia::render('Simulacro/Ficha'))->name('simulacro-calificacion');

        //Route::post('/save-simulacro', [SimulacroController::class, 'saveSimulacro']);
        Route::post('/get-simulacros', [SimulacroController::class, 'getSimulacros']);
        Route::post('/get-participantes', [SeleccionDataController::class, 'getParticipantes']);
        Route::post('/get-participantes-simulacro', [SimulacroController::class, 'getParticipantesSimulacro']);
        Route::post('/save-respuestas', [SimulacroController::class, 'saveRespuestas']);

    });



});

Route::prefix('simulacros')->group(function () {
     Route::get('/formulario_inscripcion', fn () => Inertia::render('Simulacro/formulario'));
     Route::get('/descargar-constancia', fn () => Inertia::render('Simulacro/descargarHoja'));
});



require __DIR__.'/calificacion.php';
Route::post('/get-puntaje-simulacro', [ResultadosController::class, 'getResultados']);


Route::get('/resultados-simulacro', fn () => Inertia::render('Simulacro/resultados'));

Route::get('/descargar-ingenierias', [ResultadosController::class, 'getExamenIng']);
Route::get('/descargar-biomedicas', [ResultadosController::class, 'getExamenBio']);
Route::get('/descargar-sociales', [ResultadosController::class, 'getExamenSoc']);

//PREINSCRIPCION
Route::get('/preinscripcion-adicional', fn () => Inertia::render('Publico/preinscripcion-pregrado'))->name('preinscripcion');
//Route::get('/preinscripcion', fn () => Inertia::render('Publico/preinscripcion'))->name('preinscripcion');
Route::get('/preinscripcion-general', fn () => Inertia::render('Publico/preinscripciongeneral'))->name('preinscripcion-general');
Route::get('/preinscripcion-pregrado', fn () => Inertia::render('Publico/preinscripcion-pregrado'))->name('preinscripcion-pregrado');
Route::get('/examen-vocacional', fn () => Inertia::render('Publico/exvocacional'))->name('ex-vocacional');

Route::post('/save-respuesta', [DetalleExamenVocacionalController::class, 'saveRespuesta']);

Route::post('save-pasos-preinscripcion', [PreinscripcionController::class, 'savePasos']);
Route::post('/get-postulante-datos-personales', [PostulanteController::class, 'getPostulanteXDni']);
Route::post('/get-postulante-datos-personales2', [PostulanteController::class, 'getPostulanteXDni2']);
Route::get('/get-certificado-preinscripcion/{dni}', [PostulanteController::class, 'getCertificadoPreinscripcion']);
Route::post('/enviar-codigo-verificacion-datos', [PostulanteController::class, 'enviarCodigoVerificacionDatos']);
Route::post('/verificar-codigo-datos', [PostulanteController::class, 'verificarCodigoDatos']);
Route::post('/save-postulante-dni', [PostulanteController::class, 'saveDniPostulante']);
Route::post('/save-postulante', [PostulanteController::class, 'savePostulante']);
Route::post('/save-postulante-segundas', [PostulanteController::class, 'savePostulanteSegundas']);
Route::post('/save-postulante-residencia', [PostulanteController::class, 'saveResidencia']);
Route::post('/save-postulante-colegio', [PostulanteController::class, 'saveColegio']);
Route::post('/save-postulante-apoderado', [ApoderadoController::class, 'saveApoderado']);
Route::post('save-pre-inscripcion', [PreinscripcionController::class, 'preinscribir']);
Route::get('pdf', [PreinscripcionController::class, 'pdf']);

Route::post('/get-departamentos-codigo', [SeleccionDataController::class, 'getDepartamentoCodigo']);
Route::post('/get-provincias-codigo', [SeleccionDataController::class, 'getProvinciasCodigo']);
Route::post('/get-distritos-codigo', [SeleccionDataController::class, 'getDistritosCodigo']);
Route::post('/get-ubigeo-colegio', [ColegioController::class, 'getUbigeoColegio']);
Route::post('/get-colegio-distrito', [ColegioController::class, 'getColegiosDistrito']);

Route::post('/get-colegio-distrito', [ColegioController::class, 'getColegiosDistrito']);
Route::post('/get-apoderado', [ApoderadoController::class, 'getApoderado']);
Route::post('/get-pasos-proceso', [SeleccionDataController::class, 'getPasos']);

Route::post('/get-preguntas', [PreguntaController::class, 'getPreguntasPrograma']);
Route::post('/get-preguntas-perfiles', [PreguntaController::class, 'getPreguntasPerfiles']);

Route::post('/get-datos-examen', [PreguntaController::class, 'getDatosExamen']);
Route::post('/get-datos-examen2', [PreguntaController::class, 'getDatosExamen2']);

Route::post('/save-vocacional', [DetalleExamenVocacionalController::class, 'saveVocacional']);

Route::get('/pdf-vocacional/{dni}', [PreinscripcionController::class, 'pdfvocacional']);
Route::get('/pdf-solicitud/{p}/{dni}', [PreinscripcionController::class, 'pdfsolicitud']);
Route::get('/pdf-solicitud-extranjeros/{p}/{dni}', [PreinscripcionController::class, 'pdfsolicitudExtranjeros']);

Route::post('/control-biometrico', [IngresoController::class, 'biometrico']);
Route::get('/control-biometrico-manual/{dni}', [IngresoController::class, 'registrar_biometrico']);

Route::get('/documentos-pdfs/{dni}', [PreinscripcionController::class, 'UnirPDF']);
Route::get('/siguiendo-mi-postulacion', fn () => Inertia::render('Publico/estado'));
#Route::get('/get-expediente/{programa}/{dni}', [TestController::class, 'getNroConstancia']);

Route::get('/participa/{dni}', [PostulanteController::class, 'participa']);
//Editor

Route::get('/ver-puntaje', fn () => Inertia::render('Publico/puntaje'));
Route::get('/aleatorio', fn () => Inertia::render('Publico/aleatorio'));

Route::get('/resultados', fn () => Inertia::render('Resultados/index'))->name('resultados');

//Seguimiento
Route::get('/test', fn () => Inertia::render('Prueba/test'));
//Route::get('/', [BlogController::class, 'verPuntajes']);
Route::get('/get-puntajes/{dni}', [BlogController::class, 'getPuntajes']);
Route::post('/get-puntajes-proceso', [BlogController::class, 'getPuntajesProceso']);
Route::get('/constancias-ingreso', [IngresoController::class, 'constanciasIngreso']);


Route::get('/pago-banco-nacion',[PagoSimulacroController::class,'getPagosBancoNacion']);
Route::get('/get-pagos',[PagoSimulacroController::class,'getPagos']);
Route::get('/get-pagos-caja',[PagoSimulacroController::class,'getPagosCaja']);

//SIMULACROS
Route::post('/get-ubigeo', [SeleccionDataController::class, 'getUbigeos']);
Route::post('/get-colegios-ubigeo',[SeleccionDataController::class,'getColegiosUbigeo']);
Route::post('/save-simulacro-participante', [SimulacroController::class, 'saveParticipante']);
Route::get('/pdf-simulacro-inscripcion/{dni}', [SimulacroController::class, 'pdfInscripcion']);
Route::get('/get-inscrito-simulacro/{dni}', [SimulacroController::class, 'Inscrito']);

Route::post('/subir-pagos', [PagoSimulacroController::class, 'pagosSimulacro']);
Route::get('/get-pago-simulacro/{dni}', [PagoSimulacroController::class, 'pagoSimulacro']);


Route::get('/get-e-oti', [IngresoController::class, 'getEstudianteOTI']);
Route::get('/get-pagos-simulacro-online/{dni}', function ($dni) {
    // $response = Http::get("http://38.43.133.27/PAYMENTS_MNG/v1/{$dni}/9/");
    $response = Http::get("https://service2.unap.edu.pe/PAYMENTS_MNG/v1/{$dni}/8/");
    if ($response->successful()) {
        return $response->json();
    } else {
        return response()->json(['error' => 'La solicitud GET no fue exitosa. Código de estado: ' . $response->status()], $response->status());
    }
});

//MODALIDADES y PROGRAMAS
Route::get('/get-select-modalidad-proceso/{id}',[ProgramaProcesoController::class, 'getSelectModalidadesProceso']);
Route::post('/get-select-programas-proceso',[ProgramaProcesoController::class, 'getSelectProgramasProceso']);

Route::post('/get-select-programas-proceso-area',[ProgramaProcesoController::class, 'getSelectProgramasProcesoArea']);
Route::get('/get-area-by-codigo/{area}',[ProgramaProcesoController::class, 'getAreaByCodigo']);

Route::get('/get-select-modalidad-proceso-segundas/{id}',[VacantesController::class, 'getSelectModalidadesProceso']);
Route::post('/get-select-programas-proceso-segundas',[VacantesController::class, 'getSelectProgramasProceso']);



Route::get('/distribucion', [TestController::class, 'Distribucion']);
Route::get('/pdf-lista', [TestController::class, 'pdfLista']);

Route::get('/aleatorizar', [PruebasController::class, 'aleatorizar']);

Route::middleware(['web'])->group(function () {
    Route::prefix('raiz')->group(function () {
        Route::post('/crear-carpeta', [CarpetaController::class, 'crearCarpeta']);
        Route::get('/ver-contenido-carpeta/{id}', [CarpetaController::class, 'verContenidoCarpeta']);
        Route::get('/', fn () => Inertia::render('Admin/Carpetas/index'));
    });
});

// Route::get('/', function () { return view('welcome'); })->middleware('redirect');
Route::middleware('redirect')->get('/', fn () => Inertia::render('Auth/Login'));

//RUTAS TEMPORALES
Route::post('/get-sim', [SimulacroController::class, 'getSimulacros']);
Route::post('/get-archivos', [ResultadosController::class, 'getArchivosIde']);
Route::post('/get-archivos-res', [ResultadosController::class, 'getArchivosRes']);
Route::post('/get-archivos-pat', [ResultadosController::class, 'getArchivosPat']);
Route::get('/eliminar-archivo/{id}', [ResultadosController::class, 'eliminarArchivo']);
Route::post('/get-ides', [ResultadosController::class, 'getIdes']);
Route::post('/get-res', [ResultadosController::class, 'getRes']);
Route::post('/get-pat', [ResultadosController::class, 'getPat']);
Route::post('/subir-participantes-simulacro', [ResultadosController::class, 'SubirParticipantes']);
Route::post('/get-participantes-externo', [ResultadosController::class, 'getParticipantesSimulacro']);
Route::get('/descargar-template-participantes-simulacro', [ResultadosController::class, 'descargarTemplate']);

Route::get('/ver-ficha', fn () => Inertia::render('Simulacro/Calificacion/components/ficha'));



Route::post('/get-simulacros', [SimulacroController::class, 'getSimulacrosSelect']);
Route::get('/get-ficha-respuesta/{id}', [ResultadosController::class, 'getFichaRespuesta']);

Route::get('/pdf-errores/{D}', [ResultadosController::class, 'PdfErroresCalifacion']);

Route::post('/calificar-examen', [ResultadosController::class, 'CalificarExamen']);
Route::post('/get-puntajes-examen', [ResultadosController::class, 'getPuntajes']);
Route::post('/get-pdf-resultados/{sim}', [ResultadosController::class, 'getResultadosPDF']);

Route::get('/segundas-especialidades-2026-test/preinscripcion', fn () => Inertia::render('Publico/temp/cronogram'));
Route::get('/segundas-especialidades-2026/preinscripcion', fn () => Inertia::render('Publico/temp/cronogram'));

Route::get('{p}/preinscripcion', [ProcesoController::class, 'getFormulario']);
Route::get('/get-participante-cepre/{dni}', [CepreController::class, 'getParticipanteCepre']);
Route::get('/get-sancionado/{dni}/{pro}', [SancionadoController::class, 'getSancionado']);

Route::get('/generar-captcha', [PreinscripcionController::class, 'generarCaptcha']);
Route::get('/participa-proceso/{pro}/{post}', [PreinscripcionController::class, 'estaPreinscrito']);

Route::get('/carreras-previas', fn () => Inertia::render('Publico/components/carrerasPrevias'));
Route::get('/get-data-prisma/{dni}', [PostulanteController::class, 'getDataPrisma']);

//Route::post('/subir-pagos', [PagoSimulacroController::class, 'pagosSimulacro']);
Route::post('/registrar-carreras-previas', [PostulanteController::class, 'registrarCarreras']);
Route::get('/get-paso-registrado/{p}/{dni}', [PreinscripcionController::class, 'pasoRegistrado']);

Route::get('/pdf-resultados', [ResultadosController::class, 'generarReportePrograma']);

Route::get('/ver-puntaje-alcanzado', fn () => Inertia::render('Publico/resultados'));

Route::get('/carreras-previas/{dni}', [IngresoController::class, 'carrerasPrevias']);

Route::get('/subir-archivos-pdf', fn () => Inertia::render('Publico/subir-archivos'));
Route::post('/verificar-padres', [PostulanteController::class, 'verificarPadres']);

Route::post('subir-pdf/{dni}/{cod}/{tipo}', [ResultadosController::class, 'cargaArchivoPDF']);


Route::get('/get-pago-caja/{dni}', function ($dni) {
    try {
        $response = Http::get('http://tesoreria.unap.edu.pe/services/document/?w=' . $dni . '&d=2025-12-01');
         if ($response->successful()) {
            $datosCaja = $response->json(['data']);
            if (is_array($datosCaja)) {
                $pagados = DB::table('pagos_general')
                    ->where('dni', $dni)
                    ->where('medio', 'Caja')
                    ->pluck('operacion')
                    ->toArray();
                foreach ($datosCaja as &$item) {
                    $operacion = $item['paymentTitle'] ?? null;
                    $item['estado'] = in_array($operacion, $pagados) ? 1 : 0;
                }
            }
            return response()->json($datosCaja);
        } else {
            return response()->json(['error' => 'La solicitud no fue exitosa'], $response->status());
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Se produjo un error al procesar la solicitud: ' . $e->getMessage()], 500);
    }
});


Route::get('/get-pago-BN/{dni}', [PagosController::class, 'getPagosBN_OTI']);

Route::get('/ver-cepre', [CepreController::class, 'getFilteredPostulantes']);

Route::post('/insertar-pago', [PagoSimulacroController::class, 'insertarPago']);
Route::get('/eliminar-pago/{dni}/{operacion}', [PagoSimulacroController::class, 'eliminarPago']);
Route::get('/get-pagos-dni/{dni}', [PagoSimulacroController::class, 'getPagosDNI']);

//VALIDACIONES
Route::post('/existe-celular', [ValidacionController::class, 'existeCelular']);
Route::post('/existe-correo', [ValidacionController::class, 'existeCorreo']);
Route::post('/get-apoderado-dni', [ApoderadoController::class, 'getApoderadobyDni']);

Route::get('/pdftest', [TestController::class, 'pdfTest']);

Route::post('/get-carreras-previas', [PostulanteController::class, 'getCarrerasPrevias']);


Route::get('/descargar-reglamento/{p}', [DocumentoController::class, 'descargarReglamento']);


Route::get('/sync-tables', [SyncController::class, 'syncTables']);
Route::get('/ver-pago/{concepto}', [PagoBancoController::class, 'getComprobanteConcepto']);



Route::get('{p}/resultados', [ProcesoController::class, 'getViewResultados']);
Route::get('get-puntajes-maximos-proceso/{p}', [PuntajeController::class, 'getPunajesMaximos']);

Route::get('/certificado', fn () => Inertia::render('Publico/Resultados/components/certificado'));
Route::post('/save-certificado', [CertificadoController::class, 'save']);
Route::post('/get-certificados-postulante', [CertificadoController::class, 'getCertificados']);
Route::get('/eliminar-certificado/{id}', [CertificadoController::class, 'delete']);

Route::post('/save-dni', [DniController::class, 'save']);
Route::post('/get-dnis-postulante', [DniController::class, 'getDnis']);
Route::get('/eliminar-dni/{id}', [DniController::class, 'delete']);

Route::post('/save-documentos-segundas', [DocumentoSegundaController::class, 'save']);
Route::post('/get-documentos-segundas-postulante-titulos', [DocumentoSegundaController::class, 'getTitulos']);
Route::post('/get-documentos-segundas-postulante-fotos', [DocumentoSegundaController::class, 'getFotos']);
Route::get('/eliminar-documentos-segundas/{id}', [DocumentoSegundaController::class, 'delete']);
Route::get('/verificar-documentos-preinscripcion/{d}/{p}', [DocumentoSegundaController::class, 'verificarDocumentos']);

Route::post('/get-documentos-resultados', [DocumentosResultadoController::class, 'getDocumentos']);
Route::post('/save-documento-resultado', [DocumentosResultadoController::class, 'cargarArchivoResultado'])->middleware('auth','admin');
Route::get('/delete-documento-resultados/{id}', [DocumentosResultadoController::class, 'deleteArchivo'])->middleware('auth','admin');

//DELETE LAST PROCESO
Route::post('/actualizar-verificacion', [VerificacionFotosController::class, 'updateEstado']);

#Route::get('/filial', [FilialController::class, 'index'])->name('filial-index');
Route::post('/get-fotos-verificacion', [VerificacionFotosController::class, 'getFotosVerificaion']);
Route::post('/save-filial', [VerificacionFotosController::class, 'saveFilial']);
Route::get('/eliminar-filial/{id}', [VerificacionFotosController::class, 'deleteFilial']);
Route::get('verificacion-fotos', fn () => Inertia::render('VerfificacionD/index'))->middleware('auth','simulacro');



Route::post('/export-excel', [ExcelController::class, 'export'])->name('users.export');

Route::get('/phpinfo', function () { phpinfo();});

Route::get('/notifiacion-correo', function () { return view('emails.notificaciones.notificacion_puerta'); });
#Route::get('/email-comunicado', [EmailController::class, 'enviarComunicado']);
Route::get('/prueba-correo/{a}', [EmailController::class, 'enviarCorreo']);

Route::get('/actualizar-correos-ingresantes/{actualizar}', [IngresoController::class, 'actualizarCorreos']);

Route::put('/participantes/{id}', [ResultadosController::class, 'updateParticipantes']);
Route::post('/participantes', [ResultadosController::class, 'guardarParticipante']);
Route::delete('/participantes/{id}', [ResultadosController::class, 'eliminarParticipante']);


Route::get('/verificar/{codigo}', [FirmaController::class, 'verificarFirma']);
Route::get( '/inscripcion/{codigo}/pdf', [FirmaController::class, 'verPdf']);
Route::get( '/control-biometrico/{codigo}/pdf', [FirmaController::class, 'verPdf']);

Route::get('/verificacion/{codigo}', fn ($codigo) => Inertia::render('Publico/Firma/verificar', ['codigo' => $codigo]));


// Google Login Routes
Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
});

// Postulante Routes
Route::prefix('postulante')->name('postulante.')->middleware('auth')->group(function () {
    // Páginas
    Route::get('/dashboard', [PostulanteRegistroController::class, 'dashboard'])->name('dashboard');
    Route::get('/documentos', fn () => Inertia::render('Postulante/Documentos'))->name('documentos');
    Route::get('/mis-requisitos', [PostulanteDocumentoController::class, 'getRequisitos']);
    Route::get('/mis-documentos', [PostulanteDocumentoController::class, 'misDocumentos']);
    Route::post('/subir-documento', [PostulanteDocumentoController::class, 'subirDocumento']);
    Route::get('/descargar-documento/{id}', [PostulanteDocumentoController::class, 'descargarDocumento']);
    Route::get('/preview-documento/{id}', [PostulanteDocumentoController::class, 'previewDocumento']);
    Route::post('/actualizar-documento/{id}', [PostulanteDocumentoController::class, 'actualizarDocumento']);
    Route::delete('/eliminar-documento/{id}', [PostulanteDocumentoController::class, 'eliminarDocumento']);
    Route::delete('/eliminar-documento-tipo/{idTipoDocumento}', [PostulanteDocumentoController::class, 'eliminarDocumentoPorTipo']);
    Route::post('/solicitar-revision', [PostulanteDocumentoController::class, 'solicitarRevision'])->name('solicitar-revision');
    Route::get('/estado-revision', [PostulanteDocumentoController::class, 'estadoRevision'])->name('estado-revision');
    Route::get('/seguimiento-data', [PostulanteDocumentoController::class, 'seguimientoData'])->name('seguimiento-data');
    Route::post('/fcm-token', [PostulanteDocumentoController::class, 'registrarFcmToken'])->name('fcm-token');

    // Notificaciones del postulante
    Route::get('/notificaciones', [PostulanteNotificationController::class, 'index'])->name('notificaciones');
    Route::get('/notificaciones/no-leidas', [PostulanteNotificationController::class, 'noLeidas'])->name('notificaciones.no-leidas');
    Route::post('/notificaciones/{id}/leer', [PostulanteNotificationController::class, 'marcarLeida'])->name('notificaciones.leer');
    Route::post('/notificaciones/leer-todas', [PostulanteNotificationController::class, 'marcarTodasLeidas'])->name('notificaciones.leer-todas');

    Route::get('/seguimiento', fn () => Inertia::render('Postulante/Seguimiento'))->name('seguimiento');
    Route::get('/mis-resultados', [PostulanteResultadoController::class, 'index'])->name('mis-resultados');
    Route::get('/mis-resultados/mi-rendimiento', [PostulanteResultadoController::class, 'getMiRendimiento']);
    Route::get('/mis-resultados/proceso', [PostulanteResultadoController::class, 'getResultadosProceso']);
    Route::get('/mis-acciones', fn () => Inertia::render('Postulante/MisAcciones'))->name('mis-acciones');
    Route::get('/mis-acciones/data', [AuditTrailController::class, 'getMisAcciones']);
    Route::get('/mis-acciones/documentos', [AuditTrailController::class, 'getAccionesMisDocumentos']);
    Route::get('/mis-acciones/historial', [AuditTrailController::class, 'getHistorialPreinscripciones']);

    // Registro paso a paso (GET con datos existentes, POST guarda)
    Route::get('/mis-datos', [PostulanteRegistroController::class, 'misDatos'])->name('mis-datos');
    Route::get('/paso-1', [PostulanteRegistroController::class, 'paso1'])->name('paso1');
    Route::post('/paso-1', [PostulanteRegistroController::class, 'guardarPaso1']);
    Route::get('/paso-2', [PostulanteRegistroController::class, 'paso2'])->name('paso2');
    Route::post('/paso-2', [PostulanteRegistroController::class, 'guardarPaso2']);
    Route::get('/paso-3', [PostulanteRegistroController::class, 'paso3'])->name('paso3');
    Route::post('/paso-3', [PostulanteRegistroController::class, 'guardarPaso3']);
    Route::get('/paso-4', [PostulanteRegistroController::class, 'paso4'])->name('paso4');
    Route::post('/paso-4', [PostulanteRegistroController::class, 'guardarPaso4']);
    Route::get('/paso-5', [PostulanteRegistroController::class, 'paso5'])->name('paso5');
    Route::post('/paso-5', [PostulanteRegistroController::class, 'confirmarDatos']);
    Route::get('/confirmacion/{dni}', [PostulanteRegistroController::class, 'confirmacion'])->name('confirmacion');

    // APIs internas (para axios desde Vue)
    Route::post('/api/buscar-ubigeo', [PostulanteRegistroController::class, 'buscarUbigeo'])->name('api.buscar-ubigeo');
    Route::get('/api/reniec/{dni}', [PostulanteRegistroController::class, 'consultarReniec'])->name('api.reniec');
    Route::get('/api/colegios/{ubigeo}', [PostulanteRegistroController::class, 'getColegios'])->name('api.colegios');
    Route::post('/api/validar-correo', [PostulanteRegistroController::class, 'validarCorreo'])->name('api.validar-correo');
    Route::post('/api/validar-celular', [PostulanteRegistroController::class, 'validarCelular'])->name('api.validar-celular');

    // Selección de proceso
    Route::get('/mis-procesos', [PostulanteRegistroController::class, 'getMisProcesos'])->name('mis-procesos');
    Route::post('/seleccionar-proceso', [PostulanteRegistroController::class, 'seleccionarProceso'])->name('seleccionar-proceso');
});

require __DIR__.'/auth.php';
require __DIR__.'/segundas.php';
require __DIR__.'/adminv2.php';
