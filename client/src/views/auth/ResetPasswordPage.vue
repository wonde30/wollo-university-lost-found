<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue'
import ResetPasswordForm from '@/features/auth/components/ResetPasswordForm.vue'
</script>

<template>
  <AuthLayout
    title="Reset your password"
    subtitle="Enter the OTP code sent to your email along with your new password"
  >
    <ResetPasswordForm />

    <template #footer>
      <p class="text-center text-xs text-slate-500">
        <RouterLink to="/auth/forgot-password" class="text-slate-500 hover:text-[#0F5132] hover:underline">
          ← Request a new code
        </RouterLink>
      </p>
    </template>
  </AuthLayout>
</template>
