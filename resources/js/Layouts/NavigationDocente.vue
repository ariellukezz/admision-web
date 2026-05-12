<template>
  <aside  class="z-20 hidden w-64 overflow-y-auto md:block flex-shrink-0" style="background:#f3f3f3; color:black; width: 230px; border-right: 1px solid #00000010;">
 
    <div class="text-gray-200" style="font-family: Arial, Helvetica, sans-serif; font-weight: 400; font-size: 10pt;">
      <Link class="text-lg font-bold text-gray-100 mb-12"  :href="route('revisor')">
            <div style="display: flex; align-items: center; justify-content: left; margin-top: 14px; margin-left: 20px; margin-bottom: 6px;">
                <div>
                    <img src="../../assets/imagenes/logotiny.png" width="30"/>
                </div>
                <!-- <div style="width: 1px; background-color: gray; margin: 0px 10px; height: 30px; margin-top: 8px;"></div> -->
                <div style="margin: 0px 0px; color: #476175;">
                    <!-- <div style="display: flex; justify-content: center; margin-top: 5px;"><span style="font-size:.60rem; letter-spacing: .15rem; font-weight: 500;">DIRECCIÓN DE</span></div> -->
                    <div class="ml-2" style="display: flex; justify-content: left; margin-top: 8px;"><span style="font-family: Arial, Helvetica, sans-serif; font-size: 1.7rem; letter-spacing:.09rem; font-weight: 500;">DIRECCIÓN DE ADMISIÓN</span></div>
                </div>
                <!-- <div style="padding-top: 5px;">
                    <img src="../../assets/imagenes/logoDAD.png" width="30"/>
                </div> -->
            </div>
      </Link>

      <!-- <div style="border-bottom: 1px solid #f4f4f433; margin-top: 30px; margin-bottom: 10PX;"></div>
      <div class="flex justify-left mt-2 mb-3" style="margin-left: 20px;" >
          <div class="flex justify-start">
            <div style="border-radius:50%; width: 30px; height: 30px;  border: solid 1px #ff0000; overflow: hidden;">
              <img src="../../assets/imageneS/foto.jpg" width="30"/>
            </div>
          </div>
          <div>
            <div class="ml-2" style="font-weight: bold; font-size: .7rem;">Ariel Luque Cusacani</div>  
            <div class="ml-2" style="font-weight: bold; font-size: .7rem; color: #c7c7c7;">Revisor</div>
          </div>
      </div>

      <div style="border-bottom: 1px solid #f4f4f433; margin-top: 10px; margin-bottom: 20PX;"></div> -->
      
      
      <!-- <Link class="text-lg font-bold text-gray-100" :href="route('revisor')">
        <div class="flex" style="align-items: center; margin-left: 20px; margin-top: 5px;">
            <div style="margin-top: 6px;">
              <img src="../../assets/imagenes/logotiny.png" width="35"/>
            </div>
            <div style="width: 1px; background-color: gray; margin: 0px 10px; height: 30px; margin-top: 8px;"></div> -->
            
            <!-- <div style="margin-top: 12px;">
                <div class="flex justify-start"><span style=" letter-spacing: .31rem; font-size: .7rem; margin-bottom: -10px; margin-right: 5px;">DIRECCIÓN DE</span></div>
                <span style="font-size: 1.4rem; font-weight: 700; letter-spacing: .230rem; font-family: Arial, Helvetica, sans-serif">ADMISIÓN</span>
                <div style="margin-top: -16px !important; "><span style="font-size: .77rem; letter-spacing: .38rem; font-weight: 500; font-family: Arial, Helvetica, sans-serif">DE ADMISIÓN</span></div>
                <div style=" margin-top: -12px; font-size: 0.9rem; letter-spacing: 0.03rem; font-weight: 500;">nivelación - académica</div>
            </div> -->
            


      <ul class="mt-4" style="color:black;">
        <!-- <div class="ml-6" style="margin-top: 40px; margin-bottom: 10px;"> <span style="font-weight: 700; letter-spacing: normal; font-family: Arial, Helvetica, sans-serif;"> INICIO </span> </div> -->
        <div class="mt-2 w-full" style="height: 10px;">
        </div>
        <li class="relative px-0" :class="[route().current('revisor')? 'activado':'desactivado']" style="margin-bottom: -15px;">
          <NavLink :href="route('revisor')" style="padding: 8px 20px; color:black;">
            <template #icon>
              <span><div style="width: 18px;"><img src="../../assets/imagenes/iconos/icono-dashboard.png" alt=""></div></span>
            </template>
            <span style="margin-left: 5px;;">
              Dashboard
            </span>
          </NavLink>
        </li>

        <!-- <li class="relative px-6 py-3" :class="[route().current('alumno-index')? 'activado':'']">
          <NavLink :href="route('alumno-index')" :active="route().current('alumno-index')">
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                   xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
              </svg>
            </template>
            Alumnos
          </NavLink>
        </li> -->

        <li class="relative px-0" :class="[route().current('revisor-validacion')? 'activado':'desactivado']" style="margin-bottom: -15px;">
          <NavLink :href="route('revisor-validacion')" style="padding: 8px 20px; color:black;">
            <template #icon>
              <span><div style="width: 18px;"><img src="../../assets/imagenes/iconos/icono-certificado.png" alt=""></div></span>
            </template>
            <span style="margin-left: 5px;">
              Certificados
            </span>
          </NavLink>
        </li>

        <!-- <div class="ml-6" style="margin-top: 40px; margin-bottom: 10px;"> <span style="font-weight: 700; letter-spacing: normal; font-family: Arial, Helvetica, sans-serif;"> INSCRIPCIONES </span> </div> -->

        <div class="ml-6" style="margin-top: 30px; margin-bottom: 10px; border-bottom: solid 1px #9d9d9d; margin-right: 20px;"> <span style="font-weight: 700; letter-spacing: normal; font-family: Arial, Helvetica, sans-serif;"> </span> </div>

        <!-- <li class="relative px-0" :class="[route().current('revisor-documentos')? 'activado':'']"  style="margin-bottom: -15px;">
          <NavLink :href="route('revisor-documentos')" :active="route().current('revisor-documentos')" style="padding: 10px 20px; color:black;">
            <template #icon>
              <span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
              </span>
            </template>
            <span>
              Revisión
            </span>
          </NavLink>
        </li> -->

        <li class="relative px-0" :class="[route().current('foto-inscripcion')? 'activado':'desactivado']"  style="margin-bottom: -15px;">
          <NavLink :href="route('foto-inscripcion')" style="padding: 8px 20px; color:black;">
            <template #icon>
              <span><div style="width: 18px;"><img src="../../assets/imagenes/iconos/icono-camara.png" alt=""></div></span>
            </template>
            <span style="margin-left:5px;">
              Fotos
            </span>
          </NavLink>
        </li>


        <li class="relative px-0" :class="[route().current('revisor-impresion-inscripcion')? 'activado':'desactivado']"  style="margin-bottom: -15px;">
          <NavLink :href="route('revisor-impresion-inscripcion')" style="padding: 10px 20px; color:black;">
            <template #icon>
              <span><div style="width: 18px;"><img src="../../assets/imagenes/iconos/icono-impresora.png" alt=""></div></span>
            </template>
            <span style="margin-left: 5px;">
              Revisión
            </span>
          </NavLink>
        </li>


        <!-- <div class="ml-6" style="margin-top: 40px; margin-bottom: 10px;"> <span style="font-weight: 700; letter-spacing: normal; font-family: Arial, Helvetica, sans-serif;"> CONTROL BIOMETRICO </span> </div> -->
        <div class="ml-6" style="margin-top: 30px; margin-bottom: 10px; border-bottom: solid 1px #9d9d9d; margin-right: 20px;"> <span style="font-weight: 700; letter-spacing: normal; font-family: Arial, Helvetica, sans-serif;"> </span> </div>

        <li class="relative px-0" :class="[route().current('foto-biometrico')? 'activado':'desactivado']"  style="margin-bottom: -15px;">
          <NavLink :href="route('foto-biometrico')" style="padding: 8px 20px; color:black;">
            <template #icon>
              <span><div style="width: 18px;"><img src="../../assets/imagenes/iconos/icono-camara.png" alt=""></div></span>
            </template>
            <span style="margin-left: 5px;">
              Fotos bio...
            </span>
          </NavLink>
        </li>


        <li class="relative px-0" :class="[route().current('revisor-imprimir')? 'activado':'desactivado']"  style="margin-bottom: -15px; border-radius: 8px;">
          <NavLink :href="route('revisor-imprimir')" style="padding: 8px 20px; color:black;">
            <template #icon>
              <span><div style="width: 18px;"><img src="../../assets/imagenes/iconos/icono-impresora.png" alt=""></div></span>
            </template>
            <span style="margin-left: 5px;">
              Revisión bio...
            </span>
          </NavLink>
        </li>        

        <!-- <li class="relative px-0" :class="[route().current('revisor-postulantes')? 'activado':'desactivado']"  style="margin-bottom: -15px;">
          <NavLink :href="route('revisor-postulantes')" :active="route().current('revisor-postulantes')" style="padding: 10px 20px; color:black;">
            <template #icon>
              <span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
              </span>
            </template>
            <span>
              Postulantes
            </span>
          </NavLink>
        </li> -->


        <div style="border-bottom: 1px solid #f4f4f433; margin-top: 10px; margin-bottom: 20PX;"></div>
        <!-- <div class="ml-6" style="margin-bottom: 10px;"> <span style="font-weight: 700; letter-spacing: normal; font-family: Arial, Helvetica, sans-serif;"> CONFIGURACIÓN </span> </div>
 
        <li class="relative px-6 py-3" :class="[route().current('about')? 'activado':'']">
          <NavLink :href="route('about')" :active="route().current('about')">
            <template #icon>
              <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
                   stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                <path
                    d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
              </svg>
            </template>
            Revisar Documentos
          </NavLink>
        </li> -->
        
      </ul>
    </div>
  </aside>
</template>

<script>
import NavLink from '@/Components/NavLink.vue'
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue'

export default {
  components: {
    NavLink,
    Link,
  },

  setup() {
    let showingTwoLevelMenu = ref(false)

    return {
      showingTwoLevelMenu
    }
  },
}
</script>

<style scoped>
.activado{
  background: #e5e5e5;
  margin: 0px 15px 0px 15px;
  border-radius: 4px;
}
.desactivado{
  margin: 0px 15px 0px 15px;
}
.activado span{
  color: #0f579e;
  /* font-weight:bold; */
}
.borderlef{
  border-left: 3px solid #dd02b9;
}
</style>