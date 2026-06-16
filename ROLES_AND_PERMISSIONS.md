# Sistema de Roles y Permisos - Admisión

## Descripción General
El sistema utiliza un modelo de roles basado en el campo `id_rol` en la tabla `users`. Cada usuario tiene un rol que determina sus accesos y permisos en el sistema.

---

## Roles del Sistema

### 1. **Admin (id_rol = 1)**
**Descripción:** Administrador del sistema con acceso completo.

**Permisos:**
- Acceso al panel administrativo completo (`/admin/*`)
- Gestión de usuarios y roles
- Gestión de procesos de admisión
- Gestión de programas y modalidades
- Gestión de vacantes
- Gestión de documentos y requisitos
- Gestión de inscripciones
- Gestión de resultados
- Gestión de pagos
- Gestión de reportes
- Gestión de auditoría
- Gestión de requisitos de documento
- Gestión de certificados
- Carga de documentos de resultados
- Acceso a todas las funcionalidades administrativas

**Rutas Protegidas:**
- `/admin/*` - Todas las rutas bajo el prefijo admin requieren middleware `admin`

**Métodos de Acceso:**
- Middleware: `Admin.php` (verifica `id_rol == 1`)

**Archivo de Configuración:** `app/Http/Middleware/Admin.php`

---

### 2. **Revisor (id_rol = 2)**
**Descripción:** Usuario revisor/evaluador de documentos y solicitudes de postulantes.

**Permisos:**
- Acceso al panel de revisor (`/revisor/*`)
- Visualización de solicitudes de documentos pendientes
- Revisión y evaluación de documentos
- Aprobación o rechazo de documentos
- Notificaciones de cambios en documentos
- Descarga de archivos PDF relacionados con biometría
- Visualización del dashboard del revisor
- Acceso a historial de auditoría
- Recepción de notificaciones en tiempo real

**Rutas Protegidas:**
- `/revisor/*` - Todas las rutas bajo el prefijo revisor requieren middleware `revisor`
- `/pdf-datos-biometrico/{dni}` - Acceso a PDFs con middleware `revisor`

**Métodos de Acceso:**
- Middleware: `Revisor.php` (verifica `id_rol == 2`)

**Características Especiales:**
- Recibe notificaciones no leídas (campo `notificacionesNoLeidas` en HandleInertiaRequests)
- Puede crear alias para identificarse en auditorías

**Archivo de Configuración:** `app/Http/Middleware/Revisor.php`

---

### 3. **Segundas (id_rol = 3)**
**Descripción:** Administrador del proceso de segundas admisiones.

**Permisos:**
- Acceso al panel de segundas (`/segundas/*`)
- Gestión de preinscripciones para segundas
- Gestión de postulantes en segundas
- Gestión de vacantes para segundas
- Gestión de modalidades en segundas
- Gestión de observados en segundas
- Gestión de identidades en segundas
- Actualización de preinscripciones
- Registro de inscripciones
- Visualización de resultados
- Generación de resúmenes

**Rutas Protegidas:**
- `/segundas/*` - Todas las rutas bajo el prefijo segundas requieren middleware `segundas`
- `/segundas` (ruta índice) - Acceso público con redirección a panel

**Métodos de Acceso:**
- Middleware: `Segundas.php` (verifica `id_rol == 3`)

**Archivo de Configuración:** `app/Http/Middleware/Segundas.php`

---

### 4. **Simulacro (id_rol = 6)**
**Descripción:** Responsable de la gestión de exámenes simulacros.

**Permisos:**
- Acceso al panel de simulacro (`/simulacro/*`)
- Gestión de exámenes simulacros
- Registro de participantes en simulacros
- Carga de respuestas de simulacros
- Visualización de detalles de exámenes
- Acceso a verificación de fotos

**Rutas Protegidas:**
- `/simulacro/*` - Rutas del módulo de simulacro
- `/verificacion-fotos` - Verificación de fotografías de participantes
- Rutas con middleware `simulacro`

**Métodos de Acceso:**
- Middleware: `Simulacro.php` (verifica `id_rol == 6`)

**Archivo de Configuración:** `app/Http/Middleware/Simulacro.php`

---

### 5. **Calificador (id_rol = 7)**
**Descripción:** Usuario responsable de calificación de pruebas y evaluaciones.

**Permisos:**
- Acceso al panel de calificación (`/calificacion/*`)
- Ingreso de calificaciones
- Visualización de resultados de pruebas
- Gestión de puntajes
- Acceso a detalles de exámenes vocacionales

**Rutas Protegidas:**
- `/calificacion/*` - Rutas de calificación
- Rutas con middleware `calificador`

**Métodos de Acceso:**
- Middleware: `Calificador.php` (verifica `id_rol == 7`)

**Archivo de Configuración:** `app/Http/Middleware/Calificador.php`

---

### 6. **Postulante (id_rol = 8)**
**Descripción:** Usuario postulante/candidato que se está registrando o ha postulado.

