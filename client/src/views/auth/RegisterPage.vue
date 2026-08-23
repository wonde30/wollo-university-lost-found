<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue'
import RegisterForm from '@/features/auth/components/RegisterForm.vue'
</script>

<template>
  <AuthLayout
    title="Create your account"
    subtitle="Join the Wollo University Digital Lost & Found Platform"
  >
    <RegisterForm />

    <template #footer>
      <p class="text-center text-xs text-slate-500">
        Already have an account?
        <RouterLink to="/auth/login" class="font-semibold text-[#0F5132] hover:underline ml-1">
          Sign in
        </RouterLink>
      </p>
    </template>
  </AuthLayout>
</template>
