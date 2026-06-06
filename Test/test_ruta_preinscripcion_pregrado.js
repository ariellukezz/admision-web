const fs = require('fs');
const path = require('path');

const BASE = path.resolve(__dirname, '..');

let passed = 0;
let failed = 0;

function assert(cond, msg) {
  if (cond) { passed++; console.log('  ✅ ' + msg); }
  else { failed++; console.log('  ❌ ' + msg); }
}

console.log('\n🧪 Test: Ruta de preinscripción pregrado\n');

// 1. ProcesoController tiene la ruta correcta
const controllerPath = path.join(BASE, 'app/Http/Controllers/ProcesoController.php');
const controllerContent = fs.readFileSync(controllerPath, 'utf8');

assert(
  controllerContent.includes("Inertia::render('Publico/preinscripcion-pregrado'"),
  'ProcesoController renderiza preinscripcion-pregrado'
);

assert(
  controllerContent.includes("'procceso_seleccionado' => \$proceso"),
  'ProcesoController pasa procceso_seleccionado como prop'
);

assert(
  controllerContent.includes('$proceso->nivel == 1'),
  'ProcesoController usa nivel 1 para pregrado'
);

// 2. web.php tiene la ruta directa
const webPath = path.join(BASE, 'routes/web.php');
const webContent = fs.readFileSync(webPath, 'utf8');

assert(
  webContent.includes("preinscripcion-pregrado"),
  'web.php contiene ruta preinscripcion-pregrado'
);

// 3. La página principal existe
const pagePath = path.join(BASE, 'resources/js/Pages/Publico/preinscripcion-pregrado.vue');
assert(fs.existsSync(pagePath), 'Archivo preinscripcion-pregrado.vue existe');

// 4. La página usa Inertia props correctamente
const pageContent = fs.readFileSync(pagePath, 'utf8');

assert(
  pageContent.includes("defineProps(['procceso_seleccionado'])"),
  'Página define props procceso_seleccionado'
);

assert(
  pageContent.includes('usePreinscripcionPregrado'),
  'Página usa composable usePreinscripcionPregrado'
);

// 5. La página usa el Layout correcto
assert(
  pageContent.includes("LayoutPreinscripcion.vue"),
  'Página usa LayoutPreinscripcion (no LayoutPreinscripcionSegundas)'
);

// 6. La página pasa props al composable
assert(
  pageContent.includes('usePreinscripcionPregrado(props)'),
  'Página pasa props al composable'
);

console.log(`\n📊 Resultado: ${passed} pasaron, ${failed} fallaron\n`);
process.exit(failed > 0 ? 1 : 0);
