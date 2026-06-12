# 🎨 Diseño Final del Login - Responsive

## 📱 Descripción del Diseño

### **Móviles (< 768px)**
Diseño **idéntico a la screenshot** que proporcionaste:

```
┌─────────────────────────────────────┐
│  [HEADER AZUL INSTITUCIONAL]        │
│                                     │
│         [Logo UNA]                  │
│   Sistema de Plataforma de Admisión │
│              PUNO · PERÚ            │
│         ─────────────               │
└─────────────────────────────────────┘
┌─────────────────────────────────────┐
│  [Logo pequeño]                     │
│     Admisión Unap                   │
│  Ingrese sus credenciales           │
│                                     │
│  [G] Continuar con Google           │
│                                     │
│  ── o inicie sesión con correo ──   │
│                                     │
│  CORREO ELECTRÓNICO          [📧]   │
│  [Input con label flotante]         │
│                                     │
│  CONTRASEÑA            [🔒] [👁️]   │
│                                     │
│  ☐ Recordarme    ¿Olvidó su...?     │
│                                     │
│  [→ Acceder al Sistema]             │
│                                     │
│  ¿No tiene cuenta? Registrarse      │
└─────────────────────────────────────┘
```

**Características:**
- ✅ Header azul institucional con gradiente
- ✅ Logo de la universidad
- ✅ Título: "Sistema de Plataforma de Admisión"
- ✅ Subtítulo: "PUNO · PERÚ"
- ✅ Línea decorativa dorada
- ✅ Formulario limpio y centrado
- ✅ Sin branding lateral (oculto en móvil)

---

### **Tablets (768px - 1023px) y Desktop (≥ 1024px)**
Diseño de **pantalla dividida en 2 columnas**:

```
┌─────────────────────────────────────────────────────────────┐
│                                                             │
│  ┌───────────────────┬───────────────────────────────────┐ │
│  │                   │                                   │ │
│  │   [BRANDING]      │   [FORMULARIO DE LOGIN]           │ │
│  │   Fondo azul      │                                   │ │
│  │   con trama       │   Logo + Título                   │ │
│  │                   │                                   │ │
│  │   Logo grande     │   [G] Google                      │ │
│  │   Universidad     │   ── o inicie con correo ──       │ │
│  │   Features        │                                   │ │
│  │                   │   Email + Password                │ │
│  │                   │   Recordarme + Forgot             │ │
│  │                   │   Botón Login                     │ │
│  │                   │                                   │ │
│  └───────────────────┴───────────────────────────────────┘ │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Características:**
- ✅ 50% izquierda: Branding con fondo azul y trama
- ✅ 50% derecha: Formulario de login
- ✅ Logo grande en branding (120x130px)
- ✅ Título: "Universidad Nacional del Altiplano"
- ✅ Features con iconos dorados ✓
- ✅ Formulario similar al móvil pero más espacioso

---

## 🎨 Colores y Estilos

### Header Móvil
```css
background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
padding: 2rem 1.5rem 1.5rem;
```

### Branding Desktop
```css
background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
/* Con trama/patrón sutil de fondo */
```

### Detalles Dorados
```css
--accent-gold: #d4a017;
/* Usado en: línea decorativa, iconos de features */
```

---

## 📐 Breakpoints

| Dispositivo | Ancho | Layout | Header |
|-------------|-------|--------|--------|
| **Móvil** | < 768px | 1 columna | ✅ Azul visible |
| **Tablet** | 768px - 1023px | 2 columnas | ❌ Oculto |
| **Desktop** | ≥ 1024px | 2 columnas | ❌ Oculto |

---

## ✨ Funcionalidades

### Comunes a todos los dispositivos:
- ✅ Login con Google OAuth
- ✅ Login tradicional (email/password)
- ✅ Toggle mostrar/ocultar contraseña
- ✅ Checkbox "Recordarme"
- ✅ Enlace recuperación de contraseña
- ✅ Enlace a registro
- ✅ Loading spinner
- ✅ Validación de errores
- ✅ Redirección por roles

### Específicas por dispositivo:

**Móvil:**
- Header azul institucional visible
- Logo pequeño (70x80px)
- Padding reducido (1.5rem 1rem)
- Sin branding lateral

**Tablet/Desktop:**
- Pantalla dividida 50/50
- Branding grande visible
- Logo branding (100-120px)
- Más padding (2-4rem)

---

## 🎯 Flujo de Login

### Tradicional
```
Email + Password → Validar → Auth::attempt() 
→ Verificar estado (debe ser 1)
→ Redirigir según rol:
  - Rol 1 → /admin/dashboard
  - Rol 2 → /revisor
  - Rol 3 → /segundas
  - Rol 6 → /simulacro
  - Rol 7 → /calificacion
```

### Google
```
Click Google → OAuth → Callback
→ Si nuevo: Crear usuario (rol 8)
→ Si existe: Actualizar google_id + foto
→ Auth::login()
→ Redirigir según rol
```

---

## 🧪 Pruebas Recomendadas

### En Móvil:
```
✅ Header azul con logo y título
✅ "Sistema de Plataforma de Admisión" visible
✅ "PUNO · PERÚ" visible
✅ Línea dorada decorativa
✅ Botón Google funcional
✅ Toggle contraseña
✅ Checkbox "Recordarme"
✅ Responsive al rotar pantalla
```

### En Tablet/Desktop:
```
✅ Dos columnas visibles (50% cada una)
✅ Branding izquierdo con fondo azul
✅ Trama/patrón de fondo visible
✅ Logo grande en branding
✅ Features con iconos dorados ✓
✅ Formulario derecho centrado
✅ Mismas funcionalidades que móvil
```

---

## 🚀 Comandos

```bash
# Desarrollo
npm run dev

# Build producción
npm run build

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ver rutas Google
php artisan route:list --path=google
```

---

## 📝 Variables de Entorno

```env
GOOGLE_CLIENT_ID=tu_client_id_de_google
GOOGLE_CLIENT_SECRET=tu_secreto_de_google
GOOGLE_REDIRECT_URI=https://tudominio.com/auth/google/callback
```

---

## 🎊 Resumen Final

### Lo que se logró:
1. ✅ **Móvil:** Diseño idéntico a la screenshot (header azul institucional)
2. ✅ **Tablet/Desktop:** Pantalla dividida en 2 con branding
3. ✅ **Funcionalidad:** Login con Google + tradicional
4. ✅ **Responsive:** Adaptable a todos los tamaños
5. ✅ **Estilo:** Colores institucionales (azul #1e3a5f, dorado #d4a017)
6. ✅ **UX:** Toggle contraseña, recordarme, loading states

### Archivos modificados:
- `resources/js/Pages/Auth/Login.vue` - Diseño completo
- `routes/web.php` - Rutas de Google
- `app/Http/Controllers/GoogleController.php` - Lógica web/móvil

---

**Fecha:** Junio 2026  
**Estado:** ✅ Listo para producción  
**Responsive:** ✅ Móvil + Tablet + Desktop  
**Funcional:** ✅ Google OAuth + Login tradicional
