<template>
  <GuestLayout>
    <div class="login-container">
      <div class="login-card">
        <div class="card-header">
          <ApplicationLogo class="logo"/>
          <h2 class="title">Admisión Unap</h2>
          <p class="subtitle">Ingrese sus credenciales institucionales</p>
        </div>

        <div v-if="status" class="status-alert">
          <div class="alert-icon">ℹ️</div>
          <div class="alert-message">{{ status }}</div>
        </div>

        <form @submit.prevent="submit" class="login-form">
          <div class="form-group">
            <div class="input-wrapper">
              <TextInput
                id="email"
                type="email"
                style="min-width: 320px;"
                class="modern-input"
                v-model="form.email"
                required
                autofocus
                autocomplete="username"
                placeholder=""
              />
              <label for="email" class="input-label">Correo Electrónico</label>
              <div class="input-icon">📧</div>
            </div>
            <InputError class="error-message" :message="form.errors.email" />
          </div>

          <div class="form-group">
            <div class="input-wrapper">
              <TextInput
                id="password"
                :type="contra ? 'text' : 'password'"
                class="modern-input"
                v-model="form.password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
              />
              <label for="password" class="input-label">Contraseña</label>
              <div class="input-icon">🔒</div>
            </div>
            <InputError class="error-message" :message="form.errors.password" />
          </div>

          <div class="form-options">
            <label class="checkbox-label">
              <Checkbox name="remember" v-model:checked="contra" />
              Mostrar contraseña
            </label>
            <Link v-if="canResetPassword" :href="route('password.request')" class="forgot-link">
              ¿Olvidó su contraseña?
            </Link>
          </div>

          <PrimaryButton class="login-button" :disabled="form.processing">
            {{ form.processing ? 'Verificando...' : 'Acceder al Sistema' }}
          </PrimaryButton>
        </form>

        <div class="card-footer">
          <span class="security-notice">••• 🛡️ Sistema seguro ••• </span>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ canResetPassword: Boolean, status: String });

const form = useForm({ email: '', password: '', remember: false });
const contra = ref(false);

const submit = () => {
  form.post(route('login'), { onFinish: () => form.reset('password') });
};
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 5px;
  font-family: 'Inter', sans-serif;
  border-radius: 20px;
}

.login-card {
  background: #ffffff;
  border-radius: 20px;
  max-width: 400px;
  width: 100%;
  padding: 2.5rem 2rem;
  box-shadow: 0 12px 30px rgba(0,0,0,0.08);
  border: 1px solid #e0e0e0;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.login-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 40px rgba(0,0,0,0.12);
}

.card-header {
  text-align: center;
  margin-bottom: 2.5rem;
  margin-top: 2.5rem;
}

.logo { width: 80px; height: 90px; margin: 0 auto 1rem; }

.title { font-size: 2rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem; }
.subtitle { color: #475569; font-size: 1rem; font-weight: 400; }

.status-alert {
  background: #e0f2fe;
  border: 1px solid #38bdf8;
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 2rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
  color: #0c4a6e;
}

.login-form .form-group { margin-bottom: 1.8rem; }
.input-wrapper { position: relative; }

.modern-input {
  width: 100%;
  height: 56px;
  padding: 1rem 3rem 1rem 1rem;
  border: 1.8px solid #cbd5e1;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.25s ease;
}

.modern-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
  background: #ffffff;
}

.input-label {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
  pointer-events: none;
  transition: all 0.2s ease;
  background: #fafafa;
  padding: 0 0.5rem;
  font-weight: 500;

}

.modern-input:focus + .input-label,
.modern-input:not(:placeholder-shown) + .input-label {
  top: -0.6rem; font-size: 0.95rem; color: #2563eb; transform: translateY(0);
}

.input-icon { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 1.1rem; }

.password-toggle {
  position: absolute; right: 3rem; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer; font-size: 1.1rem; color: #475569;
}

.form-options {
  display: flex; justify-content: space-between; align-items: center; margin: 1.8rem 0;
}

.checkbox-label { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: #475569; font-weight: 500; }
.forgot-link { color: #2563eb; text-decoration: none; font-size: 0.875rem; transition: color 0.2s; }
.forgot-link:hover { color: #1d4ed8; text-decoration: underline; }

.login-button {
  width: 100%;
  height: 56px;
  background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
  border: none; border-radius: 12px; color: white; font-weight: 600; cursor: pointer;
  font-size: 1rem; transition: all 0.3s ease;
}

.login-button:hover { transform: translateY(-2px); box-shadow: 0 12px 35px rgba(37,99,235,0.3); }

.card-footer {
  text-align: center; margin-top: 2rem; color: #64748b; font-size: 0.875rem;
  border-top: 1px solid #e2e8f0; padding-top: 1rem;
}

@media (prefers-color-scheme: dark) {
  .login-container {
    background: radial-gradient(circle at top, #0b1220, #060a13);
  }

  .login-card {
    background: #0f172a;
    border: 1px solid #1f2937;
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.6);
  }

  .title {
    color: #f8fafc;
  }

  .subtitle {
    color: #94a3b8;
  }

  .card-footer {
    color: #94a3b8;
    border-top: 1px solid #1f2937;
  }

  .status-alert {
    background: rgba(56, 189, 248, 0.15);
    border: 1px solid #38bdf8;
    color: #e0f2fe;
  }

  .modern-input {
    background: #0b1220;
    border-color: #334155;
    color: #f1f5f9;
  }

  .modern-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
    background: #0b1220;
  }

  .input-label {
    background: #0f172a;
    color: #94a3b8;
  }

  .modern-input:focus + .input-label,
  .modern-input:not(:placeholder-shown) + .input-label {
    color: #60a5fa;
  }

  .input-icon {
    color: #94a3b8;
  }

  .checkbox-label {
    color: #cbd5e1;
  }

  .forgot-link {
    color: #60a5fa;
  }

  .forgot-link:hover {
    color: #93c5fd;
  }

  .login-button {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.35);
  }

  .login-button:hover {
    box-shadow: 0 15px 40px rgba(59, 130, 246, 0.5);
  }
}

</style>
