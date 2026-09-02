<script setup lang="ts">
import { reactive, ref, watch } from 'vue'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useProfile } from '../composables/useProfile'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage, getValidationErrors } from '@/utils/error-handler'
import { t } from '@/i18n'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

const authStore = useAuthStore()
const { updateProfile, loading } = useProfile()
const uiStore = useUiStore()

const form = reactive({
  full_name: authStore.user?.full_name || '',
  phone: authStore.user?.phone || '',
})

watch(
  () => authStore.user,
  (newUser) => {
    if (newUser) {
      form.full_name = newUser.full_name || ''
      form.phone = newUser.phone || ''
    }
  },
  { immediate: true }
)

const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)

function clearError(field: string) {
  if (errors.value[field]) {
    const { [field]: _, ...rest } = errors.value
    errors.value = rest
  }
}

async function handleSubmit(): Promise<void> {
  generalError.value = null
  errors.value = {}

  if (!form.full_name.trim()) {
    errors.value.full_name = t('validation.required')
  } else if (form.full_name.trim().length < 2) {
    errors.value.full_name = t('validation.minLength', { min: 2 })
  } else if (form.full_name.trim().length > 150) {
    errors.value.full_name = t('validation.maxLength', { max: 150 })
  }

  if (form.phone && !/^\+?[\d\s\-()]{7,20}$/.test(form.phone)) {
    errors.value.phone = t('validation.required')
  }

  if (Object.keys(errors.value).length > 0) return

  try {
    await updateProfile({
      full_name: form.full_name.trim(),
      phone: form.phone ? form.phone.trim() : undefined,
    })
    uiStore.success(t('profile.profileUpdated'))
  } catch (err) {
    const fieldErrors = getValidationErrors(err)
    if (fieldErrors) {
      errors.value = Object.entries(fieldErrors).reduce((acc, [k, v]) => {
        acc[k] = Array.isArray(v) ? v[0] : v
        return acc
      }, {} as Record<string, string>)
    } else {
      generalError.value = getErrorMessage(err, t('common.errorOccurred'))
    }
  }
}
</script>

<template>
  <form class="space-y-5" @submit.prevent="handleSubmit">
    <div v-if="generalError" class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-400">
      {{ generalError }}
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <AppInput
        id="profile-full-name"
        :label="t('auth.register.fullName')"
        :placeholder="t('auth.placeholders.legalName')"
        :model-value="form.full_name"
        :error="errors.full_name"
        required
        @update:model-value="form.full_name = $event; clearError('full_name')"
      />

      <AppInput
        id="profile-email"
        :label="t('profile.emailReadOnly')"
        type="email"
        :model-value="authStore.user?.email || ''"
        disabled
        :helper-text="t('profile.emailCannotChange')"
      />
    </div>

    <AppInput
      id="profile-phone"
      :label="t('auth.register.phone')"
      placeholder="+251 9XX XXX XXXX"
      :model-value="form.phone"
      :error="errors.phone"
      @update:model-value="form.phone = $event; clearError('phone')"
    />

    <div class="flex justify-end pt-3 border-t border-slate-100 dark:border-slate-800">
      <AppButton variant="primary" type="submit" :loading="loading">
        {{ t('common.save') }}
      </AppButton>
    </div>
  </form>
</template>
