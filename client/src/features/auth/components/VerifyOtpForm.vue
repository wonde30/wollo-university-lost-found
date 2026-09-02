<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'
import { getErrorMessage } from '@/utils/error-handler'
import { useUiStore } from '@/stores/ui.store'
import { t } from '@/i18n'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const uiStore = useUiStore()

const form = reactive({
  email: (route.query.email as string) || authStore.user?.email || '',
  code: '',
})

const generalError = ref<string | null>(null)
const loading = ref(false)
const resending = ref(false)
const cooldown = ref(0)
let timer: ReturnType<typeof setInterval> | null = null

function startCooldown() {
  cooldown.value = 60
  if (timer) clearInterval(timer)
  timer = setInterval(() => {
    cooldown.value--
    if (cooldown.value <= 0) {
      if (timer) clearInterval(timer)
    }
  }, 1000)
}

async function handleSubmit(): Promise<void> {
  if (!form.email || !form.code) {
    generalError.value = t('validation.required')
    return
  }

  generalError.value = null
  loading.value = true
  try {
    await authStore.verifyEmail(form.email, form.code)
    uiStore.success(t('auth.verifiedSuccess'))

    if (authStore.isAdmin) router.push('/admin/dashboard')
    else if (authStore.isStaff) router.push('/staff/dashboard')
    else router.push('/student/dashboard')
  } catch (err) {
    generalError.value = getErrorMessage(err, t('common.errorOccurred'))
  } finally {
    loading.value = false
  }
}

async function handleResend(): Promise<void> {
  if (!form.email) {
    generalError.value = t('validation.email')
    return
  }
  if (cooldown.value > 0) return

  resending.value = true
  try {
    await authStore.resendVerification(form.email)
    uiStore.success(t('auth.resentSuccess'))
    startCooldown()
  } catch (err) {
    generalError.value = getErrorMessage(err, t('common.errorOccurred'))
  } finally {
    resending.value = false
  }
}
</script>

<template>
  <form class="space-y-4" @submit.prevent="handleSubmit">
    <div v-if="generalError" class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-400">
      {{ generalError }}
    </div>

    <p class="text-xs text-slate-500 dark:text-slate-400">
      {{ t('auth.verifyOtp.subtitle') }}
    </p>

    <AppInput
      id="verify-email"
      :label="t('auth.register.email')"
      type="email"
      placeholder="student@wu.edu.et"
      :model-value="form.email"
      required
      @update:model-value="form.email = $event"
    />

    <AppInput
      id="verify-code"
      :label="t('auth.verifyOtp.code')"
      placeholder="e.g. 123456"
      :model-value="form.code"
      required
      @update:model-value="form.code = $event"
    />

    <AppButton
      type="submit"
      variant="primary"
      size="md"
      block
      :loading="loading"
    >
      {{ t('auth.verifyOtp.submit') }}
    </AppButton>

    <div class="flex items-center justify-between pt-2 text-xs">
      <button
        type="button"
        class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 underline cursor-pointer"
        :disabled="resending || cooldown > 0"
        @click="handleResend"
      >
        {{ resending ? t('common.processing') : cooldown > 0 ? `${t('auth.verifyOtp.resend')} (${cooldown}s)` : t('auth.verifyOtp.resend') }}
      </button>

      <RouterLink to="/auth/login" class="font-semibold text-[#0B5D3B] dark:text-[#75bd97] hover:underline">
        {{ t('auth.verifyOtp.backToLogin') }}
      </RouterLink>
    </div>
  </form>
</template>
