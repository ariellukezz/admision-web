<?php
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SimulacroController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\VacantesController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\ProcesoController;
use App\Http\Controllers\FilialController;
use App\Http\Controllers\VerificacionFotosController;
use App\Http\Controllers\ModalidadController;
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
use App\Http\Controllers\FotoController;
use App\Http\Controllers\PagoSimulacroController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\AdministrativoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PruebasController;
use App\Http\Controllers\ResultadosController;
use App\Http\Controllers\CarpetaController;
use App\Http\Controllers\PonderacionController;
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
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\DniController;
use App\Http\Controllers\DocumentoSegundaController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\ReglamentoController;
use App\Http\Controllers\RatioController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\CarrerasPreviasController;
use App\Http\Controllers\ResumenBiometricoController;
use App\Http\Controllers\ResumenInscripcionesController;
use App\Http\Controllers\DescargarArchivosController;
use App\Http\Controllers\SyncController;
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
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified','revisor','admin'])->name('dashboard');

Route::middleware('auth')->group(function () {
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
    Route::get('/eliminar-modalidad/{id}', [ModalidadController::class, 'deleteModalidad']);
    Route::get('/get-modalidades-activas', [ModalidadController::class, 'getModalidadesActivas']);

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

    Route::post('/get-postulantes-admin', [PostulanteController::class, 'getPostulantesAdmin']);
  #  Route::post('/get-participantes-vocacional', [vocacionalController::class, 'participantesVocacional']);

    Route::post('/save-postulante-admin', [PostulanteController::class, 'savePostulanteAdmin']);
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

    //ESTUDIOS ANTERIORES
    Route::get('/carreras-previas', fn () => Inertia::render('Admin/Estudios/carreras_previas'))->name('admin-carreras-previas');
    Route::post('/get-carreras-previas-registrado', [CarrerasPreviascontroller::class, 'getCarrerasPrevias']);
    Route::post('/save-carrera-previa', [CarrerasPreviascontroller::class, 'save']);
    //Route::post('/programas/get-programas', [ProgramaController::class, 'getProgramas']);
    Route::get('/eliminar-carrera-previa/{id}', [CarrerasPreviascontroller::class, 'delete']);


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
});

#Route::post('/get-participantes-vocacional', [vocacionalController::class, 'participantesVocacional']);

