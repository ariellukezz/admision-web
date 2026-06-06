const fs = require('fs');
const path = require('path');

const BASE = path.resolve(__dirname, '..');

let passed = 0;
let failed = 0;

function assert(cond, msg) {
  if (cond) { passed++; console.log('  ✅ ' + msg); }
  else { failed++; console.log('  ❌ ' + msg); }
}

console.log('\n🧪 Test: Composable usePreinscripcionPregrado\n');

const composablePath = path.join(BASE, 'resources/js/composables/usePreinscripcionPregrado.js');
const content = fs.readFileSync(composablePath, 'utf8');

// 1. Exporta la función correcta
assert(
  content.includes('export const usePreinscripcionPregrado'),
  'Exporta usePreinscripcionPregrado'
);

// 2. Acepta props como parámetro
assert(
  content.includes('usePreinscripcionPregrado = (props)'),
  'Acepta props como parámetro'
);

// 3. Reactive objects
const reactiveObjects = [
  'formState',
  'datospersonales',
  'datosresidencia',
  'datoscolegio',
  'datospadre',
  'datosmadre',
  'datos_transversales',
  'datos_preinscripcion',
];

reactiveObjects.forEach(name => {
  assert(content.includes(`const ${name} = reactive(`), `Reactive object: ${name}`);
});

// 4. UI refs
const uiRefs = [
  'pagina_pre',
  'avance',
  'avance_current',
  'open',
  'checkbox1',
  'loading',
  'modalcarrerasprevias',
  'modalSancionado',
  'participa',
  'postulante_inscrito',
  'codigo_aleatorio',
  'anteriores',
  'carreras_previas',
];

uiRefs.forEach(name => {
  assert(content.includes(`const ${name} = ref(`), `UI ref: ${name}`);
});

// 5. Constants
const constants = ['tipo_docs', 'estados_civil', 'sexos'];
constants.forEach(name => {
  assert(content.includes(`const ${name} =`), `Constant: ${name}`);
});

// 6. Input sanitizers
const sanitizers = ['dniInput', 'nombresInput', 'pimerapellidoInput', 'celularInput'];
sanitizers.forEach(name => {
  assert(content.includes(`const ${name} =`), `Input sanitizer: ${name}`);
});

// 7. Validators
const validators = ['validateFechaNacimiento', 'validateCelular', 'validateCorreo', 'validateCodigoSecreto'];
validators.forEach(name => {
  assert(content.includes(name), `Validator: ${name}`);
});

// 8. Navigation
['next', 'prev', 'abrirModalDatos', 'cancelarInscripcion'].forEach(name => {
  assert(content.includes(`const ${name} =`), `Navigation: ${name}`);
});

// 9. Save methods
const saveMethods = [
  'saveDNI',
  'saveDatosPersonales',
  'saveDatosResidencia',
  'savecolegio',
  'saveApoderado',
  'saveApoderadoM',
  'saveAdditionalData',
  'submit',
];

saveMethods.forEach(name => {
  assert(content.includes(`const ${name} =`), `Save method: ${name}`);
});

// 10. Data loading functions
const loaders = [
  'getDatosPersonales',
  'getDatosPersonales2',
  'getCodigoAleatorio',
  'getModalidades',
  'getProgramas',
  'getProgramasArea',
  'getAreaCodigo',
  'getSancionado',
  'getParticipanteCepre',
  'consultaInscripcion',
  'consultaInscripcion2',
  'getDataPrisma',
  'getCarrerasPrevias',
  'getCarrerasPreviasPostulacion',
  'registrarPrevias',
];

loaders.forEach(name => {
  assert(content.includes(name), `Data loader: ${name}`);
});

// 11. Ubigeo helpers
const ubigeoHelpers = [
  'getDepartamentos',
  'getProvincias',
  'getDistritos',
  'getDepartamentosColegio',
  'getProvinciasColegio',
  'getDistritosColegio',
  'getColegios',
  'getUbigeo',
  'onSelectDepartamentos',
  'onSelectDepartamentosC',
];

ubigeoHelpers.forEach(name => {
  assert(content.includes(name), `Ubigeo helper: ${name}`);
});

// 12. Apoderado helpers
['getApoderado', 'getApoderadoM', 'getApoderadoDNI'].forEach(name => {
  assert(content.includes(name), `Apoderado helper: ${name}`);
});

// 13. Cultural data helpers
const culturalHelpers = [
  'getCondicionesLengua',
  'getOptionsPertenenciaCultural',
  'getLenguasIndigenas',
  'getPueblosIndigenas',
  'getInformacionAdicional',
];

culturalHelpers.forEach(name => {
  assert(content.includes(name), `Cultural helper: ${name}`);
});

// 14. External API
['getDatosApi', 'getPadreApi', 'getMadreApi'].forEach(name => {
  assert(content.includes(name), `External API: ${name}`);
});

// 15. UI helpers
const uiHelpers = ['notificacion', 'cambiarFormato', 'getDocs', 'irDiagnostico', 'descargaReglamento', 'getColegioSeleccionado'];
uiHelpers.forEach(name => {
  assert(content.includes(name), `UI helper: ${name}`);
});

// 16. Form ref setters
const setters = [
  'setFormRef',
  'setFormDatosPersonales',
  'setFormDatosResidencia',
  'setFormDatosColegio',
  'setFormDatosPadre',
  'setFormDatosMadre',
  'setFormDatosTransversales',
  'setFormPreinscripcion',
];

setters.forEach(name => {
  assert(content.includes(name), `Form setter: ${name}`);
});

// 17. Watchers
assert(content.includes('watch(buscarDep'), 'Watcher: buscarDep → getDepartamentos');
assert(content.includes('watch(buscarDepC'), 'Watcher: buscarDepC → getDepartamentosColegio');
assert(content.includes("watch(() => datospadre.dni"), 'Watcher: datospadre.dni → getApoderadoDNI');
assert(content.includes("watch(() => datosmadre.dni"), 'Watcher: datosmadre.dni → getApoderadoDNI');
assert(content.includes("watch(() => datos_preinscripcion.tipo_certificado"), 'Watcher: tipo_certificado → ejemplo modal');
assert(content.includes("watch(() => formState.dni"), 'Watcher: formState.dni → getPasoRegistrado');
assert(content.includes("watch(() => datos_preinscripcion.modalidad"), 'Watcher: modalidad → getProgramas');
assert(content.includes('watch(pagina_pre'), 'Watcher: pagina_pre → step data loading');

// 18. Uses PROCESO() function (not hardcoded)
assert(
  content.includes('const PROCESO = () => props.procceso_seleccionado.id'),
  'Usa PROCESO() function (no hardcoded)'
);

// 19. Init call
assert(content.includes('getCodigoAleatorio()'), 'Llama getCodigoAleatorio() en init');

// 20. Return block includes everything
assert(content.includes('return {'), 'Tiene return block');
assert(content.includes('programas'), 'Return incluye programas');
assert(content.includes('selectedItems'), 'Return incluye selectedItems');

console.log(`\n📊 Resultado: ${passed} pasaron, ${failed} fallaron\n`);
process.exit(failed > 0 ? 1 : 0);
