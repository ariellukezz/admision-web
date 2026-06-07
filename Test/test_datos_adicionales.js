const fs = require('fs');
const path = require('path');

const BASE = path.resolve(__dirname, '..');

let passed = 0;
let failed = 0;

function assert(cond, msg) {
  if (cond) { passed++; console.log('  ✅ ' + msg); }
  else { failed++; console.log('  ❌ ' + msg); }
}

console.log('\n🧪 Test: Datos adicionales (discapacidad + cultural)\n');

// 1. Composable incluye datos_transversales
const composablePath = path.join(BASE, 'resources/js/composables/usePreinscripcionPregrado.js');
const composable = fs.readFileSync(composablePath, 'utf8');

assert(
  composable.includes('const datos_transversales = reactive('),
  'Composable define datos_transversales como reactive'
);

// 2. Campos de discapacidad
assert(
  composable.includes('discapacidad'),
  'datos_transversales incluye discapacidad'
);
assert(
  composable.includes('tipo_discapacidad'),
  'datos_transversales incluye tipo_discapacidad'
);

// 3. Campos culturales
assert(
  composable.includes('id_condicion_lengua'),
  'datos_transversales incluye id_condicion_lengua'
);
assert(
  composable.includes('id_lengua_indigena'),
  'datos_transversales incluye id_lengua_indigena'
);
assert(
  composable.includes('id_pertenencia_cultural'),
  'datos_transversales incluye id_pertenencia_cultural'
);
assert(
  composable.includes('id_pueblo_indigena'),
  'datos_transversales incluye id_pueblo_indigena'
);

// 4. Cultural data loading functions
assert(
  composable.includes('getCondicionesLengua'),
  'Composable tiene getCondicionesLengua'
);
assert(
  composable.includes('getOptionsPertenenciaCultural'),
  'Composable tiene getOptionsPertenenciaCultural'
);
assert(
  composable.includes('getLenguasIndigenas'),
  'Composable tiene getLenguasIndigenas'
);
assert(
  composable.includes('getPueblosIndigenas'),
  'Composable tiene getPueblosIndigenas'
);
assert(
  composable.includes('getInformacionAdicional'),
  'Composable tiene getInformacionAdicional'
);

// 5. Endpoints de datos culturales (de segundas, globales)
assert(
  composable.includes('/get-condiciones-lengua-segundas'),
  'Usa endpoint /get-condiciones-lengua-segundas'
);
assert(
  composable.includes('/get-pertenencia-cultural-segundas'),
  'Usa endpoint /get-pertenencia-cultural-segundas'
);
assert(
  composable.includes('/get-lengua-segundas'),
  'Usa endpoint /get-lengua-segundas'
);
assert(
  composable.includes('/get-pueblos-indigenes-segundas'),
  'Usa endpoint /get-pueblos-indigenes-segundas'
);
assert(
  composable.includes('/get-identidad-cultural/'),
  'Usa endpoint /get-identidad-cultural/{id_postulante}/{id_proceso}'
);

// 6. saveAdditionalData
assert(
  composable.includes('const saveAdditionalData ='),
  'Composable tiene saveAdditionalData'
);
assert(
  composable.includes('/save-postulante-adicional'),
  'saveAdditionalData usa endpoint /save-postulante-adicional'
);

// 7. Watcher carga datos culturales en paso 6
assert(
  composable.includes("newValue === 6") && composable.includes('getCondicionesLengua'),
  'Watcher de pagina_pre=6 carga datos culturales'
);

// 8. Componente AdditionalDataForm existe y tiene la estructura correcta
const componentPath = path.join(BASE, 'resources/js/Pages/Publico/components/preinscripcion/AdditionalDataForm.vue');
const component = fs.readFileSync(componentPath, 'utf8');

assert(
  component.includes('discapacidad'),
  'AdditionalDataForm tiene campo discapacidad'
);
assert(
  component.includes('tipo_discapacidad'),
  'AdditionalDataForm tiene campo tipo_discapacidad'
);
assert(
  component.includes('id_condicion_lengua'),
  'AdditionalDataForm tiene campo id_condicion_lengua'
);
assert(
  component.includes('id_lengua_indigena'),
  'AdditionalDataForm tiene campo id_lengua_indigena'
);
assert(
  component.includes('id_pertenencia_cultural'),
  'AdditionalDataForm tiene campo id_pertenencia_cultural'
);
assert(
  component.includes('id_pueblo_indigena'),
  'AdditionalDataForm tiene campo id_pueblo_indigena'
);

// 9. Conditional rules (discapacidad === 1 → tipo required)
assert(
  component.includes('datos_transversales.discapacidad === 1'),
  'AdditionalDataForm tiene regla condicional para tipo_discapacidad'
);

// 10. SI_LENGUA_ID y SI_PUEBLO_ID constants
assert(
  component.includes('SI_LENGUA_ID'),
  'AdditionalDataForm tiene SI_LENGUA_ID'
);
assert(
  component.includes('SI_PUEBLO_ID'),
  'AdditionalDataForm tiene SI_PUEBLO_ID'
);

// 11. Página principal renderiza AdditionalDataForm en paso 6
const pagePath = path.join(BASE, 'resources/js/Pages/Publico/preinscripcion-pregrado.vue');
const page = fs.readFileSync(pagePath, 'utf8');

assert(
  page.includes('v-if="pagina_pre === 6"') && page.includes('AdditionalDataForm'),
  'Página renderiza AdditionalDataForm en paso 6'
);

assert(
  page.includes('saveAdditionalData'),
  'Página llama saveAdditionalData en handleNext para paso 6'
);

console.log(`\n📊 Resultado: ${passed} pasaron, ${failed} fallaron\n`);
process.exit(failed > 0 ? 1 : 0);
