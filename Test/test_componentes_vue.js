const fs = require('fs');
const path = require('path');

const BASE = path.resolve(__dirname, '..');

let passed = 0;
let failed = 0;

function assert(cond, msg) {
  if (cond) { passed++; console.log('  ✅ ' + msg); }
  else { failed++; console.log('  ❌ ' + msg); }
}

console.log('\n🧪 Test: Componentes Vue de preinscripción pregrado\n');

const componentDir = path.join(BASE, 'resources/js/Pages/Publico/components/preinscripcion');

// 1. IdentityValidation
const identity = fs.readFileSync(path.join(componentDir, 'IdentityValidation.vue'), 'utf8');
assert(identity.includes('defineProps'), 'IdentityValidation define props');
assert(identity.includes('formState'), 'IdentityValidation recibe formState');
assert(identity.includes('codigo_aleatorio'), 'IdentityValidation recibe codigo_aleatorio');
assert(identity.includes('validateCodigoSecreto'), 'IdentityValidation recibe validateCodigoSecreto');
assert(identity.includes("defineEmits(['proceed'])"), 'IdentityValidation emite proceed');
assert(identity.includes('setFormRef'), 'IdentityValidation usa setFormRef');
assert(identity.includes('onMounted'), 'IdentityValidation usa onMounted para setFormRef');

// 2. PersonalDataForm
const personal = fs.readFileSync(path.join(componentDir, 'PersonalDataForm.vue'), 'utf8');
assert(personal.includes('defineProps'), 'PersonalDataForm define props');
assert(personal.includes('datospersonales'), 'PersonalDataForm recibe datospersonales');
assert(personal.includes('setFormDatosPersonales'), 'PersonalDataForm usa setFormDatosPersonales');
assert(personal.includes('validateCorreo'), 'PersonalDataForm recibe validateCorreo');
assert(personal.includes('validateCelular'), 'PersonalDataForm recibe validateCelular');
assert(personal.includes('validateFechaNacimiento'), 'PersonalDataForm recibe validateFechaNacimiento');
assert(personal.includes('onMounted'), 'PersonalDataForm usa onMounted');

// 3. ResidenceDataForm
const residence = fs.readFileSync(path.join(componentDir, 'ResidenceDataForm.vue'), 'utf8');
assert(residence.includes('defineProps'), 'ResidenceDataForm define props');
assert(residence.includes('datosresidencia'), 'ResidenceDataForm recibe datosresidencia');
assert(residence.includes('setFormDatosResidencia'), 'ResidenceDataForm usa setFormDatosResidencia');
assert(residence.includes('onSelectDepartamentos'), 'ResidenceDataForm recibe onSelectDepartamentos');
assert(residence.includes('departamentos'), 'ResidenceDataForm recibe departamentos');
assert(residence.includes('DownOutlined'), 'ResidenceDataForm usa DownOutlined');

// 4. SchoolDataForm
const school = fs.readFileSync(path.join(componentDir, 'SchoolDataForm.vue'), 'utf8');
assert(school.includes('defineProps'), 'SchoolDataForm define props');
assert(school.includes('datoscolegio'), 'SchoolDataForm recibe datoscolegio');
assert(school.includes('setFormDatosColegio'), 'SchoolDataForm usa setFormDatosColegio');
assert(school.includes('onSelectDepartamentosC'), 'SchoolDataForm recibe onSelectDepartamentosC');
assert(school.includes('colegios'), 'SchoolDataForm recibe colegios');
assert(school.includes('DownOutlined'), 'SchoolDataForm usa DownOutlined');

// 5. ParentDataForm (reusable)
const parent = fs.readFileSync(path.join(componentDir, 'ParentDataForm.vue'), 'utf8');
assert(parent.includes('defineProps'), 'ParentDataForm define props');
assert(parent.includes('datos: Object'), 'ParentDataForm recibe datos');
assert(parent.includes('title'), 'ParentDataForm recibe title');
assert(parent.includes('setFormRef'), 'ParentDataForm usa setFormRef');
assert(parent.includes('onMounted'), 'ParentDataForm usa onMounted');

