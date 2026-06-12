# 📱 Vista Previa del Nuevo Login

## 🎨 Diseño por Dispositivo

### 📱 Móvil (< 768px)

```
┌─────────────────────────────────────┐
│  [Header Azul Institucional]        │
│                                     │
│         [Logo UNA]                  │
│   Sistema de Plataforma de Admisión │
│              PUNO · PERÚ            │
│         ─────────────               │
└─────────────────────────────────────┘
┌─────────────────────────────────────┐
│                                     │
│  ┌─────────────────────────────┐   │
│  │  Iniciar sesión             │   │
│  │  Ingrese sus credenciales   │   │
│  │                             │   │
│  │  [G] Continuar con Google   │   │
│  │                             │   │
│  │  ── o inicie sesión con ──  │   │
│  │                             │   │
│  │  CORREO ELECTRÓNICO         │   │
│  │  [📧 correo@ejemplo.com]    │   │
│  │                             │   │
│  │  CONTRASEÑA                 │   │
│  │  [🔒 contraseña     👁️]    │   │
│  │                             │   │
│  │  ☐ Recordarme               │   │
│  │            ¿Olvidó su...?   │   │
│  │                             │   │
│  │  [→ Iniciar sesión]         │   │
│  │                             │   │
│  │  ¿No tiene cuenta?          │   │
│  │  Registrarse                │   │
│  └─────────────────────────────┘   │
│                                     │
└─────────────────────────────────────┘
```

---

### 💻 Tablet/Desktop (≥ 768px)

```
┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│  ┌─────────────────────┬─────────────────────────────────────┐ │
│  │                     │                                     │ │
│  │   [BRANDING]        │   [FORMULARIO]                      │ │
│  │                     │                                     │ │
│  │   [Logo Grande]     │   Iniciar sesión                    │ │
│  │                     │   Ingrese sus credenciales          │ │
│  │   Universidad       │                                     │ │
│  │   Nacional del      │   [G] Continuar con Google          │ │
│  │   Altiplano         │                                     │ │
│  │                     │   ── o inicie sesión con ──         │ │
│  │   Plataforma        │                                     │ │
│  │   Digital de        │   CORREO ELECTRÓNICO                │ │
│  │   Admisión 2026     │   [📧 ________________]             │ │
│  │                     │                                     │ │
│  │   ✓ Proceso         │   CONTRASEÑA                        │ │
│  │     en línea        │   [🔒 ________ 👁️]                  │ │
│  │                     │                                     │ │
│  │   ✓ Seguridad       │   ☐ Recordarme                      │ │
│  │                     │          ¿Olvidó su contraseña?     │ │
│  │   ✓ Resultados      │                                     │ │
│  │     en tiempo real  │   [→ Iniciar sesión]                │ │
│  │                     │                                     │ │
│  │                     │   ¿No tiene cuenta? Registrarse     │ │
│  │                     │                                     │ │
│  └─────────────────────┴─────────────────────────────────────┘ │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎨 Colores y Estilos

### Header Móvil
```
╔═══════════════════════════════════╗
║  Fondo: Gradiente Azul            ║
║  #1e3a5f → #2c5282                ║
║                                   ║
║  Logo: 80x90px                    ║
║  Título: Blanco, 1.5rem           ║
║  Subtítulo: Blanco, 0.875rem      ║
║  Línea decorativa: Dorada         ║
╚═══════════════════════════════════╝
```

### Branding Desktop
```
┌─────────────────────────────────┐
│  Fondo: Gradiente Azul          │
│  con patrón sutil               │
│                                 │
│  Logo: 120x130px                │
│  (blanco, con filtro)           │
│                                 │
│  Features con iconos            │
│  dorados                        │
└─────────────────────────────────┘
```

### Formulario
```
┌─────────────────────────────────┐
│  Fondo: Blanco (#ffffff)        │
│                                 │
│  Inputs:                        │
│  - Alto: 52px                   │
│  - Borde: 1.5px                 │
│  - Radius: 10px                 │
│  - Icono: 1.125rem              │
│                                 │
│  Botón Google:                  │
│  - Blanco con borde             │
│  - Logo Google SVG              │
│  - Hover: sombra                │
│                                 │
│  Botón Login:                   │
│  - Gradiente azul               │
│  - Icono flecha                 │
│  - Hover: elevación             │
└─────────────────────────────────┘
```

---

## 🔄 Estados y Validaciones

### Input Normal
```
┌─────────────────────────────┐
│ CORREO ELECTRÓNICO          │
│ [📧 correo@ejemplo.com]     │
└─────────────────────────────┘
```

### Input Focus
```
┌─────────────────────────────┐
│ CORREO ELECTRÓNICO          │
│ [📧 texto escrito] ← Azul   │
│  ↑ Borde azul, sombra       │
└─────────────────────────────┘
```

### Input con Error
```
┌─────────────────────────────┐
│ CORREO ELECTRÓNICO          │
│ [📧 email-invalido]         │
│ ⚠️ El correo no es válido   │ ← Rojo
└─────────────────────────────┘
```

### Botón Loading
```
┌─────────────────────────────┐
│  [⟳] Verificando...         │
│  ← Spinner animado          │
└─────────────────────────────┘
```

---

## 🎯 Flujo de Usuario

### Login Exitoso (Web)
```
Usuario → Click "Google" 
   ↓
Google OAuth
   ↓
Callback
   ↓
Auth::login($user)
   ↓
Redirección según rol
   ↓
Dashboard
```

### Login Exitoso (Móvil)
```
Usuario → Click "Google"
   ↓
Google OAuth
   ↓
Callback
   ↓
Token Sanctum
   ↓
unapadmisionapp://oauth/google?token=...
   ↓
App Móvil
```

### Login Fallido
```
Usuario → Email/Password
   ↓
Validación falla
   ↓
Error en campo
   ↓
Mensaje rojo debajo
```

---

## 📊 Dimensiones y Espaciado

### Móvil
```
Padding lateral: 1rem (16px)
Padding vertical: 1.5rem (24px)
Gap entre elementos: 1.5rem (24px)
Ancho máximo form: 100%
```

### Desktop
```
Padding branding: 4rem (64px)
Padding form: 3rem (48px)
Ancho máximo form: 420px
Gap entre elementos: 1.5rem (24px)
```

### Tipografía
```
Títulos: 1.875rem (30px), 700
Subtítulos: 1rem (16px), 400
Labels: 0.75rem (12px), 700, uppercase
Inputs: 1rem (16px)
Botones: 1rem (16px), 600
```

---

## 🌙 Modo Oscuro

El diseño soporta modo oscuro automático:

```
Móvil:
- Header: Se mantiene azul
- Form: #1e293b (gris oscuro azulado)
- Texto: #f1f5f9 (blanco)
- Bordes: #334155 (gris medio)

Desktop:
- Branding: Se mantiene azul
- Form: #1e293b
- Inputs: #0f172a
- Focus: Azul más brillante (#3b82f6)
```

---

## ✨ Animaciones

### Fade In al cargar
```css
@keyframes fadeIn {
  from: opacity(0), translateY(10px)
  to: opacity(1), translateY(0)
}
Duración: 0.4s
```

### Hover en botones
```css
transform: translateY(-2px)
box-shadow: 0 8px 20px rgba(...)
Duración: 0.2s
```

### Spinner loading
```css
@keyframes spin {
  to: transform: rotate(360deg)
}
Duración: 0.8s
Iteración: infinite
```

---

**Nota:** Esta es una representación ASCII del diseño. 
Para ver el diseño real, ejecuta `npm run dev` y visita `/login` en tu navegador.
