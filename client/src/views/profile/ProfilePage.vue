<script setup lang="ts">
import AvatarUpload from '@/features/profile/components/AvatarUpload.vue'
import ProfileForm from '@/features/profile/components/ProfileForm.vue'
import PasswordChangeForm from '@/features/profile/components/PasswordChangeForm.vue'
import NotificationPreferencesForm from '@/features/notifications/components/NotificationPreferencesForm.vue'
import AppTabs from '@/components/ui/AppTabs.vue'
import { ref, computed } from 'vue'
import { t } from '@/i18n'

const activeTab = ref('profile')

const tabs = computed(() => [
  { id: 'profile', label: t('profile.detailsTab') },
  { id: 'password', label: t('profile.passwordTab') },
  { id: 'notifications', label: t('nav.notifications') },
])
</script>

<template>
  <div class="max-w-2xl mx-auto space-y-4 sm:space-y-5">
    <div>
      <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ t('nav.profile') }}</h1>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">{{ t('profile.subtitle') }}</p>
    </div>

    <!-- Avatar Section -->
    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-2xs p-4 sm:p-5 transition-colors duration-150">
      <h2 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 mb-4">{{ t('profile.photo') }}</h2>
      <AvatarUpload />
    </div>

    <!-- Tabs: Profile / Password / Notifications -->
    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-2xs p-4 sm:p-5 space-y-4 transition-colors duration-150">
      <AppTabs v-model="activeTab" :tabs="tabs" />

      <div v-if="activeTab === 'profile'">
        <ProfileForm />
      </div>
      <div v-else-if="activeTab === 'password'">
        <PasswordChangeForm />
      </div>
      <div v-else-if="activeTab === 'notifications'">
        <NotificationPreferencesForm />
      </div>
    </div>
  </div>
</template>
