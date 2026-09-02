<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'
import { validateLoginForm } from '../validation/auth.validation'
import type { LoginCredentials } from '../types/auth.types'
import { getErrorMessage, getValidationErrors } from '@/utils/error-handler'
import { useUiStore } from '@/stores/ui.store'
import { t } from '@/i18n'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppCheckbox from '@/components/ui/AppCheckbox.vue'

const router = useRouter()
const authStore = useAuthStore()
const uiStore = useUiStore()

const form = reactive<LoginCredentials>({
  email: '',
  password: '',
  remember: false,
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
    uiStore.success(t('common.welcomeBack', { name: authStore.user?.full_name || '' }))

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
      generalError.value = getErrorMessage(err, t('auth.login.invalidCredentials'))
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <form class="space-y-4" @submit.prevent="handleSubmit">
    <div v-if="generalError" class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-400">
      {{ generalError }}
    </div>

    <AppInput
      id="login-email"
      :label="t('auth.login.email')"
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
        <label for="login-password" class="block text-sm font-medium text-slate-700 dark:text-slate-200">
          {{ t('auth.login.password') }} <span class="text-rose-500">*</span>
        </label>
        <RouterLink to="/auth/forgot-password" class="text-xs text-[#0B5D3B] dark:text-[#75bd97] hover:underline font-medium">
          {{ t('auth.login.forgotPassword') }}
        </RouterLink>
      </div>
      <AppInput
        id="login-password"
        type="password"
        :placeholder="t('auth.placeholders.password')"
        :model-value="form.password"
        :error="errors.password"
        required
        autocomplete="current-password"
        @update:model-value="form.password = $event"
      />
    </div>

    <div class="flex items-center justify-between">
      <AppCheckbox
        id="remember-me"
        :label="t('auth.login.rememberMe')"
        :model-value="form.remember"
        @update:model-value="form.remember = $event"
      />
    </div>

    <AppButton
      type="submit"
      variant="primary"
      size="md"
      block
      :loading="loading"
    >
      {{ t('auth.login.signIn') }}
    </AppButton>
  </form>
</template>
