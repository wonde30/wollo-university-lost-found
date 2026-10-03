<script setup lang="ts">
import { reactive, ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'
import { validateRegisterForm } from '../validation/auth.validation'
import type { RegisterData, PublicUniversityDomain } from '../types/auth.types'
import { getPublicUniversityDomains } from '../api/auth.api'
import { getErrorMessage, getValidationErrors } from '@/utils/error-handler'
import { useUiStore } from '@/stores/ui.store'
import { t } from '@/i18n'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { useReferencesStore } from '@/features/lookups/stores/references.store'

const router = useRouter()
const authStore = useAuthStore()
const uiStore = useUiStore()
const referencesStore = useReferencesStore()

// Multi-step State (1: Identity, 2: OTP, 3: Success/Credentials)
const currentStep = ref<1 | 2 | 3>(1)

const form = reactive<RegisterData>({
  full_name: '',
  university_id: '',
  email: '',
  phone: '',
  organizational_unit_id: null,
})

const otpCode = ref('')
const publicDomains = ref<PublicUniversityDomain[]>([])
const loadingDomains = ref(false)

const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)
const loading = ref(false)
const resending = ref(false)
const cooldown = ref(0)
let cooldownTimer: ReturnType<typeof setInterval> | null = null

onMounted(async () => {
  if (!referencesStore.organizationalUnitsLoaded) {
    referencesStore.fetchOrganizationalUnits()
  }

  loadingDomains.value = true
  try {
    const domains = await getPublicUniversityDomains()
    publicDomains.value = domains
  } catch {
    // Graceful fallback
  } finally {
    loadingDomains.value = false
  }
})

onUnmounted(() => {
  if (cooldownTimer) clearInterval(cooldownTimer)
})

function startCooldown(): void {
  cooldown.value = 60
  if (cooldownTimer) clearInterval(cooldownTimer)
  cooldownTimer = setInterval(() => {
    cooldown.value--
    if (cooldown.value <= 0) {
      if (cooldownTimer) clearInterval(cooldownTimer)
    }
  }, 1000)
}

const emailDomain = computed(() => {
  const parts = form.email.split('@')
  return parts.length > 1 ? parts[1].toLowerCase().trim() : ''
})

const isKnownDomain = computed(() => {
  if (!emailDomain.value || publicDomains.value.length === 0) return true
  return publicDomains.value.some(d => d.domain.toLowerCase() === emailDomain.value)
})

async function handleInitiateRegister(): Promise<void> {
  generalError.value = null
  errors.value = validateRegisterForm(form)
  if (Object.keys(errors.value).length > 0) return

  loading.value = true
  try {
    await authStore.register(form)
    uiStore.success(t('auth.registerSuccessOtp'))
    currentStep.value = 2
    startCooldown()
  } catch (err) {
    const fieldErrors = getValidationErrors(err)
    if (fieldErrors) {
      errors.value = Object.entries(fieldErrors).reduce((acc, [k, v]) => {
        acc[k] = v[0] || ''
        return acc
      }, {} as Record<string, string>)
    } else {
      generalError.value = getErrorMessage(err, t('common.errorOccurred'))
    }
  } finally {
    loading.value = false
  }
}

async function handleVerifyOtp(): Promise<void> {
  if (!otpCode.value || otpCode.value.length < 6) {
    generalError.value = t('validation.required')
    return
  }

  generalError.value = null
  loading.value = true
  try {
    await authStore.verifyEmail(form.email, otpCode.value.trim())
    uiStore.success(t('auth.verifiedSuccess'))
    currentStep.value = 3
  } catch (err) {
    generalError.value = getErrorMessage(err, t('common.errorOccurred'))
  } finally {
    loading.value = false
  }
}

async function handleResendOtp(): Promise<void> {
  if (cooldown.value > 0 || resending.value) return

  generalError.value = null
  resending.value = true
  try {
    await authStore.resendVerification(form.email, 'email_verification')
    uiStore.success(t('auth.resentSuccess'))
    startCooldown()
  } catch (err) {
    generalError.value = getErrorMessage(err, t('common.errorOccurred'))
  } finally {
    resending.value = false
  }
}

