<template>
  <div>
    <div class="flex h-screen bg-gray-50" style="position: relative;"> 
      <!-- <NavigationMobile /> -->
      <div class="men"  style="display: flex; width: 50px; height: 30px; position: absolute; transition: all 0.3s ease; z-index: 11; top: 20px; padding-left: 15px;" :style="{'left': sidewidth } ">
        <button @click="sidechange" class="p-1 mr-5 -ml-1 rounded-md focus:outline-none focus:shadow-outline-purple" aria-label="Menu">
          <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
          </svg>
        </button>

        <div>
          <div class="mi-titulo">{{ props.title }}</div>
        </div>
        
      </div>
      <Navigation :style="{ 'width': sidewidth }" :usuario="usu" style="transition: all 0.3s ease;" />
      <div class="flex flex-col flex-1 w-full">
        <TopMenu :usuario="usu" title="Revisión de documentos"/>
        <main class="h-full overflow-y-auto">
          <div class="container px-4 mx-auto grid">
            <h2 class="my-2 text-2xl font-semibold text-gray-700">
              <slot name="header" />
            </h2>
            <slot/>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import Navigation from './NavigationDocente.vue';
import TopMenu from "./TopMenu.vue";
//import NavigationMobile from './NavigationMobile.vue';
import {ref, onMounted} from 'vue'

const sidewidth = ref('230px')

const sidechange = () => {
  if( sidewidth.value === '0px'){
    sidewidth.value = '230px';
  }else{
    sidewidth.value = '0px';
  }
}

const props = defineProps(['title'])
// onMounted(() => {
//   getUsuario()
// });

const usu = ref(null) 
// const getUsuario =  async () => {
//   let res = await axios.post("/docente/get-usuario");
//   usu.value = res.data.datos[0];

//   console.log(usu.value)
// }


</script>
<style scoped>
.mi-titulo {
    font-size: 1.1rem;
    min-width: 250px;
    display: flex;
    font-family: Arial, Helvetica, sans-serif;
    text-transform: uppercase;
    margin-left: -12px;
    font-weight: bold; 
    margin-top: 2px; 
}
@media only screen and (max-width: 767px) {
  .men { display: none; }
  .mi-titulo{ display: none;}
}



</style>