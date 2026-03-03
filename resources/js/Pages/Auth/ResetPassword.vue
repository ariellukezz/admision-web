<template>
    <Head title="Reset Password" />

    <GuestLayout>
        <div class="tech-diamond-form">
            <!-- Logo -->
            <Link href="/" class="flex justify-center items-center mb-8">
                <ApplicationLogo class="w-16 h-16 text-blue-600 fill-current" />
            </Link>

            <!-- Título -->
            <h2 class="text-2xl font-light text-center text-gray-800 mb-2 tracking-wide">
                Restablecer contraseña
            </h2>
            
            <!-- Descripción -->
            <p class="text-sm text-gray-500 text-center mb-8 leading-relaxed">
                Ingresa tu nueva contraseña para acceder a tu cuenta
            </p>

            <!-- Formulario -->
            <form @submit.prevent="submit" class="space-y-5">
                <!-- Email (solo lectura) -->
                <div>
                    <InputLabel for="email" value="Email" class="text-xs font-medium text-gray-600 uppercase tracking-wider" />
                    <div class="mt-1.5">
                        <TextInput 
                            id="email" 
                            type="email" 
                            class="block w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed" 
                            v-model="form.email" 
                            required 
                            autofocus 
                            autocomplete="username"
                            readonly
                            disabled
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <!-- Nueva contraseña -->
                <div>
                    <InputLabel for="password" value="Nueva contraseña" class="text-xs font-medium text-gray-600 uppercase tracking-wider" />
                    <div class="mt-1.5">
                        <TextInput 
                            id="password" 
                            type="password" 
                            class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition-colors duration-200" 
                            v-model="form.password" 
                            required 
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <!-- Confirmar contraseña -->
                <div>
                    <InputLabel for="password_confirmation" value="Confirmar contraseña" class="text-xs font-medium text-gray-600 uppercase tracking-wider" />
                    <div class="mt-1.5">
                        <TextInput 
                            id="password_confirmation" 
                            type="password" 
                            class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition-colors duration-200" 
                            v-model="form.password_confirmation" 
                            required 
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>

                <!-- Requisitos de contraseña (opcional) -->
                <div class="text-xs text-gray-500 space-y-1 bg-gray-50 p-3 rounded-lg">
                    <p class="flex items-center">
                        <svg class="w-4 h-4 mr-1.5" :class="form.password.length >= 8 ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Mínimo 8 caracteres
                    </p>
                    <p class="flex items-center">
                        <svg class="w-4 h-4 mr-1.5" :class="/[A-Z]/.test(form.password) ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Al menos una mayúscula
                    </p>
                    <p class="flex items-center">
                        <svg class="w-4 h-4 mr-1.5" :class="/[0-9]/.test(form.password) ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Al menos un número
                    </p>
                </div>

                <!-- Botón de submit -->
                <button 
                    type="submit"
                    class="w-full py-3 px-4 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 mt-6"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Restableciendo...
                    </span>
                    <span v-else>Restablecer contraseña</span>
                </button>
            </form>

            <!-- Enlace de retorno -->
            <div class="text-center mt-8">
                <Link href="/login" class="text-sm text-gray-500 hover:text-gray-700 transition-colors duration-200">
                    ← Volver al inicio de sesión
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<style scoped>
.tech-diamond-form {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
    padding: 2.5rem 2rem;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    box-shadow: 
        0 20px 40px -15px rgba(0, 0, 0, 0.2),
        inset 0 1px 1px rgba(255, 255, 255, 0.8);
    position: relative;
    overflow: hidden;
}

/* Borde superior sutil que complementa el layout */
.tech-diamond-form::before {
    content: '';
    position: absolute;
    top: 0;
    left: 10%;
    right: 10%;
    height: 2px;
    background: linear-gradient(90deg, 
        transparent 0%, 
        rgba(0, 168, 255, 0.2) 20%,
        rgba(0, 120, 212, 0.3) 50%,
        rgba(0, 168, 255, 0.2) 80%,
        transparent 100%
    );
    border-radius: 2px;
}

/* Patrón de rombos muy sutil en el fondo del formulario */
.tech-diamond-form::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        repeating-linear-gradient(45deg, 
            transparent, 
            transparent 30px, 
            rgba(0, 168, 255, 0.02) 30px, 
            rgba(0, 168, 255, 0.02) 32px
        ),
        repeating-linear-gradient(-45deg, 
            transparent, 
            transparent 30px, 
            rgba(0, 120, 212, 0.01) 30px, 
            rgba(0, 120, 212, 0.01) 32px
        );
    background-size: 60px 60px;
    pointer-events: none;
    z-index: 0;
    opacity: 0.5;
}

/* Asegura que el contenido esté sobre el patrón */
.tech-diamond-form > * {
    position: relative;
    z-index: 1;
}

/* Estilo para el input cuando está enfocado */
:deep(input:not([readonly]):focus) {
    box-shadow: 0 0 0 3px rgba(0, 168, 255, 0.1);
}

/* Responsive */
@media (max-width: 640px) {
    .tech-diamond-form {
        padding: 2rem 1.5rem;
        margin: 1rem;
        border-radius: 16px;
    }
}

/* Dark mode ajustado para el layout dark */
@media (prefers-color-scheme: dark) {
    .tech-diamond-form {
        background: rgba(26, 26, 26, 0.98);
        box-shadow: 
            0 20px 40px -15px rgba(0, 0, 0, 0.5),
            inset 0 1px 1px rgba(255, 255, 255, 0.05);
    }

    .tech-diamond-form h2 {
        color: #e5e5e5;
    }

    .tech-diamond-form p {
        color: #9ca3af;
    }

    :deep(.text-gray-600) {
        color: #d1d5db;
    }

    :deep(.bg-gray-50) {
        background-color: #262626;
        border-color: #404040;
        color: #e5e5e5;
    }

    :deep(.bg-gray-50:focus) {
        background-color: #333333;
        border-color: #00a8ff;
    }

    :deep(.bg-gray-100) {
        background-color: #1a1a1a;
        border-color: #333333;
        color: #666666;
    }

    :deep(.bg-gray-900) {
        background-color: #00a8ff;
    }

    :deep(.bg-gray-900:hover) {
        background-color: #0097e6;
    }

    :deep(.text-gray-500) {
        color: #9ca3af;
    }

    :deep(.text-gray-500:hover) {
        color: #e5e5e5;
    }

    .bg-gray-50 {
        background-color: #1a1a1a;
        border-color: #333333;
    }
}
</style>