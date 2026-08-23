<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import AvatarUpload from '@/features/profile/components/AvatarUpload.vue'
import ProfileForm from '@/features/profile/components/ProfileForm.vue'
import PasswordChangeForm from '@/features/profile/components/PasswordChangeForm.vue'
import AppTabs from '@/components/ui/AppTabs.vue'
import { ref } from 'vue'

const activeTab = ref('profile')

const tabs = [
  { id: 'profile', label: 'Profile Details' },
  { id: 'password', label: 'Change Password' },
]
</script>

<template>
  <DashboardLayout>
    <div class="max-w-2xl mx-auto space-y-6">
      <div>
        <h1 class="text-xl font-black text-slate-900">My Profile</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage your personal information and account security.</p>
      </div>

      <!-- Avatar Section -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <h2 class="text-sm font-bold text-slate-700 mb-5">Profile Photo</h2>
        <AvatarUpload />
      </div>

      <!-- Tabs: Profile / Password -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-5">
        <AppTabs v-model="activeTab" :tabs="tabs" />

        <div v-if="activeTab === 'profile'">
          <ProfileForm />
        </div>
        <div v-else-if="activeTab === 'password'">
          <PasswordChangeForm />
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
