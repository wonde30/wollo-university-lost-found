<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue'
import ForgotPasswordForm from '@/features/auth/components/ForgotPasswordForm.vue'
</script>

<template>
  <AuthLayout
    title="Forgot your password?"
    subtitle="Enter your university email and we'll send you a reset code"
  >
    <ForgotPasswordForm />

    <template #footer>
      <p class="text-center text-xs text-slate-500">
        Remember your password?
        <RouterLink to="/auth/login" class="font-semibold text-[#0F5132] hover:underline ml-1">
          Sign in
        </RouterLink>
      </p>
    </template>
  </AuthLayout>
</template>
