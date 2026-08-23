<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'
import { validateLoginForm } from '../validation/auth.validation'
import type { LoginCredentials } from '../types/auth.types'
import { getErrorMessage, getValidationErrors } from '@/utils/error-handler'
import { useUiStore } from '@/stores/ui.store'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

const router = useRouter()
const authStore = useAuthStore()
const uiStore = useUiStore()

const form = reactive<LoginCredentials>({
  email: '',
  password: '',
})

const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)
const loading = ref(false)

async function handleSubmit(): Promise<void> {
  generalError.value = null
  errors.value = validateLoginForm(form)
  if (Object.keys(errors.value).length > 0) return

  loading.value = true
  try {
    await authStore.login(form)
    uiStore.success(`Welcome back, ${authStore.user?.full_name}!`)

    if (authStore.isAdmin) {
      router.push('/admin/dashboard')
    } else if (authStore.isStaff) {
      router.push('/staff/dashboard')
    } else {
      router.push('/student/dashboard')
    }
  } catch (err) {
    const fieldErrors = getValidationErrors(err)
    if (fieldErrors) {
      errors.value = Object.entries(fieldErrors).reduce((acc, [k, v]) => {
        acc[k] = v[0] || ''
        return acc
      }, {} as Record<string, string>)
    } else {
      generalError.value = getErrorMessage(err, 'Failed to sign in. Please check your credentials.')
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <form class="space-y-4" @submit.prevent="handleSubmit">
    <div v-if="generalError" class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700">
      {{ generalError }}
    </div>

    <AppInput
      id="login-email"
      label="University Email"
      type="email"
      placeholder="student@wu.edu.et"
      :model-value="form.email"
      :error="errors.email"
      required
      autocomplete="username"
      @update:model-value="form.email = $event"
    />

    <div>
      <div class="flex items-center justify-between mb-1">
        <label for="login-password" class="block text-sm font-medium text-slate-700">
          Password <span class="text-rose-500">*</span>
        </label>
        <RouterLink to="/auth/forgot-password" class="text-xs text-[#0F5132] hover:underline font-medium">
          Forgot password?
        </RouterLink>
      </div>
      <AppInput
        id="login-password"
        type="password"
        placeholder="Enter your password"
        :model-value="form.password"
        :error="errors.password"
        required
        autocomplete="current-password"
        @update:model-value="form.password = $event"
      />
    </div>

    <AppButton
      type="submit"
      variant="primary"
      size="md"
      block
      :loading="loading"
    >
      Sign In to Platform
    </AppButton>

    <div class="text-center pt-2">
      <span class="text-xs text-slate-500">Don't have an account? </span>
      <RouterLink to="/auth/register" class="text-xs font-semibold text-[#0F5132] hover:underline">
        Register here
      </RouterLink>
    </div>
  </form>
</template>
