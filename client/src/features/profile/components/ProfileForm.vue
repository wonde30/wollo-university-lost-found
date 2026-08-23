<script setup lang="ts">
import { reactive, ref, watch } from 'vue'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useProfile } from '../composables/useProfile'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage, getValidationErrors } from '@/utils/error-handler'
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
    errors.value.full_name = 'Full name is required.'
  } else if (form.full_name.trim().length < 2) {
    errors.value.full_name = 'Full name must be at least 2 characters.'
  } else if (form.full_name.trim().length > 150) {
    errors.value.full_name = 'Full name must not exceed 150 characters.'
  }

  if (form.phone && !/^\+?[\d\s\-()]{7,20}$/.test(form.phone)) {
    errors.value.phone = 'Please enter a valid phone number.'
  }

  if (Object.keys(errors.value).length > 0) return

  try {
    await updateProfile({
      full_name: form.full_name.trim(),
      phone: form.phone ? form.phone.trim() : undefined,
    })
    uiStore.success('Profile updated successfully.')
  } catch (err) {
    const fieldErrors = getValidationErrors(err)
    if (fieldErrors) {
      errors.value = Object.entries(fieldErrors).reduce((acc, [k, v]) => {
        acc[k] = Array.isArray(v) ? v[0] : v
        return acc
      }, {} as Record<string, string>)
    } else {
      generalError.value = getErrorMessage(err, 'Failed to update profile.')
    }
  }
}
</script>

<template>
  <form class="space-y-5" @submit.prevent="handleSubmit">
    <div v-if="generalError" class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700">
      {{ generalError }}
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <AppInput
        id="profile-full-name"
        label="Full Name"
        placeholder="Your full legal name"
        :model-value="form.full_name"
        :error="errors.full_name"
        required
        @update:model-value="form.full_name = $event; clearError('full_name')"
      />

      <AppInput
        id="profile-email"
        label="University Email"
        type="email"
        :model-value="authStore.user?.email || ''"
        disabled
        helper-text="Email cannot be changed"
      />
    </div>

    <AppInput
      id="profile-phone"
      label="Phone Number"
      placeholder="+251 9XX XXX XXXX"
      :model-value="form.phone"
      :error="errors.phone"
      @update:model-value="form.phone = $event; clearError('phone')"
    />

    <div class="flex justify-end pt-3 border-t border-slate-100">
      <AppButton variant="primary" type="submit" :loading="loading">
        Save Profile Changes
      </AppButton>
    </div>
  </form>
</template>
