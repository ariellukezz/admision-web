const fs = require('fs');
const path = require('path');

const BASE = path.resolve(__dirname, '..');

let passed = 0;
let failed = 0;

function assert(cond, msg) {
  if (cond) { passed++; console.log('  ✅ ' + msg); }
  else { failed++; console.log('  ❌ ' + msg); }
}

console.log('\n🧪 Test: Estructura de preinscripción pregrado\n');

// 1. Composable existe
const composablePath = path.join(BASE, 'resources/js/composables/usePreinscripcionPregrado.js');
assert(fs.existsSync(composablePath), 'Composable usePreinscripcionPregrado.js existe');

// 2. Página principal existe
const pagePath = path.join(BASE, 'resources/js/Pages/Publico/preinscripcion-pregrado.vue');
assert(fs.existsSync(pagePath), 'Página preinscripcion-pregrado.vue existe');

// 3. Componentes existen (11)
const componentDir = path.join(BASE, 'resources/js/Pages/Publico/components/preinscripcion');
const expectedComponents = [
  'IdentityValidation.vue',
  'PersonalDataForm.vue',
  'ResidenceDataForm.vue',
  'SchoolDataForm.vue',
  'ParentDataForm.vue',
  'AdditionalDataForm.vue',
  'PostulationDataForm.vue',
  'SuccessMessage.vue',
  'NavigationButtons.vue',
  'VerificationModal.vue',
  'CarrerasPreviasModal.vue',
];

expectedComponents.forEach(name => {
  const filePath = path.join(componentDir, name);
  assert(fs.existsSync(filePath), `Componente ${name} existe`);
});

// 4. Composable usa props.procceso_seleccionado
const composableContent = fs.readFileSync(composablePath, 'utf8');
assert(
  composableContent.includes('props.procceso_seleccionado'),
  'Composable usa props.procceso_seleccionado (no hardcoded PROCESO)'
);

// 5. Composable incluye datos culturales
assert(
  composableContent.includes('datos_transversales'),
  'Composable incluye datos_transversales (discapacidad/cultural)'
);

// 6. Composable incluye carreras previas
assert(
  composableContent.includes('getCarrerasPrevias'),
  'Composable incluye lógica de carreras previas'
);

// 7. Composable incluye captcha
assert(
  composableContent.includes('getCodigoAleatorio'),
  'Composable incluye lógica de captcha'
);

// 8. Composable incluye sancionado
assert(
  composableContent.includes('getSancionado'),
  'Composable incluye lógica de sancionado'
);

// 9. Composable NO usa showToast (bug corregido)
assert(
  !composableContent.includes('showToast'),
  'Composable NO usa showToast (bug corregido → notificacion)'
);

// 10. Página importa todos los componentes
const pageContent = fs.readFileSync(pagePath, 'utf8');
expectedComponents.forEach(name => {
  assert(
    pageContent.includes(name),
    `Página importa componente ${name}`
  );
});

// 11. ProcesoController renderiza preinscripcion-pregrado
const controllerPath = path.join(BASE, 'app/Http/Controllers/ProcesoController.php');
const controllerContent = fs.readFileSync(controllerPath, 'utf8');
assert(
  controllerContent.includes("Inertia::render('Publico/preinscripcion-pregrado'"),
  'ProcesoController renderiza preinscripcion-pregrado para nivel 1'
);

// 12. preinscripcioncepre.vue NO fue eliminado (backup)
const ceprePath = path.join(BASE, 'resources/js/Pages/Publico/preinscripcioncepre.vue');
assert(fs.existsSync(ceprePath), 'preinscripcioncepre.vue NO fue eliminado (backup)');

console.log(`\n📊 Resultado: ${passed} pasaron, ${failed} fallaron\n`);
process.exit(failed > 0 ? 1 : 0);
