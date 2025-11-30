<template>
  <aside class="modern-gradient-sidebar">
    <!-- Header -->
    <div class="sidebar-gradient-header">
      <Link :href="route('simulacro-inicio')" class="brand-container">
        <div class="logo-wrapper">
          <img src="../../assets/imagenes/logotiny.png" alt="Logo" class="logo-img" />
          <div class="logo-glow"></div>
        </div>
        <div class="brand-content">
          <div class="brand-line brand-main">DIRECCIÓN DE</div>
          <div class="brand-line brand-accent">ADMISIÓN</div>
        </div>
      </Link>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-gradient-nav">
      <div class="nav-section">
        <div class="section-title">
          <div class="title-line"></div>
          <span>INICIO</span>
          <div class="title-line"></div>
        </div>

        <ul class="nav-gradient-menu">
          <li class="nav-gradient-item" :class="{ 'nav-gradient-active': route().current('calificar-cal') }">
            <NavLink :href="route('calificar-cal')" :active="route().current('calificar-cal')" class="nav-gradient-link">
              <div class="nav-content-wrapper">
                <div class="nav-gradient-icon">
                  <div class="icon-bg"></div>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M9 5H7C5.89543 5 5 5.89543 5 7V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V7C19 5.89543 18.1046 5 17 5H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5C15 5.55228 14.5523 6 14 6H10C9.44772 6 9 5.55228 9 5Z" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M12 12H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M12 16H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M8 12H8.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M8 16H8.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                  </svg>
                </div>
                <span class="nav-gradient-text">Calificación</span>
              </div>
              <div class="nav-highlight"></div>
            </NavLink>
          </li>

          <li class="nav-gradient-item" :class="{ 'nav-gradient-active': route().current('simulacro-inscritos') }">
            <NavLink :href="route('simulacro-inscritos')" :active="route().current('simulacro-inscritos')" class="nav-gradient-link">
              <div class="nav-content-wrapper">
                <div class="nav-gradient-icon">
                  <div class="icon-bg"></div>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M14 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V8L14 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 2V8H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 13H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M16 17H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M10 9H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                  </svg>
                </div>
                <span class="nav-gradient-text">Subir resultados</span>
              </div>
              <div class="nav-highlight"></div>
            </NavLink>
          </li>

          <li class="nav-gradient-item" :class="{ 'nav-gradient-active': route().current('calificar-ponderacion') }">
            <NavLink :href="route('calificar-ponderacion')" :active="route().current('calificar-ponderacion')" class="nav-gradient-link">
              <div class="nav-content-wrapper">
                <div class="nav-gradient-icon">
                  <div class="icon-bg"></div>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M8 7H6C4.89543 7 4 7.89543 4 9V19C4 20.1046 4.89543 21 6 21H16C17.1046 21 18 20.1046 18 19V18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M16 5H18C19.1046 5 20 5.89543 20 7V16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M12 8L12 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M16 12L8 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M15 15L9 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M15 9L9 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                  </svg>
                </div>
                <span class="nav-gradient-text">Ponderaciones</span>
              </div>
              <div class="nav-highlight"></div>
            </NavLink>
          </li>
        </ul>
      </div>

      <!-- Partículas -->
      <div class="sidebar-particles">
        <div class="particle particle-1"></div>
        <div class="particle particle-2"></div>
        <div class="particle particle-3"></div>
      </div>
    </nav>
  </aside>
</template>

<script>
import NavLink from '@/Components/NavLink.vue'
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue'

export default {
  components: { NavLink, Link },
  setup() {
    let showingTwoLevelMenu = ref(false)
    return { showingTwoLevelMenu }
  },
}
</script>

