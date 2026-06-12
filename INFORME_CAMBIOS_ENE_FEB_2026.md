# Informe de Cambios — Enero y Febrero 2026

**Proyecto:** Sistema de Admisión  
**Período:** 01/01/2026 — 28/02/2026  
**Repositorio:** `C:\laragon\www\admision`  
**Fecha del informe:** 18 de junio de 2026  

---

## 1. Resumen General

| Métrica | Valor |
|---|---|
| **Total de commits** | 80 |
| **Commits en Enero** | 33 |
| **Commits en Febrero** | 47 |
| **Días con actividad** | 15 |
| **Autor** | Ariel Luque (ariellukezz) |
| **Líneas insertadas** | ~47,968 |
| **Líneas eliminadas** | ~35,361 |

> **Nota:** Los commits aparecen bajo dos identidades (`Ariel Luque` — 44 commits, `ariellukezz` — 36 commits), correspondientes al mismo desarrollador con diferente configuración de git.

---

## 2. Cronología de Actividad

### Enero 2026 (33 commits — 5 días activos)

| Fecha | Commits | Tema principal |
|---|---|---|
| 15-01 | 1 | Actualización general del sistema |
| 19-01 | 8 | API de pago, traslado interno, gestión de procesos, test de diagnóstico |
| 20-01 | 1 | Consulta RENIEC para administradores |
| 23-01 | 4 | Integración de firma electrónica, horarios y credenciales |
| 26-01 | 19 | Verificación de firma electrónica, control de usuarios, inscripciones |

### Febrero 2026 (47 commits — 10 días activos)

| Fecha | Commits | Tema principal |
|---|---|---|
| 03-02 | 1 | Actualización de rutas (web.php) |
| 12-02 | 1 | Servicio de identidad cultural — segundas especialidades |
| 13-02 | 1 | Actualización de login |
| 18-02 | 3 | Servicio de admisión (carreras previas), estado, TestController |
| 20-02 | 1 | Servicio OTI — seguimiento de matrícula de postulantes |
| 23-02 | 1 | Cambios generales |
| 24-02 | 15 | Firma electrónica institucional, control biométrico, constancia de ingreso |
| 25-02 | 13 | Actualización de correos, IngresoController, ApixController |
| 26-02 | 3 | Datos biométricos (datosbiometricos.blade.php) |
| 27-02 | 8 | Actualización de constancia, InscripcionController, IngresoController |

---

## 3. Líneas de Desarrollo Identificadas

### 3.1. Firma Electrónica y Verificación (23 ene — 26 ene)
Integración inicial del sistema de firma electrónica. Múltiples iteraciones de verificación de firmas, actualización del controlador `FirmaController.php`, `UsuarioController.php` y `InscripcionController.php`. Se agregó lógica para gestionar qué usuario firma y cómo se valida la firma.

**Archivos principales:**
- `app/Http/Controllers/FirmaController.php`
- `app/Http/Controllers/InscripcionController.php`
- `app/Http/Controllers/UsuarioController.php`
- `routes/web.php`

### 3.2. API de Pago y Gestión de Procesos (19 ene)
Implementación de `api_pago_caja` con nuevas rutas en `api.php`. Actualización de `ProgramaProcesoController.php` para gestión de procesos de admisión. Se añadió soporte para traslado interno y corrección de bug de reglamento.

**Archivos principales:**
- `app/Http/Controllers/ProgramaProcesoController.php`
- `routes/api.php`

### 3.3. Consulta RENIEC (20 ene)
Integración de consulta RENIEC para administradores dentro del módulo de Gestión Técnica.

### 3.4. Horarios y Gestión de Credenciales (23 ene)
Actualización de horarios del sistema y primera fase de gestión de credenciales. Modificaciones en `solicitud.blade.php`.

