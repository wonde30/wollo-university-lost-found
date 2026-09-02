<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'
import { validateForgotPasswordForm } from '../validation/auth.validation'
import { getErrorMessage } from '@/utils/error-handler'
import { useUiStore } from '@/stores/ui.store'
import { t } from '@/i18n'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

const router = useRouter()
const authStore = useAuthStore()
const uiStore = useUiStore()

const form = reactive({ email: '' })
const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)
const loading = ref(false)

async function handleSubmit(): Promise<void> {
  generalError.value = null
  errors.value = validateForgotPasswordForm(form)
  if (Object.keys(errors.value).length > 0) return

  loading.value = true
  try {
    await authStore.forgotPassword(form.email)
    uiStore.success(t('auth.resetOtpSent'))
    router.push({ path: '/auth/reset-password', query: { email: form.email } })
  } catch (err) {
    generalError.value = getErrorMessage(err, t('common.errorOccurred'))
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

    <p class="text-xs text-slate-500 dark:text-slate-400">
      {{ t('auth.forgotPassword.subtitle') }}
    </p>

    <AppInput
      id="forgot-email"
      :label="t('auth.forgotPassword.email')"
      type="email"
      placeholder="student@wu.edu.et"
      :model-value="form.email"
      :error="errors.email"
      required
      @update:model-value="form.email = $event"
    />

    <AppButton
      type="submit"
      variant="primary"
      size="md"
      block
      :loading="loading"
    >
      {{ t('auth.forgotPassword.submit') }}
    </AppButton>

    <div class="text-center pt-2">
      <RouterLink to="/auth/login" class="text-xs font-semibold text-[#0B5D3B] dark:text-[#75bd97] hover:underline">
        &larr; {{ t('auth.forgotPassword.signIn') }}
      </RouterLink>
    </div>
  </form>
</template>
