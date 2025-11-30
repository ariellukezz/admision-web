<template>
  <div class="tech-diamond-container">
    <slot />
  </div>
</template>

<script setup>
</script>

<style scoped>
.tech-diamond-container {
  display: flex;
  min-height: 100vh;
  align-items: center;
  justify-content: center;
  background-color: #f7f7f7;
  background-image: 
    repeating-linear-gradient(45deg, transparent, transparent 28px, rgba(0,0,0,0.01) 28px, rgba(0,0,0,0.03) 30px),
    repeating-linear-gradient(-45deg, transparent, transparent 28px, rgba(0,0,0,0.01) 28px, rgba(0,0,0,0.05) 30px),
    repeating-linear-gradient(45deg, transparent, transparent 14px, rgba(0,0,0,0.005) 14px, rgba(0,0,0,0.009) 16px),
    repeating-linear-gradient(-45deg, transparent, transparent 14px, rgba(0,0,0,0.008) 14px, rgba(0,0,0,0.007) 16px);
  background-size: 60px 60px, 60px 60px, 30px 30px, 30px 30px;
  background-position: 0 0, 15px 15px, 7px 7px, 22px 22px;
  position: relative;
  animation: backgroundFloat 20s ease-in-out infinite;
}

@keyframes backgroundFloat {
  0%, 100% { background-position: 0 0, 15px 15px, 7px 7px, 22px 22px; }
  33% { background-position: 2px 1px, 17px 16px, 9px 8px, 24px 23px; }
  66% { background-position: -1px 2px, 14px 17px, 6px 9px, 21px 24px; }
}

.tech-diamond-container::before {
  content: '';
  position: absolute;
  inset: 0;
  background: repeating-linear-gradient(45deg, transparent, transparent 56px, rgba(0,168,255,0.01) 56px, rgba(0,168,255,0.01) 58px);
  background-size: 120px 120px;
  background-position: -30px -30px;
  animation: diamondDrift 25s linear infinite;
  pointer-events: none;
  opacity: 0.3;
}

@keyframes diamondDrift {
  0% { background-position: -30px -30px; }
  100% { background-position: -150px -150px; }
}

.tech-diamond-wrapper {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 470px;
  padding: 2rem 1rem;
  position: relative;
  z-index: 1;
}

.tech-diamond-content {
  background: #ffffff;
  border: 1px solid #f3f3f4;
  border-radius: 12px;
  padding: 3rem 2.5rem;
  width: 100%;
  max-width: 400px;
  position: relative;
  overflow: hidden;
}

.tech-diamond-content::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, transparent 0%, #00a8ff 20%, #0078d4 40%, #0097e6 60%, #00a8ff 80%, transparent 100%);
  background-size: 200% 100%;
  animation: securityPulse 4s ease-in-out infinite;
  border-radius: 12px 12px 0 0;
}

@keyframes securityPulse {
  0%,100% { background-position: -200% 0; opacity: 0.7; }
  50% { background-position: 200% 0; opacity: 1; }
}

.tech-diamond-content::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image:
    repeating-linear-gradient(45deg, transparent, transparent 20px, rgba(0,168,255,0.01) 20px, rgba(0,168,255,0.01) 22px),
    repeating-linear-gradient(-45deg, transparent, transparent 20px, rgba(0,168,255,0.008) 20px, rgba(0,168,255,0.008) 22px);
  background-size: 44px 44px, 44px 44px;
  background-position: 5px 5px, 27px 27px;
  pointer-events: none;
  z-index: -1;
  animation: innerDiamondShift 8s ease-in-out infinite;
}

@keyframes innerDiamondShift {
  0%,100% { background-position: 5px 5px, 27px 27px; opacity: 0.3; }
  50% { background-position: 7px 3px, 29px 25px; opacity: 0.5; }
}

@media(min-width:768px) {
  .tech-diamond-content {
    padding: 3.5rem 3rem;
    max-width: 420px;
    border-radius: 14px;
  }
}

@media(max-width:480px) {
  .tech-diamond-container { padding-top:0; background-size:40px 40px,40px 40px,20px 20px,20px 20px; animation:none; }
  .tech-diamond-wrapper { height:100vh; padding:1rem; }
  .tech-diamond-content { padding:2.5rem 2rem; border-radius:10px; max-width:100%; width:100%; animation:none; }
}

@media(min-width:1200px) { .tech-diamond-content { max-width:440px; border-radius:16px; } }

@media(prefers-reduced-motion: reduce) {
  .tech-diamond-container, .tech-diamond-content, .tech-diamond-content::before, .tech-diamond-content::after { animation:none !important; }
  .tech-diamond-container::before { display:none; }
}

@media(prefers-color-scheme: dark) {
  .tech-diamond-container { background-color:#0a0a0a; background-image: repeating-linear-gradient(45deg, transparent, transparent 28px, rgba(255,255,255,0.02) 28px, rgba(255,255,255,0.02) 30px), repeating-linear-gradient(-45deg, transparent, transparent 28px, rgba(255,255,255,0.015) 28px, rgba(255,255,255,0.015) 30px); }
  .tech-diamond-content { background:#1a1a1a; border:1px solid #2a2a2a; box-shadow:0 2px 12px rgba(0,0,0,0.3),0 1px 6px rgba(0,0,0,0.2); }
}
</style>
