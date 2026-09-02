<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'
import { validateRegisterForm } from '../validation/auth.validation'
import type { RegisterData } from '../types/auth.types'
import { getErrorMessage, getValidationErrors } from '@/utils/error-handler'
import { useUiStore } from '@/stores/ui.store'
import { t } from '@/i18n'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

import { useReferencesStore } from '@/features/lookups/stores/references.store'
import { onMounted } from 'vue'

const router = useRouter()
const authStore = useAuthStore()
const uiStore = useUiStore()
const referencesStore = useReferencesStore()

const form = reactive<RegisterData & { password_confirmation: string }>({
  full_name: '',
  university_id: '',
  email: '',
  password: '',
  password_confirmation: '',
  phone: '',
  organizational_unit_id: null,
})

onMounted(() => {
  if (!referencesStore.organizationalUnitsLoaded) {
    referencesStore.fetchOrganizationalUnits()
  }
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
    uiStore.success(t('auth.registerSuccessOtp'))
    router.push({ path: '/auth/verify-otp', query: { email: form.email } })
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
</script>

<template>
  <form class="space-y-3.5" @submit.prevent="handleSubmit">
    <div v-if="generalError" class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-400">
      {{ generalError }}
    </div>

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
      placeholder="e.g. WU123456"
      :model-value="form.university_id"
      :error="errors.university_id"
      required
      @update:model-value="form.university_id = $event"
    />

    <AppInput
      id="reg-email"
      :label="t('auth.register.email')"
      type="email"
      placeholder="e.g. abebe@wu.edu.et"
      :model-value="form.email"
      :error="errors.email"
      required
      @update:model-value="form.email = $event"
    />

    <AppInput
      id="reg-phone"
      :label="t('auth.register.phone')"
      type="tel"
      placeholder="e.g. +251 911 234 567"
      :model-value="form.phone || ''"
      :error="errors.phone"
      @update:model-value="form.phone = $event"
    />

    <div v-if="referencesStore.organizationalUnits.length > 0">
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
        Academic Unit / College (Optional)
      </label>
      <select
        :value="form.organizational_unit_id || ''"
        class="w-full h-10 px-3 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0B5D3B]"
        @change="form.organizational_unit_id = ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null"
      >
        <option value="">Select College / Unit...</option>
        <option v-for="u in referencesStore.organizationalUnits" :key="u.id" :value="u.id">
          {{ u.name }} ({{ u.short_code }})
        </option>
      </select>
    </div>


    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <AppInput
        id="reg-password"
        :label="t('auth.register.password')"
        type="password"
        :placeholder="t('validation.minLength', { min: 8 })"
        :model-value="form.password"
        :error="errors.password"
        required
        @update:model-value="form.password = $event"
      />

      <AppInput
        id="reg-password-confirm"
        :label="t('auth.register.confirmPassword')"
        type="password"
        :placeholder="t('auth.placeholders.repeatPassword')"
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
      {{ t('auth.register.submit') }}
    </AppButton>
  </form>
</template>