function handleGoToLogin(): void {
  router.push({ path: '/auth/login', query: { email: form.email } })
}
</script>

<template>
  <div class="space-y-5">
    <!-- Stepper Indicator -->
    <div class="flex items-center justify-between px-2 pt-1 pb-3 border-b border-slate-200 dark:border-slate-800">
      <div class="flex items-center space-x-2">
        <div
          class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors"
          :class="currentStep === 1
            ? 'bg-[#0B5D3B] text-white shadow-sm'
            : currentStep > 1
              ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400'
              : 'bg-slate-100 dark:bg-slate-800 text-slate-400'"
        >
          <span v-if="currentStep > 1">✓</span>
          <span v-else>1</span>
        </div>
        <span class="text-xs font-medium" :class="currentStep === 1 ? 'text-[#0B5D3B] dark:text-[#75bd97] font-semibold' : 'text-slate-500'">
          {{ t('auth.step1Title') }}
        </span>
      </div>

      <div class="w-8 h-0.5 bg-slate-200 dark:bg-slate-700"></div>

      <div class="flex items-center space-x-2">
        <div
          class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors"
          :class="currentStep === 2
            ? 'bg-[#0B5D3B] text-white shadow-sm'
            : currentStep > 2
              ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400'
              : 'bg-slate-100 dark:bg-slate-800 text-slate-400'"
        >
          <span v-if="currentStep > 2">✓</span>
          <span v-else>2</span>
        </div>
        <span class="text-xs font-medium" :class="currentStep === 2 ? 'text-[#0B5D3B] dark:text-[#75bd97] font-semibold' : 'text-slate-500'">
          {{ t('auth.step2Title') }}
        </span>
      </div>

      <div class="w-8 h-0.5 bg-slate-200 dark:bg-slate-700"></div>

      <div class="flex items-center space-x-2">
        <div
          class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors"
          :class="currentStep === 3
            ? 'bg-emerald-600 text-white shadow-sm'
            : 'bg-slate-100 dark:bg-slate-800 text-slate-400'"
        >
          3
        </div>
        <span class="text-xs font-medium" :class="currentStep === 3 ? 'text-emerald-600 font-semibold' : 'text-slate-500'">
          {{ t('auth.step3Title') }}
        </span>
      </div>
    </div>

    <!-- General Error Banner -->
    <div v-if="generalError" class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-400">
      {{ generalError }}
    </div>

    <!-- ========================================== -->
    <!-- STEP 1: Institutional Details Registration -->
    <!-- ========================================== -->
    <form v-if="currentStep === 1" class="space-y-3.5" @submit.prevent="handleInitiateRegister">
      <AppInput
        id="reg-name"
        :label="t('auth.register.fullName')"
        :placeholder="t('auth.placeholders.fullName')"
        :model-value="form.full_name"
        :error="errors.full_name"
        required
        @update:model-value="form.full_name = $event"
      />

      <AppInput
        id="reg-id"
        :label="t('auth.register.idNumber')"
        :placeholder="t('auth.idPlaceholder')"
        :model-value="form.university_id"
        :error="errors.university_id"
        required
        @update:model-value="form.university_id = $event"
      />

      <div>
        <AppInput
          id="reg-email"
          :label="t('auth.register.email')"
          type="email"
          :placeholder="t('auth.emailPlaceholder')"
          :model-value="form.email"
          :error="errors.email"
          required
          @update:model-value="form.email = $event"
        />

        <!-- Active University Domain Hint Pills -->
        <div v-if="publicDomains.length > 0" class="mt-1.5 flex flex-wrap items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400">
          <span class="font-medium text-slate-600 dark:text-slate-300">{{ t('auth.allowedDomains') }}:</span>
          <span
            v-for="d in publicDomains"
            :key="d.domain"
            class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 font-mono text-[10px]"
          >
            @{{ d.domain }}
          </span>
        </div>

        <div v-if="emailDomain && !isKnownDomain" class="mt-1 text-[11px] text-amber-600 dark:text-amber-400">
          {{ t('auth.domainRequirementNote') }}
        </div>
      </div>

      <AppInput
        id="reg-phone"
        :label="t('auth.register.phone')"
        type="tel"
        :placeholder="t('auth.phonePlaceholder')"
        :model-value="form.phone || ''"
        :error="errors.phone"
        @update:model-value="form.phone = $event"
      />

      <div v-if="referencesStore.organizationalUnits.length > 0">
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
          {{ t('auth.academicUnitOptional') }}
        </label>
        <select
          :value="form.organizational_unit_id || ''"
          class="w-full h-10 px-3 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0B5D3B]"
          @change="form.organizational_unit_id = ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null"
        >
          <option value="">{{ t('auth.selectAcademicUnit') }}</option>
          <option v-for="u in referencesStore.organizationalUnits" :key="u.id" :value="u.id">
            {{ u.name }} ({{ u.short_code }})
          </option>
        </select>
      </div>

      <AppButton
        type="submit"
        variant="primary"
        size="md"
        block
        :loading="loading"
      >
        {{ t('auth.createAccountBtn') }}
      </AppButton>
    </form>

    <!-- ========================================== -->
    <!-- STEP 2: Secure OTP Verification            -->
    <!-- ========================================== -->
    <form v-else-if="currentStep === 2" class="space-y-4" @submit.prevent="handleVerifyOtp">
      <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-300 space-y-1">
        <div class="font-semibold">{{ t('auth.verifyOtp.title') }}</div>
        <div>{{ t('auth.verifyOtp.subtitle') }} <strong class="font-mono text-emerald-900 dark:text-emerald-200">{{ form.email }}</strong></div>
      </div>

      <AppInput
        id="otp-input"
        :label="t('auth.otpCode')"
        :placeholder="t('auth.otpPlaceholder')"
        :model-value="otpCode"
        required
        :maxlength="6"
        class="text-center font-mono tracking-widest text-lg"
        @update:model-value="otpCode = $event"
      />

      <AppButton
        type="submit"
        variant="primary"
        size="md"
        block
        :loading="loading"
      >
        {{ t('auth.verifyOtpBtn') }}
      </AppButton>

      <div class="flex items-center justify-between pt-2 text-xs">
        <button
          type="button"
          class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 underline cursor-pointer disabled:opacity-50"
          :disabled="resending || cooldown > 0"
          @click="handleResendOtp"
        >
          {{ resending ? t('common.processing') : cooldown > 0 ? `${t('auth.verifyOtp.resend')} (${cooldown}s)` : t('auth.verifyOtp.resend') }}
        </button>

        <button
          type="button"
          class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 underline cursor-pointer"
          @click="currentStep = 1"
        >
          {{ t('auth.changeEmail') }}
        </button>
      </div>
    </form>

    <!-- ========================================== -->
    <!-- STEP 3: Activation & Credential Delivered  -->
    <!-- ========================================== -->
    <div v-else-if="currentStep === 3" class="space-y-5 text-center py-2">
      <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-950/60 border-2 border-emerald-500 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto text-2xl font-bold shadow-sm">
        ✓
      </div>

      <div class="space-y-2">
        <h3 class="text-base font-bold text-slate-900 dark:text-white">
          {{ t('auth.credentialsSent') }}
        </h3>
        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed max-w-sm mx-auto">
          {{ t('auth.credentialsSentSubtitle') }}
        </p>
      </div>

      <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-left text-xs text-amber-800 dark:text-amber-300">
        <div class="font-bold flex items-center gap-1.5 mb-1">
          <span>⚠️</span>
          <span>{{ t('auth.securityNotice') }}</span>
        </div>
        <p class="text-[11px] leading-relaxed">
          {{ t('auth.securityNoticeDesc') }}
        </p>
      </div>

      <AppButton
        type="button"
        variant="primary"
        size="md"
        block
        @click="handleGoToLogin"
      >
        {{ t('auth.goToLogin') }}
      </AppButton>
    </div>
  </div>
</template>
