<!--
  Aviso de postulación a segundo programa: el bloque avisa, pero no grita.
  Antes era un rectángulo rojo plano con etiquetas blancas ilegibles.
-->
<template>
  <div class="ant-prev">
    <div class="ant-prev-head">
      <span class="ant-prev-tag">Postulante a segundo programa</span>
    </div>
    <a-row :gutter="16">
    <a-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
        <a-form-item>
        <label class="ant-prev-label">Carrera</label>
        <a-input v-model:value="item.career" />
        </a-form-item>
    </a-col>
    <a-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
        <a-form-item>
        <label class="ant-prev-label">Código</label>
        <a-input v-model:value="item.code" />
        </a-form-item>
    </a-col>
    
    <!-- <a-col :xs="24" :sm="24" :md="24" :lg="18" :xl="18">
        <a-form-item>
        <label class="ant-prev-label">Carrera</label>
        <a-input v-model:value="item.career" />
        </a-form-item>
    </a-col> -->

    <a-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6">
        <a-form-item>
        <label class="ant-prev-label">Condición</label>
        <a-input v-model:value="item.cond1tion" />
        </a-form-item>
    </a-col>
  </a-row>
  </div>
</template>


<script setup>
import { ref } from 'vue';
import Icon,{ SolutionOutlined } from '@ant-design/icons-vue';

const comprobantes = ref([]);

const c = ref(0)
const props = defineProps({
  item: {
    type: Object,
    default: '',
  }
});

// const getComprobantes = async () => {
//   let res = await axios.post('get-comprobantes',
//   { dni: props.dni });
//   comprobantes.value = res.data.datos;
// }
// const getEstadoEstudiante = async() => {
//   try {
//     const response = await axios.get('https://service2.unap.edu.pe/CONDITIONS/?w='+anteriores, 
//     { headers: { 'Content-Type': 'application/json'}  });
//     anteriores.value = response.data;
//   } catch (error) {
//     console.error('Error:', error);
//   }
// };

//getEstadoEstudiante()
</script>

<style scoped>
.ant-prev {
  margin-bottom: var(--rev-s-5);
  padding: var(--rev-s-5) var(--rev-s-6) 0;
  background: var(--rev-warning-bg);
  border: 1px solid var(--rev-warning-border);
  border-left: 3px solid var(--rev-warning);
  border-radius: var(--rev-r-lg);
}
.ant-prev-head { margin-bottom: var(--rev-s-4); }
.ant-prev-tag {
  font-size: var(--rev-fs-2xs);
  font-weight: 680;
  letter-spacing: var(--rev-track-caps);
  text-transform: uppercase;
  color: var(--rev-warning-ink);
}
.ant-prev-label {
  display: block;
  margin-bottom: 3px;
  font-size: var(--rev-fs-sm);
  font-weight: 600;
  color: var(--rev-warning-ink);
}
</style>
