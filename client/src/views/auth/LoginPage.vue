<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue'
import LoginForm from '@/features/auth/components/LoginForm.vue'
import { t } from '@/i18n'
</script>

<template>
  <AuthLayout
    :title="t('auth.login.title')"
    :subtitle="t('auth.login.subtitle')"
  >
    <LoginForm />

    <template #footer>
      <p class="text-center text-xs text-slate-500 dark:text-slate-400">
        {{ t('auth.login.noAccount') }}
        <RouterLink to="/auth/register" class="font-semibold text-[#0B5D3B] dark:text-[#75bd97] hover:underline ml-1">
          {{ t('auth.login.registerHere') }}
        </RouterLink>
      </p>
      <p class="text-center text-xs text-slate-500 dark:text-slate-400 mt-2">
        <RouterLink to="/auth/forgot-password" class="text-slate-500 dark:text-slate-400 hover:text-[#0B5D3B] dark:hover:text-[#75bd97] hover:underline">
          {{ t('auth.login.forgotPassword') }}
        </RouterLink>
      </p>
    </template>
  </AuthLayout>
</template>
