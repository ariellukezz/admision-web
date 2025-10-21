<template>
    <header class="z-10 py-4 shadow-md" style="background-color: white; height: 64px;">
      <div class="container flex justify-between items-center px-6 mx-auto h-full md:justify-end">
        <!-- <div>
          <button @click="$page.props.showingMobileMenu = !$page.props.showingMobileMenu" class="p-1 mr-5 -ml-1 rounded-md md:hidden focus:outline-none focus:shadow-outline-purple" aria-label="Menu">
            <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
            </svg>
          </button>          
        </div> -->


        <a-dropdown :trigger="['click']">
          <a class="ant-dropdown-link" @click.prevent style="text-decoration: none; color: black;">
            <div class="flex">
              <div class="mr-2" style="margin-top: -5px; text-transform: capitalize; font-family: Arial, Helvetica, sans-serif;">
                {{ $page.props.auth.user.name }}
              </div>
              <DownOutlined />
            </div>

          </a>
          <template #overlay>
            <a-menu>
              <a-menu-item key="0">
                <button @click="dropdownOpen = ! dropdownOpen" class="relative block overflow-hidden"></button>   
                    
              </a-menu-item>
                <a-menu-divider />
                <dropdown-link class="w-full text-left" :href="route('logout')" method="post" as="button">
                    Salir del sistema
                </dropdown-link>
            </a-menu>
          </template>
        </a-dropdown>
      </div>
    </header>
</template>

<script setup>
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import { DownOutlined } from '@ant-design/icons-vue';
const usuario = defineProps(['usuario'])
</script>