Route::prefix('revisor')->middleware('auth','revisor')->group(function () {

    Route::get('/', fn () => Inertia::render('Revisor/revisor'))->name('revisor');
    Route::get('/validacion', fn () => Inertia::render('Revisor/validacion'))->name('revisor-validacion');
    Route::get('/documentos', fn () => Inertia::render('Revisor/documentos'))->name('revisor-documentos');
    Route::get('/imprimir', fn () => Inertia::render('Revisor/imprimir', [ 'id_proceso' => auth()->user()->id_proceso]))->name('revisor-imprimir');
    Route::get('/postulantes', fn () => Inertia::render('Revisor/postulantes'))->name('revisor-postulantes');
    Route::get('/comprobantes-xd', fn () => Inertia::render('Revisor/components/voucher'));

    Route::post('/get-pagos-banco', [PagoBancoController::class, '  ']);


    Route::post('/get-certificados-revision', [DocumentoController::class, 'getCertificadosRevision']);
    Route::post('/cambiar-estado', [DocumentoController::class, 'cambiarEstado']);
    Route::post('/get-comprobantes', [SeleccionDataController::class, 'getComprobantesDNI']);
    Route::post('/get-comprobantes-banco', [PagoBancoController::class, 'getComprobantesDNI']);
    Route::post('/verificar-comprobante', [SeleccionDataController::class, 'verificarComprobante']);
    Route::post('/verificar-comprobante-proceso', [PagoBancoController::class, 'verificarComprobanteProceso']);

    Route::get('/get-requisitos', [SeleccionDataController::class, 'getRequisitos']);
    Route::post('/save-requisito', [SeleccionDataController::class, 'saveReq']);

    Route::post('/get-postulantes', [SeleccionDataController::class, 'getPostulantes']);
    Route::post('/get-postulantes-biometrico', [PostulanteController::class, 'getPostulantesBiometrico']);

    Route::post('/get-postulante-dni', [SeleccionDataController::class, '   ']);

    Route::post('/get-postulante-requisitos', [SeleccionDataController::class, 'getPostulanteRequisitos']);
    Route::post('/get-postulantes-requisitos', [SeleccionDataController::class, 'getRequisitoPostulantes']);

    Route::get('/avance', [TestController::class, 'saveAvance']);

    Route::get('/foto-inscripcion', fn () => Inertia::render('Foto/foto'))->name('foto-inscripcion');
    Route::get('/foto-biometrico', fn () => Inertia::render('Foto/fotobiometrico'))->name('foto-biometrico');
    Route::post('/guardar-foto-inscripcion', [FotoController::class, 'guardarFotoInscripcion']);
    Route::post('/guardar-foto-biometrico', [FotoController::class, 'guardarFotoBiometrico']);

    Route::post('/control-biometrico', [IngresoController::class, 'biometrico']);
    Route::post('/crear_correo_institucional', [IngresoController::class, 'crearCorreo']);
    


    Route::get('/impresion', fn () => Inertia::render('Revisor/impresion'))->name('revisor-impresion-inscripcion');
    Route::get('/get-postulante-dni/{dni}', [InscripcionController::class, 'getPostulanteByDni']);
    Route::get('/get-apoderados-postulante/{dni}', [InscripcionController::class, 'getApoderados']);
    Route::get('/get-vouchers-postulante/{dni}', [InscripcionController::class, 'getVouchers']);
    Route::get('/get-documentos-postulante/{dni}', [InscripcionController::class, 'getDocumentos']);
    Route::get('/get-preinscripciones-postulante/{dni}', [InscripcionController::class, 'getPreinscipciones']);
    Route::get('/get-inscripciones-postulante/{dni}', [InscripcionController::class, 'getInscripciones']);
    Route::get('/pdf-inscripción/{dni}', [InscripcionController::class, 'pdfInscripcion']);
    Route::post('/inscribir', [InscripcionController::class, 'Inscribir']);

    Route::get('/seguimiento', fn () => Inertia::render('Revisor/seguimiento'))->name('revisor-seguimiento');

    Route::post('/actualizar-postulante', [PostulanteController::class, 'actualizarDatos']);
    Route::post('/actualizar-ingresante', [PostulanteController::class, 'actualizarDatosIngresante']);

    Route::get('/get-ingresante/{dni}', [IngresoController::class, 'getDatosIngreso']);
    Route::get('/get-ingresante-general/{dni}', [IngresoController::class, 'getDatosIngresoGeneral']);

    Route::get('/get-codigo/{dni}', [IngresoController::class, 'getCodigo']);

    Route::get('/get-codigos-postulante/{dni}', [DocumentoController::class, 'getCodigoDNI']);

    Route::get('/nuevo-pdf-inscripcion/{dni}', [InscripcionController::class, 'pdfInscripcion']);
    Route::post('/cambiar-codigo', [DocumentoController::class, 'cambiarCodigo']);

    Route::get('/api-pagos/{parametro}', function ($parametro) {
        try {
            $response = Http::get('http://unap.scielodigital.net.pe/caja/pago_admision/server/CHECK_PAYMENT/?w=' . $parametro);
            return response($response->body(), $response->status())->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en la solicitud'], 500);
        }
    });

});
Route::get('/pdf-datos-biometrico/{dni}', [IngresoController::class, 'pdfbiometrico2'])->middleware('auth','revisor');

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



