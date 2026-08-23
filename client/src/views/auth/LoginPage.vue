<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue'
import LoginForm from '@/features/auth/components/LoginForm.vue'
</script>

<template>
  <AuthLayout
    title="Welcome back"
    subtitle="Sign in to your Wollo University Lost & Found account"
  >
    <LoginForm />

    <template #footer>
      <p class="text-center text-xs text-slate-500">
        Don't have an account?
        <RouterLink to="/auth/register" class="font-semibold text-[#0F5132] hover:underline ml-1">
          Register here
        </RouterLink>
      </p>
      <p class="text-center text-xs text-slate-500 mt-1.5">
        <RouterLink to="/auth/forgot-password" class="text-slate-500 hover:text-[#0F5132] hover:underline">
          Forgot your password?
        </RouterLink>
      </p>
    </template>
  </AuthLayout>
</template>
