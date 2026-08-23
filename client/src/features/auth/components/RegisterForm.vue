<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'
import { validateRegisterForm } from '../validation/auth.validation'
import type { RegisterData } from '../types/auth.types'
import { getErrorMessage, getValidationErrors } from '@/utils/error-handler'
import { useUiStore } from '@/stores/ui.store'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

const router = useRouter()
const authStore = useAuthStore()
const uiStore = useUiStore()

const form = reactive<RegisterData & { password_confirmation: string }>({
  full_name: '',
  university_id: '',
  email: '',
  password: '',
  password_confirmation: '',
  phone: '',
})

const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)
const loading = ref(false)

async function handleSubmit(): Promise<void> {
  generalError.value = null
  errors.value = validateRegisterForm(form)
  if (Object.keys(errors.value).length > 0) return

  loading.value = true
  try {
    await authStore.register(form)
    uiStore.success('Account registered successfully! Please verify your email.')
    router.push({ path: '/auth/verify-otp', query: { email: form.email } })
  } catch (err) {
    const fieldErrors = getValidationErrors(err)
    if (fieldErrors) {
      errors.value = Object.entries(fieldErrors).reduce((acc, [k, v]) => {
        acc[k] = v[0] || ''
        return acc
      }, {} as Record<string, string>)
    } else {
      generalError.value = getErrorMessage(err, 'Failed to create account. Please try again.')
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <form class="space-y-3.5" @submit.prevent="handleSubmit">
    <div v-if="generalError" class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700">
      {{ generalError }}
    </div>

    <AppInput
      id="reg-name"
      label="Full Name"
      placeholder="e.g. Abebe Kebede"
      :model-value="form.full_name"
      :error="errors.full_name"
      required
      @update:model-value="form.full_name = $event"
    />

    <AppInput
      id="reg-id"
      label="University ID"
      placeholder="e.g. WU123456"
      :model-value="form.university_id"
      :error="errors.university_id"
      required
      @update:model-value="form.university_id = $event"
    />

    <AppInput
      id="reg-email"
      label="University Email"
      type="email"
      placeholder="e.g. abebe@wu.edu.et"
      :model-value="form.email"
      :error="errors.email"
      required
      @update:model-value="form.email = $event"
    />

    <AppInput
      id="reg-phone"
      label="Phone Number (Optional)"
      type="tel"
      placeholder="e.g. +251 911 234 567"
      :model-value="form.phone || ''"
      :error="errors.phone"
      @update:model-value="form.phone = $event"
    />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <AppInput
        id="reg-password"
        label="Password"
        type="password"
        placeholder="Min. 8 characters"
        :model-value="form.password"
        :error="errors.password"
        required
        @update:model-value="form.password = $event"
      />

      <AppInput
        id="reg-password-confirm"
        label="Confirm Password"
        type="password"
        placeholder="Repeat password"
        :model-value="form.password_confirmation"
        :error="errors.password_confirmation"
        required
        @update:model-value="form.password_confirmation = $event"
      />
    </div>

    <AppButton
      type="submit"
      variant="primary"
      size="md"
      block
      :loading="loading"
    >
      Create Student Account
    </AppButton>

    <div class="text-center pt-2">
      <span class="text-xs text-slate-500">Already registered? </span>
      <RouterLink to="/auth/login" class="text-xs font-semibold text-[#0F5132] hover:underline">
        Sign in here
      </RouterLink>
    </div>
  </form>
</template>
