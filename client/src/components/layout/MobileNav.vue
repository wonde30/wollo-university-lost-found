<script setup lang="ts">
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useUiStore } from '@/stores/ui.store'
import AppDrawer from '@/components/ui/AppDrawer.vue'
import AppButton from '@/components/ui/AppButton.vue'

const authStore = useAuthStore()
const uiStore = useUiStore()
</script>

<template>
  <AppDrawer
    :open="uiStore.isMobileMenuOpen"
    title="Navigation Menu"
    placement="left"
    @close="uiStore.setMobileMenuOpen(false)"
  >
    <div class="space-y-4 py-2">
      <div class="space-y-1">
        <RouterLink
          to="/"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50"
          @click="uiStore.setMobileMenuOpen(false)"
        >
          Home
        </RouterLink>

        <RouterLink
          to="/browse"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50"
          @click="uiStore.setMobileMenuOpen(false)"
        >
          Browse Items
        </RouterLink>

        <RouterLink
          to="/track"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50"
          @click="uiStore.setMobileMenuOpen(false)"
        >
          Track Status
        </RouterLink>
      </div>

      <div class="pt-4 border-t border-slate-100">
        <template v-if="authStore.isAuthenticated">
          <RouterLink
            :to="authStore.isAdmin ? '/admin/dashboard' : authStore.isStaff ? '/staff/dashboard' : '/student/dashboard'"
            class="block mb-2"
            @click="uiStore.setMobileMenuOpen(false)"
          >
            <AppButton variant="primary" block>
              Go to Dashboard
            </AppButton>
          </RouterLink>

          <RouterLink
            to="/profile"
            class="block"
            @click="uiStore.setMobileMenuOpen(false)"
          >
            <AppButton variant="outline" block>
              Account Profile
            </AppButton>
          </RouterLink>
        </template>

        <template v-else>
          <div class="space-y-2">
            <RouterLink to="/auth/login" class="block" @click="uiStore.setMobileMenuOpen(false)">
              <AppButton variant="outline" block>Sign In</AppButton>
            </RouterLink>
            <RouterLink to="/auth/register" class="block" @click="uiStore.setMobileMenuOpen(false)">
              <AppButton variant="primary" block>Register Account</AppButton>
            </RouterLink>
          </div>
        </template>
      </div>
    </div>
  </AppDrawer>
</template>
