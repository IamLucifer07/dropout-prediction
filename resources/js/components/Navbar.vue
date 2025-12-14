<template>
  <nav class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex items-center">
          <!-- Logo and App Name -->
          <Link href="/" class="flex items-center space-x-3">
            <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="text-xl font-bold text-gray-900">Dropout Prediction</span>
          </Link>
        </div>
        
        <div class="flex items-center space-x-4">
          <!-- Navigation Links -->
          <Link
            href="/"
            class="text-sm font-medium text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md"
            :class="{ 'text-indigo-600 bg-indigo-50': $page.url === '/' }"
          >
            Dashboard
          </Link>
          <Link
            href="/students"
            class="text-sm font-medium text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md"
            :class="{ 'text-indigo-600 bg-indigo-50': $page.url === '/students' }"
          >
            Create Student
          </Link>
          <Link
            href="/students-list"
            class="text-sm font-medium text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md"
            :class="{ 'text-indigo-600 bg-indigo-50': $page.url.startsWith('/students-list') || $page.url.startsWith('/students/') }"
          >
            View Students
          </Link>
          
          <!-- User Info and Dropdown Menu -->
          <div class="flex items-center space-x-3 border-l pl-4 relative">
            <span class="text-sm text-gray-600">{{ user?.name }}</span>
            <span class="text-xs text-gray-400">|</span>
            <span class="text-sm text-gray-500">{{ user?.college_name }}</span>
            
            <!-- Profile Icon Button -->
            <div class="relative" ref="dropdownRef">
              <button
                @click.stop="toggleDropdown"
                class="p-2 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
                aria-label="User menu"
              >
                <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
              </button>
              
              <!-- Dropdown Menu -->
              <div
                v-show="isDropdownOpen"
                @click.stop
                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
              >
                <Link
                  href="/settings/profile"
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="closeDropdown"
                >
                  <svg class="h-4 w-4 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                  Profile Settings
                </Link>
                <div class="border-t border-gray-200 my-1"></div>
                <button
                  @click="handleLogout"
                  class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 text-left"
                >
                  <svg class="h-4 w-4 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                  </svg>
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, onMounted, onUnmounted } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const isDropdownOpen = ref(false)
const dropdownRef = ref(null)

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value
}

const closeDropdown = () => {
  isDropdownOpen.value = false
}

const handleLogout = () => {
  closeDropdown()
  router.post('/logout')
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
