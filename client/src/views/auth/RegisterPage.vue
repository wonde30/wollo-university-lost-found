<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue'
import RegisterForm from '@/features/auth/components/RegisterForm.vue'
import { t } from '@/i18n'
</script>

<template>
  <AuthLayout
    :title="t('auth.register.title')"
    :subtitle="t('auth.register.subtitle')"
  >
    <RegisterForm />

    <template #footer>
      <p class="text-center text-xs text-slate-500 dark:text-slate-400">
        {{ t('auth.register.alreadyAccount') }}
        <RouterLink to="/auth/login" class="font-semibold text-[#0B5D3B] dark:text-[#75bd97] hover:underline ml-1">
          {{ t('auth.register.signIn') }}
        </RouterLink>
      </p>
    </template>
  </AuthLayout>
</template>