<style scoped>
.modern-gradient-sidebar {
  width: 280px;
  min-height: 100vh;
  background: linear-gradient(160deg, #1a2a3a 0%, #2c3e50 50%, #34495e 100%);
  border-right: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
  box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
}

/* Header */
.sidebar-gradient-header { padding: 2rem 1.5rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); background: linear-gradient(135deg, rgba(52,152,219,0.1), rgba(41,128,185,0.05)); z-index:1; position:relative;}
.brand-container { display:flex; align-items:center; gap:1rem; text-decoration:none; transition: transform 0.3s ease; }
.brand-container:hover { transform: translateX(5px); }
.logo-wrapper { position:relative; width:50px; height:50px; }
.logo-img { width:100%; height:100%; border-radius:12px; z-index:2; filter: brightness(1.1);}
.logo-glow { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:60px;height:60px;background:linear-gradient(45deg,#3498db,#2980b9);border-radius:16px;opacity:0.3;filter:blur(10px);animation:glow 3s ease-in-out infinite alternate;}
@keyframes glow{0%{opacity:0.2;transform:translate(-50%,-50%) scale(0.95);}100%{opacity:0.4;transform:translate(-50%,-50%) scale(1.05);}}
.brand-content { display:flex; flex-direction:column; }
.brand-main { font-size:0.8rem;color:rgba(255,255,255,0.7);margin-bottom:2px;letter-spacing:0.15em; }
.brand-accent { font-size:1.3rem;font-weight:700;background:linear-gradient(135deg,#ffffff,#bdc3c7);-webkit-background-clip:text;-webkit-text-fill-color:transparent; }

/* Navigation */
.sidebar-gradient-nav { flex:1; padding:1rem 10px; position:relative; z-index:1; }
.nav-section { margin-bottom:1rem; }
.section-title { display:flex; align-items:center; gap:0.75rem; padding:0 1.5rem 1rem; color:rgba(255,255,255,0.5); font-size:0.75rem; font-weight:600; text-transform:uppercase;}
.title-line { flex:1; height:1px; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); }
.nav-gradient-menu { list-style:none; padding:0; margin:0; }
.nav-gradient-item { position:relative; margin:0.25rem 0; }
.nav-gradient-link { display:flex; align-items:center; gap:0.05rem; padding:0.575rem 0.5rem; color:rgba(255,255,255,0.8); text-decoration:none; transition: all 0.3s ease; border-radius:8px; }
.nav-gradient-link:hover { background: rgba(255,255,255,0.08); padding-left:2rem; color:white; }
.nav-gradient-active .nav-gradient-link { background: linear-gradient(135deg, rgba(52,152,219,0.2), rgba(41,128,185,0.15)); color:white; }

.nav-content-wrapper { display:flex; align-items:center; gap:0.75rem; flex:1; }
.nav-gradient-icon { width:36px; height:36px; display:flex; align-items:center; justify-content:center; position:relative; flex-shrink:0; border-radius:50%; transition: all 0.3s ease; }
.icon-bg { position:absolute; top:0; left:0; width:100%; height:100%; border-radius:50%; background: linear-gradient(135deg, #3498db, #2980b9); opacity:0; transition: opacity 0.3s ease; }
.nav-gradient-link:hover .icon-bg, .nav-gradient-active .icon-bg { opacity:0.2; }
.nav-gradient-icon svg { z-index:1; transition: transform 0.3s ease; }
.nav-gradient-link:hover .nav-gradient-icon svg { transform: scale(1.1); }

.nav-gradient-text { font-size:0.9rem; font-weight:500; white-space:nowrap; text-overflow:ellipsis; overflow:hidden; }
.nav-highlight { position:absolute; right:1rem; width:8px; height:8px; background:#3498db; border-radius:50%; top:50%; transform:translateY(-50%) scale(0); transition: all 0.3s ease; }
.nav-gradient-active .nav-highlight { transform:translateY(-50%) scale(1); opacity:1; }

/* Partículas */
.sidebar-particles { position:absolute; top:0; left:0; right:0; bottom:0; pointer-events:none; z-index:0; }
.particle { position:absolute; background: rgba(255,255,255,0.007); border-radius:50%; animation: float 6s ease-in-out infinite; }
.particle-1 { width:70px;height:70px;top:10%;left:-20px;animation-delay:0s;}
.particle-2 { width:80px;height:80px;bottom:20%;right:20px;animation-delay:2s;}
.particle-3 { width:60px;height:60px;top:50%;left:-15px;animation-delay:4s;}
@keyframes float{0%,100%{transform:translateY(0) scale(1);}50%{transform:translateY(-10px) scale(1.05);}}

/* Scroll */
.modern-gradient-sidebar::-webkit-scrollbar { width:6px; }
.modern-gradient-sidebar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
.modern-gradient-sidebar::-webkit-scrollbar-thumb { background: linear-gradient(180deg,#3498db,#2980b9); border-radius:3px; }

/* Responsive */
@media(max-width:768px){.modern-gradient-sidebar{width:260px;transform:translateX(-100%);position:fixed;z-index:1000;}.modern-gradient-sidebar.mobile-open{transform:translateX(0);}}
</style>
