<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue'
import VerifyOtpForm from '@/features/auth/components/VerifyOtpForm.vue'
import { t } from '@/i18n'
</script>

<template>
  <AuthLayout
    :title="t('auth.verifyOtp.title')"
    :subtitle="t('auth.verifyOtp.subtitle')"
  >
    <VerifyOtpForm />
  </AuthLayout>
</template>
