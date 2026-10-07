<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md">
      <h2 class="text-2xl font-bold text-gray-800 mb-2">Verificación en dos pasos</h2>
      <p class="text-gray-600 mb-6">Ingresa el código de 6 dígitos enviado a tu correo.</p>

      <p v-if="$page.props.flash?.success" class="mb-4 text-green-600">{{ $page.props.flash.success }}</p>
      <p v-if="$page.props.errors?.code" class="mb-4 text-red-600">{{ $page.props.errors.code }}</p>

      <form @submit.prevent="submit">
        <input
          v-model="form.code"
          type="text"
          maxlength="6"
          placeholder="000000"
          class="w-full text-center text-3xl tracking-widest border rounded-lg p-3 mb-4"
        />
        <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 text-white rounded-lg p-3 font-semibold disabled:opacity-50">
          Verificar
        </button>
      </form>

      <button @click="resend" :disabled="resending" class="w-full mt-3 text-blue-600 hover:underline disabled:opacity-50">
        {{ resending ? 'Enviando...' : 'Reenviar código' }}
      </button>

      <button @click="cancel" class="block w-full text-center mt-4 text-gray-500 text-sm">Cancelar</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const form = useForm({ code: '' })
const resending = ref(false)

function cancel() {
  router.visit('/login')
}

function submit() {
  form.post('/2fa/verify')
}

function resend() {
  resending.value = true
  form.code = ''
  form.post('/2fa/resend', {
    preserveScroll: true,
    onFinish: () => (resending.value = false),
  })
}
</script>
