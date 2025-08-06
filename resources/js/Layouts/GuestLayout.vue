<template>
  <div class="login-container">
    <div class="login-wrapper">
      <div class="login-content">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
// Component logic if needed
</script>

<style scoped>
/* Base styles for all devices */
.login-container {
  display: flex;
  min-height: 100vh;
  align-items: center;
  justify-content: center;
  background: linear-gradient(#ffffff, #e0e0e093);
  background-size: 100% 100%;
}

.login-wrapper {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 470px;
  background: transparent;
}

.login-content {
  background: white;
  padding: 3.5rem 1.5rem;
  border-radius: 0.75rem;
  width: 100%;
  max-width: 28rem;
  border: 1px solid #e2e3e5d0;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.020);
}

/* Styles for screens 768px and up */
@media (min-width: 768px) {
  .login-content {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }
}

/* Full width styles only below 480px */
@media (max-width: 480px) {
  .login-container {
    padding-top: 0;
  }

  .login-wrapper {
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      box-shadow: none;
   
  }

  .login-content {
    padding: 2rem 2rem;
    border-radius: 0;
    max-width: 100%;
    width: 100%;
    background: none;
  }
}

</style>
