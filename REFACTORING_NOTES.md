# Refactorización de preinscripcion.vue

## Descripción del cambio

El archivo `preinscripcion.vue` ha sido dividido en componentes reutilizables sin perder funcionalidad. Esto mejora la mantenibilidad, legibilidad y permite reutilizar estos componentes en otras partes de la aplicación.

## Archivos creados

### Composable (Lógica compartida)
- **`resources/js/composables/usePreinscripcion.js`**
  - Contiene toda la lógica reactiva (estado) y funciones de negocio
  - Maneja llamadas API, validaciones y transformación de datos
  - Se puede reutilizar en múltiples componentes

### Componentes
- **`resources/js/Pages/Segundas/Publico/components/IdentityValidation.vue`**
  - Página 0: Formulario de validación de identidad
  - Incluye selección de tipo de documento y código de verificación

- **`resources/js/Pages/Segundas/Publico/components/PersonalDataForm.vue`**
  - Página 1: Formulario de datos personales
  - Contiene información de nombres, contacto, dirección y ubicaciones

- **`resources/js/Pages/Segundas/Publico/components/AdditionalDataForm.vue`**
  - Página 2: Datos adicionales requeridos por SUNEDU
  - Incluye discapacidad, identidad lingüística y pertenencia cultural

- **`resources/js/Pages/Segundas/Publico/components/ProgramAndDocs.vue`**
  - Página 6: Selección de programa y documentación
  - Permite elegir programa y subir documentos requeridos

- **`resources/js/Pages/Segundas/Publico/components/SuccessMessage.vue`**
  - Página 7: Mensaje de éxito
  - Muestra confirmación de preinscripción exitosa

- **`resources/js/Pages/Segundas/Publico/components/NavigationButtons.vue`**
  - Barra de navegación inferior
  - Botones de "Anterior" y "Siguiente"/"Verificar Datos"

- **`resources/js/Pages/Segundas/Publico/components/VerificationModal.vue`**
  - Modal de verificación final de datos
  - Resumen de información antes de guardar

## Estructura del archivo principal (preinscripcion.vue)

Ahora el archivo principal:

1. **Template**: Solo contiene composición de componentes y condicionales de página
2. **Script**: Importa componentes y usa el composable `usePreinscripcion`
3. **Styles**: Mantiene todos los estilos globales para los componentes

## Funcionalidad preservada

✅ Toda la funcionalidad original se mantiene:
- Validación de documentos
- Cálculo de edad mínima
- Búsqueda de ubicaciones geográficas
- Validación de correo y celular únicos
- Modal de verificación final
- Descarga de documentos
- Spinner de carga

## Cómo usar

### En preinscripcion.vue (archivo principal):

```vue
<script setup>
import { usePreinscripcion } from '@/composables/usePreinscripcion'
import IdentityValidation from './components/IdentityValidation.vue'
// ... otros imports

const props = defineProps(['procceso_seleccionado'])
const { 
  formState, 
  datospersonales, 
  // ... otros estados y funciones
} = usePreinscripcion(props)
</script>
```

### Reutilizar en otros componentes:

```vue
<script setup>
import { usePreinscripcion } from '@/composables/usePreinscripcion'

const props = defineProps(['procceso_seleccionado'])
const { formState, saveDatosPersonales } = usePreinscripcion(props)
</script>
```

## Ventajas de esta refactorización

1. **Mantenibilidad**: Cada componente tiene una responsabilidad clara
2. **Reutilización**: El composable puede usarse en otros formularios
3. **Testing**: Más fácil de testear componentes individuales
4. **Performance**: Componentes más pequeños = mejor rendimiento
5. **Escalabilidad**: Fácil agregar nuevas páginas o funcionalidades
6. **Legibilidad**: Código más organizado y fácil de seguir

## Notas importantes

- El composable `usePreinscripcion` requiere que se pase `props` como argumento para acceder a `procceso_seleccionado`
- Todos los watchers y inicializaciones están en el composable
- La reactive data se comparte entre todos los componentes a través del composable
- Los componentes reciben props explícitas para mayor claridad

## Próximos pasos opcionales

1. Crear tests unitarios para cada componente
2. Crear historias de Storybook para documentación visual
3. Extraer validaciones a un composable separado
4. Crear un componente para manejar errores global