**Permisos:**
- Acceso a preinscripción
- Envío de documentos
- Visualización de estado de solicitud
- Descarga de comprobantes
- Consulta de resultados propios
- Acceso a perfil personal
- Gestión de cuenta (cambio de contraseña, actualización de datos)
- Recepción de certificados
- Acceso limitado a datos de inscripción propia

**Rutas Protegidas:**
- Rutas públicas de postulantes (sin middleware específico de rol)
- Acceso basado en autenticación general

**Rol por Defecto:**
- Este es el rol asignado por defecto a nuevos usuarios (Google Login, registro normal)

---

## Permisos de Spatie (Base de Datos)

El sistema también utiliza el paquete **Spatie Laravel-Permission** para un modelo de permisos más granular. Los permisos definidos en la base de datos incluyen:

- `ver-rol` - Visualizar roles
- `crear-rol` - Crear nuevos roles
- `editar-rol` - Editar roles existentes
- `borrar-rol` - Eliminar roles
- `ver-blog` - Visualizar blog
- `crear-blog` - Crear posts de blog
- `editar-blog` - Editar posts de blog
- `borrar-blog` - Eliminar posts de blog

**Nota:** Este sistema de permisos está parcialmente implementado. Actualmente, el acceso está principalmente controlado por `id_rol`.

---

## Tabla de Referencia Rápida

| ID Rol | Nombre | Prefijo Ruta | Middleware | Acceso Principal |
|--------|--------|--------------|-----------|-----------------|
| 1 | Admin | `/admin` | `admin` | Panel administrativo completo |
| 2 | Revisor | `/revisor` | `revisor` | Revisión de documentos |
| 3 | Segundas | `/segundas` | `segundas` | Segundas admisiones |
| 6 | Simulacro | `/simulacro` | `simulacro` | Exámenes simulacros |
| 7 | Calificador | `/calificacion` | `calificador` | Calificación de pruebas |
| 8 | Postulante | (público) | (ninguno) | Registro y solicitud |

---

## Flujos de Redirección por Rol

Al iniciar sesión (`AuthenticatedSessionController.php`), el usuario es redirigido según su rol:

```
id_rol = 1 (Admin)      → /admin/dashboard
id_rol = 2 (Revisor)    → /revisor
id_rol = 3 (Segundas)   → /segundas
id_rol = 6 (Simulacro)  → /simulacro
id_rol = 7 (Calificador) → /calificacion
id_rol = 8 (Postulante) → /
```

---

## Auditoría

El sistema implementa auditoría para:
- **Admin (id_rol = 1):** Todas las acciones se registran
- **Revisor (id_rol = 2):** Acciones especiales se marcan como `is_revisor = true`
- **Otros roles:** Acciones limitadas se registran

---

## Configuración de Middleware

Todos los middleware de rol se encuentran en: `app/Http/Middleware/`

- `Admin.php` - Verificación de admin
- `Revisor.php` - Verificación de revisor
- `Segundas.php` - Verificación de segundas
- `Simulacro.php` - Verificación de simulacro
- `Calificador.php` - Verificación de calificador
- `HandleInertiaRequests.php` - Manejo de props compartidas (incluye datos de notificaciones para revisores)

---

## Métodos Auxiliares en User Model

El modelo `User` proporciona métodos auxiliares:

```php
$user->isAdmin()     // Retorna true si id_rol == 1
$user->isRevisor()   // Retorna true si id_rol == 2
$user->hasRolId($id) // Verifica si el usuario tiene un id_rol específico
```

---

## Notas Importantes

1. **Roles sin middleware explícito:** Los roles 4 y 5 existen en la estructura pero no tienen middleware específico definido. Requieren investigación adicional.

2. **Google Login:** Los usuarios que inician sesión con Google son asignados al rol 8 (Postulante) por defecto.

3. **Campo id_proceso_actual:** Los usuarios tienen un proceso actual asignado que se utiliza en algunos contextos de autorización.

4. **Alias para Revisores:** Los revisores (id_rol = 2) pueden crear alias en `revisor_aliases` para identificarse en las auditorías.

5. **Restricción de Acceso:** Cada middleware devuelve un error 403 "No tienes permisos" cuando el usuario no tiene el rol requerido.

---

## Matriz de Acceso por Funcionalidad

### Gestión de Usuarios
- **Admin:** Crear, editar, eliminar, cambiar rol
- **Otros:** Sin acceso

### Gestión de Procesos
- **Admin:** Crear, editar, eliminar, gestionar configuración
- **Segundas:** Gestionar procesos de segundas
- **Otros:** Sin acceso

### Gestión de Documentos
- **Admin:** Crear, editar, eliminar, cargar
- **Revisor:** Revisar, aprobar/rechazar
- **Postulante:** Subir documentos propios
- **Otros:** Sin acceso

### Gestión de Calificaciones
- **Calificador:** Ingresar y gestionar calificaciones
- **Admin:** Ver y gestionar
- **Otros:** Ver solo resultados propios

### Gestión de Simulacros
- **Simulacro:** Gestionar exámenes y participantes
- **Admin:** Acceso completo
- **Postulante:** Participar en simulacros