// 6. AdditionalDataForm
const additional = fs.readFileSync(path.join(componentDir, 'AdditionalDataForm.vue'), 'utf8');
assert(additional.includes('defineProps'), 'AdditionalDataForm define props');
assert(additional.includes('datos_transversales'), 'AdditionalDataForm recibe datos_transversales');
assert(additional.includes('setFormDatosTransversales'), 'AdditionalDataForm usa setFormDatosTransversales');
assert(additional.includes('condiciones_lengua'), 'AdditionalDataForm recibe condiciones_lengua');
assert(additional.includes('pueblos_indigenas'), 'AdditionalDataForm recibe pueblos_indigenas');
assert(additional.includes('tipos_discapacidad'), 'AdditionalDataForm tiene tipos_discapacidad');
assert(additional.includes('SI_LENGUA_ID'), 'AdditionalDataForm tiene SI_LENGUA_ID');
assert(additional.includes('SI_PUEBLO_ID'), 'AdditionalDataForm tiene SI_PUEBLO_ID');

// 7. PostulationDataForm
const postulation = fs.readFileSync(path.join(componentDir, 'PostulationDataForm.vue'), 'utf8');
assert(postulation.includes('defineProps'), 'PostulationDataForm define props');
assert(postulation.includes('datos_preinscripcion'), 'PostulationDataForm recibe datos_preinscripcion');
assert(postulation.includes('setFormPreinscripcion'), 'PostulationDataForm usa setFormPreinscripcion');
assert(postulation.includes('modalidades'), 'PostulationDataForm recibe modalidades');
assert(postulation.includes('programas'), 'PostulationDataForm recibe programas');
assert(postulation.includes('toggleSelection2'), 'PostulationDataForm recibe toggleSelection2');
assert(postulation.includes('id_modalidad_proceso'), 'PostulationDataForm recibe id_modalidad_proceso');

// 8. SuccessMessage
const success = fs.readFileSync(path.join(componentDir, 'SuccessMessage.vue'), 'utf8');
assert(success.includes("defineEmits"), 'SuccessMessage define emits');
assert(success.includes('downloadSolicitud'), 'SuccessMessage emite downloadSolicitud');
assert(success.includes('download'), 'SuccessMessage emite download');
assert(success.includes('diagnostico'), 'SuccessMessage emite diagnostico');

// 9. NavigationButtons
const nav = fs.readFileSync(path.join(componentDir, 'NavigationButtons.vue'), 'utf8');
assert(nav.includes('defineProps'), 'NavigationButtons define props');
assert(nav.includes('pagina_pre'), 'NavigationButtons recibe pagina_pre');
assert(nav.includes('avance'), 'NavigationButtons recibe avance');
assert(nav.includes("defineEmits(['previous', 'next', 'verify'])"), 'NavigationButtons emite previous/next/verify');

// 10. VerificationModal
const verify = fs.readFileSync(path.join(componentDir, 'VerificationModal.vue'), 'utf8');
assert(verify.includes('defineProps'), 'VerificationModal define props');
assert(verify.includes('datospersonales'), 'VerificationModal recibe datospersonales');
assert(verify.includes('datospadre'), 'VerificationModal recibe datospadre');
assert(verify.includes('datosmadre'), 'VerificationModal recibe datosmadre');
assert(verify.includes('datos_preinscripcion'), 'VerificationModal recibe datos_preinscripcion');
assert(verify.includes("defineEmits"), 'VerificationModal define emits');
assert(verify.includes('submit'), 'VerificationModal emite submit');

// 11. CarrerasPreviasModal
const carreras = fs.readFileSync(path.join(componentDir, 'CarrerasPreviasModal.vue'), 'utf8');
assert(carreras.includes('defineProps'), 'CarrerasPreviasModal define props');
assert(carreras.includes('loading'), 'CarrerasPreviasModal recibe loading');
assert(carreras.includes('modalSancionado'), 'CarrerasPreviasModal recibe modalSancionado');
assert(carreras.includes('anteriores'), 'CarrerasPreviasModal recibe anteriores');
assert(carreras.includes('toggleSelection'), 'CarrerasPreviasModal recibe toggleSelection');
assert(carreras.includes("defineEmits(['close', 'cancel'])"), 'CarrerasPreviasModal emite close/cancel');

console.log(`\n📊 Resultado: ${passed} pasaron, ${failed} fallaron\n`);
process.exit(failed > 0 ? 1 : 0);
