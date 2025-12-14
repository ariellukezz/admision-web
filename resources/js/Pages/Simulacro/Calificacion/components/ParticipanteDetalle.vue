<template>
  <div class="participante-detalle">
    <a-descriptions :column="1" bordered size="small">
      <a-descriptions-item label="DNI">
        {{ participante.dni }}
      </a-descriptions-item>
      <a-descriptions-item label="Nombres Completos">
        {{ participante.nombres }} {{ participante.paterno }} {{ participante.materno }}
      </a-descriptions-item>
      <a-descriptions-item label="Puesto">
        {{ participante.puesto }}
      </a-descriptions-item>
      <a-descriptions-item label="Unidad Organizativa">
        {{ participante.unidad }}
      </a-descriptions-item>
      <a-descriptions-item label="Código de Puesto">
        {{ participante.cod_puesto }}
      </a-descriptions-item>
      <a-descriptions-item label="Estado IDE">
        <a-tag :color="participante.id_ide > 0 ? 'green' : 'red'">
          {{ participante.id_ide > 0 ? 'Asignado' : 'Sin asignar' }}
        </a-tag>
      </a-descriptions-item>
      <a-descriptions-item label="Área" v-if="participante.area">
        {{ participante.area }}
      </a-descriptions-item>
    </a-descriptions>
  </div>
</template>

<script setup>
defineProps({
  participante: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['actualizado']);
</script>