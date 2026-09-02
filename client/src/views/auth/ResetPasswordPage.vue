<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue'
import ResetPasswordForm from '@/features/auth/components/ResetPasswordForm.vue'
import { t } from '@/i18n'
</script>

<template>
  <AuthLayout
    :title="t('auth.resetPassword.title')"
    :subtitle="t('auth.resetPassword.subtitle')"
  >
    <ResetPasswordForm />

    <template #footer>
      <p class="text-center text-xs text-slate-500 dark:text-slate-400">
        <RouterLink to="/auth/forgot-password" class="text-slate-500 dark:text-slate-400 hover:text-[#0B5D3B] dark:hover:text-[#75bd97] hover:underline">
          ← {{ t('auth.resetPassword.requestNewCode') }}
        </RouterLink>
      </p>
    </template>
  </AuthLayout>
</template>