Route::prefix('calificacion')->group(function () {
    Route::get('/subir-resultado', fn () => Inertia::render('Simulacro/SubirResultado'));
    Route::get('/resultado-simulacro', fn () => Inertia::render('Simulacro/resultados'));
    Route::post('/subir-excel-simulacro', [ResultadosController::class, 'SubirResultado']);

    //CALIFICACIÓN
    Route::get('/calificacion', fn () => Inertia::render('Simulacro/Calificacion/lecturas'))->name('simulacro-calificacion');
    Route::post('/carga-ide', [ResultadosController::class, 'cargaArchivoIde']);
    Route::post('/actualizar-ide', [ResultadosController::class, 'actualizarIde']);
    

    //TEMP
    Route::post('/carga-ide/{proceso}/{area}', [ResultadosController::class, 'cargaArchivoIde'])->withoutMiddleware(['web']);
    Route::post('/carga-res/{proceso}', [ResultadosController::class, 'cargaArchivoRes'])->withoutMiddleware(['web']);
    Route::post('/carga-pat/{proceso}', [ResultadosController::class, 'cargaArchivoPat'])->withoutMiddleware(['web']);

    Route::get('/get-select-puestos', [ResultadosController::class, 'selectPuestos']);
    // Route::post('/carga-res', [ResultadosController::class, 'cargaArchivoRes']);
    Route::get('/leer-ide/{area}', [ResultadosController::class, 'leerIde']);

    Route::get('/descargar-excel', [ResultadosController::class, 'descargarExcel']);

    


});
Route::post('/get-puntaje-simulacro', [ResultadosController::class, 'getResultados']);


Route::get('/resultados-simulacro', fn () => Inertia::render('Simulacro/resultados'));

Route::get('/descargar-ingenierias', [ResultadosController::class, 'getExamenIng']);
Route::get('/descargar-biomedicas', [ResultadosController::class, 'getExamenBio']);
Route::get('/descargar-sociales', [ResultadosController::class, 'getExamenSoc']);

//PREINSCRIPCION
Route::get('/preinscripcion-adicional', fn () => Inertia::render('Publico/preinscripcion'))->name('preinscripcion');
//Route::get('/preinscripcion', fn () => Inertia::render('Publico/preinscripcion'))->name('preinscripcion');
Route::get('/preinscripcion-general', fn () => Inertia::render('Publico/preinscripciongeneral'))->name('preinscripcion-general');
Route::get('/examen-vocacional', fn () => Inertia::render('Publico/exvocacional'))->name('ex-vocacional');

Route::post('/save-respuesta', [DetalleExamenVocacionalController::class, 'saveRespuesta']);

Route::post('save-pasos-preinscripcion', [PreinscripcionController::class, 'savePasos']);
Route::post('/get-postulante-datos-personales', [PostulanteController::class, 'getPostulanteXDni']);
Route::post('/get-postulante-datos-personales2', [PostulanteController::class, 'getPostulanteXDni2']);
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
Route::get('/c-ides-bd/{area}', [ResultadosController::class, 'cargarIdeBD']);

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
Route::get('/leer-ides', fn () => Inertia::render('Simulacro/Calificacion/components/leer-ide'));
Route::get('/leer-ide/{area}', [ResultadosController::class, 'leerIde']);
Route::get('/cal', fn () => Inertia::render('Simulacro/Calificacion/calificacion'))->name('calificar-cal') ;
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

Route::get('/ver-ficha', fn () => Inertia::render('Simulacro/Calificacion/components/ficha'));

Route::get('/ponderacion', fn () => Inertia::render('Simulacro/Calificacion/ponderacion'))->name('calificar-ponderacion');

Route::post('/get-simulacros', [SimulacroController::class, 'getSimulacrosSelect']);
Route::get('/get-ficha-respuesta/{id}', [ResultadosController::class, 'getFichaRespuesta']);
Route::post('/save-ponderacion', [PonderacionController::class, 'save']);
Route::post('/get-ponderaciones', [PonderacionController::class, 'getPonderaciones']);
Route::post('/save-ponderacion-detalle', [PonderacionController::class, 'insertarPonderacion']);

Route::post('/get-ponderaciones-select', [PonderacionController::class, 'getPonderacionesSelect']);

Route::get('/calific/{a}', [ResultadosController::class, 'Calificar']);
Route::get('/pdf-errores/{D}', [ResultadosController::class, 'PdfErroresCalifacion']);

Route::post('/calificar-examen', [ResultadosController::class, 'CalificarExamen']);
Route::post('/get-puntajes-examen', [ResultadosController::class, 'getPuntajes']);
Route::post('/get-pdf-resultados/{sim}', [ResultadosController::class, 'getResultadosPDF']);

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
        $response = Http::get('http://tesoreria.unap.edu.pe/services/document/?w=' . $dni . '&d=2024-07-01');
         if ($response->successful()) {
            $datosCaja = $response->json(['data']);
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


require __DIR__.'/auth.php';
require __DIR__.'/segundas.php';
