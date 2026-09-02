<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue'
import ForgotPasswordForm from '@/features/auth/components/ForgotPasswordForm.vue'
import { t } from '@/i18n'
</script>

<template>
  <AuthLayout
    :title="t('auth.forgotPassword.title')"
    :subtitle="t('auth.forgotPassword.subtitle')"
  >
    <ForgotPasswordForm />

    <template #footer>
      <p class="text-center text-xs text-slate-500 dark:text-slate-400">
        {{ t('auth.forgotPassword.rememberPassword') }}
        <RouterLink to="/auth/login" class="font-semibold text-[#0B5D3B] dark:text-[#75bd97] hover:underline ml-1">
          {{ t('auth.forgotPassword.signIn') }}
        </RouterLink>
      </p>
    </template>
  </AuthLayout>
</template>