### 3.5. Identidad Cultural — Segundas Especialidades (12 feb)
Adición del servicio de identidad cultural para el flujo de segundas especialidades. **Cambio significativo:** 5,656 inserciones y 2,071 eliminaciones en 25 archivos.

### 3.6. Actualización de Login (13 feb)
Refactorización del sistema de autenticación. 252 archivos modificados (incluye assets compilados).

### 3.7. Servicios OTI y Admisión (18–20 feb)
- Servicio OTI para seguimiento de matrícula de postulantes.
- Servicio de admisión para registro de carreras previas.
- Actualizaciones de estado y `TestController.php`.

### 3.8. Firma Electrónica Institucional y Control Biométrico (24 feb)
Implementación de firma electrónica institucional para control biométrico. Se actualizaron `HuellaController.php` (lectura de huellas), `IngresoController.php` y la constancia de ingreso. 234 archivos modificados (~2,059 inserciones).

### 3.9. Actualización de Constancia de Ingreso y Correos (25–27 feb)
Ronda intensiva de ajustes a la constancia de ingreso. Múltiples commits sobre `IngresoController.php` (20+ cambios en 3 días). Actualización del sistema de correos. Ajustes finales en `InscripcionController.php`, `inscripcion.blade.php` y `datosbiometricos.blade.php`.

---

## 4. Archivos Más Modificados

| # | Archivo | Commits |
|---|---|---|
| 1 | `public/build/assets/{lecturas}` | 36 |
| 2 | `public/build/assets/{impresion}` | 36 |
| 3 | `app/Http/Controllers/IngresoController.php` | 24 |
| 4 | `public/build/manifest.json` | 21 |
| 5 | `public/build/assets/{preinscripcioncepre}` | 19 |
| 6 | `app/Http/Controllers/InscripcionController.php` | 17 |
| 7 | `routes/web.php` | 13 |
| 8 | `public/build/assets/{preinscripcion}` | 8 |
| 9 | `public/build/assets/impresion` | 8 |
| 10 | `public/build/assets/lecturas` | 8 |
| 11 | `routes/api.php` | 6 |
| 12 | `resources/views/inscripcion/inscripcion.blade.php` | 6 |
| 13 | `app/Http/Controllers/FirmaController.php` | 5 |
| 14 | `resources/js/Pages/Publico/preinscripcioncepre.vue` | 4 |
| 15 | `resources/views/ingreso/datosbiometricos.blade.php` | 4 |
| 16 | `resources/js/Layouts/AuthenticatedLayout.vue` | 4 |
| 17 | `resources/js/Pages/Publico/Firma/verificar.vue` | 4 |
| 18 | `app/Http/Controllers/ProgramaProcesoController.php` | 3 |
| 19 | `app/Http/Controllers/UsuarioController.php` | 3 |
| 20 | `app/Http/Controllers/TestController.php` | 3 |

> **Nota:** Los archivos en `public/build/` son assets compilados (Vite). Su alto número de cambios refleja recompilaciones del frontend, no cambios manuales de código.

---

## 5. Observaciones

1. **Firma electrónica como hilo conductor:** El desarrollo de firma electrónica abarca todo el bimestre — desde la integración inicial en enero hasta la versión institucional con control biométrico en febrero.

2. **Constancia de ingreso como módulo crítico:** El `IngresoController.php` fue el archivo de código fuente más modificado (24 commits), concentrados principalmente en la última semana de febrero.

3. **Commits con mensajes genéricos:** Varios commits tienen mensajes como `_`, `-`, `.`, o `...` que no describen el cambio realizado. Se recomienda usar mensajes descriptivos.

4. **Cambios masivos de formateo:** Múltiples commits con ~200+ archivos y inserciones ≈ eliminaciones sugieren reformateo de código (posiblemente por Laravel Pint) mezclado con cambios funcionales.

5. **Un solo desarrollador:** Todo el trabajo fue realizado por Ariel Luque, sin merges ni trabajo en paralelo de otros contribuyentes.

