<template>
  <div style="background:#e7e7e7a9;">
    <div class="flex h-screen bg-gray-60" style="position: relative;"> 
      <div class="men"  style="width: 50px; height: 30px; position: absolute; transition: all 0.3s ease; z-index: 11; top: 20px; padding-left: 15px;" :style="{'left': sidewidth } ">
        <button @click="sidechange" class="p-1 mr-5 -ml-1 rounded-md focus:outline-none focus:shadow-outline-purple" aria-label="Menu">
          <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
          </svg>
        </button>  
      </div>
      <!-- <NavigationMobile /> -->
      <Navigation :style="{ 'width': sidewidth }" style="transition: all 0.3s ease;" />
      <div class="flex flex-col flex-1 w-full">
        <TopMenu :usuario="usu"/>

        <main class="h-full overflow-y-auto">
          <div class="container px-4 pt-4 mx-auto grid" style="background: none; min-height: calc(100vh - 160px); align-items: flex-start; display: flex;">
            <h2 class="my-2 text-2xl font-semibold text-gray-700" >
              <slot name="header" />
            </h2>
            <slot />
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import Navigation from './NavigationCalificador.vue';
import TopMenu from "./TopMenu.vue";
import {ref, onMounted} from 'vue'

const sidewidth = ref('260px')

const sidechange = () => {
  if( sidewidth.value === '0px'){
    sidewidth.value = '260px';
  }else{
    sidewidth.value = '0px';
  }
}

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
@media only screen and (max-width: 767px) {
  .men {
    display: none;
  }
}

</style>