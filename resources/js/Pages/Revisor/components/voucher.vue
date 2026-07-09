<template>
<div>
    <a-row :gutter="16" class="mb-3">
        <a-col :span="12" v-for="item in comprobantes" :key="item.id">
            <a-card @click="verificar(item.id, item.verificado)" :class="item.verificado === 1? 'verde':'rojo'" style="border-radius: 12px; cursor:pointer;">
                <div class="flex justify-between mb-6">
                    <div>
                        <div class="flex justify-center align-center" style="width: 40px; height:40px; border: 1px solid #d9d9d9;">
                            <div style="margin-top: -3px;">
                                <span style="font-weight: bold; font-size: 1.9rem;">{{ (item.codigo || '').slice(-2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div>{{ (item.fecha || '').split("-").reverse().join("-") }}</div>
                        <div class="flex justify-end">
                            <span style="font-weight: bold; font-size: 1.2rem;"> {{ item.nro_operacion }} </span>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="flex justify-between">
                        <div>
                            <div style="margin-bottom: -4px;">
                                <span style="font-weight: bold; font-size: .9rem;"> {{ item.ndoc_postulante }} </span>
                            </div>
                            <span style="font-size: .9rem;"> {{ item.nombres }} {{ item.primer_apellido }} {{ item.segundo_apellido }} </span>
                        </div>

                        <div>
                        <span style="font-size: 1.5rem; font-weight: bold;"> S/ {{ Number(item.monto || 0).toFixed(2) }} </span>
                    </div>
                    </div>
                </div>
            </a-card>
        </a-col>
    </a-row>
</div>
</template>
<script setup>
import { ref } from 'vue';
const comprobantes = ref([]);

const props = defineProps({ 
    dni: { type: String, default: '' },
    proceso: { type: String, default: '' },
});

const getComprobantes = async () => {
  let res = await axios.post('get-comprobantes',
  { dni: props.dni });
  comprobantes.value = res.data.datos;
}

const verificar = async (id, estado) => {
  let res = await axios.post('verificar-comprobante',
  { id: id, estado: !estado });
  getComprobantes()
}

</script>

<style scoped>
.rojo{ color: #525252; background: white;}
.verde { background: #e3e3e3;}
</style>