---

## 6. Listado Completo de Commits

### Enero 2026

| Fecha | Mensaje |
|---|---|
| 15-01 | ... |
| 19-01 | Update solicitud.blade.php |
| 19-01 | actualización de botón para test de diagnóstico |
| 19-01 | Update api.php |
| 19-01 | api_pago_caja |
| 19-01 | actualización para traslado interno |
| 19-01 | Update ProgramaProcesoController.php |
| 19-01 | Update ProgramaProcesoController.php |
| 19-01 | gestión de años y corrección del bug de reglamento para proceso |
| 20-01 | consulta reniec para administradores en Gestión técnica consulta Reniec |
| 23-01 | integración de firma primera parte |
| 23-01 | actualización de horario y gestión de credenciales 1 |
| 23-01 | actualización de horario |
| 23-01 | Update solicitud.blade.php |
| 26-01 | actualización de firma |
| 26-01 | Update UsuarioController.php |
| 26-01 | _ |
| 26-01 | Update InscripcionController.php |
| 26-01 | Update InscripcionController.php |
| 26-01 | Update InscripcionController.php |
| 26-01 | Update InscripcionController.php |
| 26-01 | Update InscripcionController.php |
| 26-01 | Update InscripcionController.php |
| 26-01 | Update InscripcionController.php |
| 26-01 | actualización de usuario que firma |
| 26-01 | verificación de firma electrónica |
| 26-01 | Update web.php |
| 26-01 | Update web.php |
| 26-01 | verificación de firma 2 |
| 26-01 | verificación de firmas 3 |
| 26-01 | Update FirmaController.php |
| 26-01 | Update FirmaController.php |

### Febrero 2026

| Fecha | Mensaje |
|---|---|
| 03-02 | Update web.php |
| 12-02 | Agregando el servicio de identidad cultural a las segundas especialidades |
| 13-02 | actualización de login |
| 18-02 | actualización de estado |
| 18-02 | Update TestController.php |
| 18-02 | servicio de admisión carreras previas para registrar |
| 20-02 | servicio para OTI, seguimiento a la matrícula de los postulantes |
| 23-02 | _ |
| 24-02 | firma_electrónica_institucional para control biométrico |
| 24-02 | Update IngresoController.php |
| 24-02 | actualizacion de constancia de ingreso |
| 24-02 | Update IngresoController.php |
| 24-02 | Update HuellaController.php |
| 24-02 | _ |
| 24-02 | _ |
| 24-02 | Update IngresoController.php |
| 24-02 | Update IngresoController.php |
| 24-02 | Update IngresoController.php |
| 24-02 | Update IngresoController.php |
| 24-02 | Update IngresoController.php |
| 24-02 | Update IngresoController.php |
| 24-02 | - |
| 25-02 | Update ApixController.php |
| 25-02 | Update IngresoController.php |
| 25-02 | _ |
| 25-02 | Update IngresoController.php |
| 25-02 | Update IngresoController.php |
| 25-02 | Update IngresoController.php |
| 25-02 | actualización de correos |
| 25-02 | - |
| 25-02 | _ |
| 25-02 | Update IngresoController.php |
| 25-02 | Update IngresoController.php |
| 25-02 | Update IngresoController.php |
| 25-02 | Update IngresoController.php |
| 26-02 | Update datosbiometricos.blade.php |
| 26-02 | Update datosbiometricos.blade.php |
| 26-02 | Update datosbiometricos.blade.php |
| 27-02 | Update IngresoController.php |
| 27-02 | ACTUALIZACIÓN DE CONSTANCIA |
| 27-02 | Update InscripcionController.php |
| 27-02 | Update InscripcionController.php |
| 27-02 | Update inscripcion.blade.php |
| 27-02 | Update IngresoController.php |
| 27-02 | _ |
| 27-02 | . |

---

*Informe generado automáticamente desde el historial del repositorio Git.*
