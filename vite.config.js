import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import Components from 'unplugin-vue-components/vite';
import { AntDesignVueResolver } from 'unplugin-vue-components/resolvers';

export default defineConfig({
      server: {
        // host: '0.0.0.0',
        // port: 5173,
        // strictPort: true,
        // watch: {
        //     usePolling: true,
        // },
        // hmr: {
        //     host: 'localhost',
        // },
        host: 'localhost',
        port: 5173,
        strictPort: true,
        cors: {
            origin: '*',
            methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            allowedHeaders: ['Content-Type', 'Authorization'],
        },
      },
      plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        Components({
            resolvers: [
              AntDesignVueResolver({
                importStyle: false, // css in js
              }),
            ],
          }),
    ],
    css: {
        preprocessorOptions: {
          less: {
            javascriptEnabled: true,
            lessOptions: {
                modifyVars: {
                  // Aquí puedes personalizar las variables de Less según tus preferencias
                  'primary-color': 'red', // Ejemplo: cambia el color principal
                  'link-color': '#1DA57A',    // Ejemplo: cambia el color de los enlaces
                  'border-radius-base': '12px' // Ejemplo: cambia el radio de los bordes
                },
                javascriptEnabled: true, // Deja esto en true para habilitar JavaScript en Less
              },
          },
        }
    },
});
