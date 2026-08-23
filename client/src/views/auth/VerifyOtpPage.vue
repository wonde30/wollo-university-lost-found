<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue'
import VerifyOtpForm from '@/features/auth/components/VerifyOtpForm.vue'
</script>

<template>
  <AuthLayout
    title="Verify your email"
    subtitle="A 6-digit code was sent to your university email address"
  >
    <VerifyOtpForm />
  </AuthLayout>
</template>
