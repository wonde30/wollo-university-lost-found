<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

const authStore = useAuthStore()
const uiStore = useUiStore()

const form = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)
const loading = ref(false)

function clearError(field: string) {
  if (errors.value[field]) {
    const { [field]: _, ...rest } = errors.value
    errors.value = rest
  }
}

function validate(): boolean {
  errors.value = {}
  if (!form.current_password) {
    errors.value.current_password = 'Current password is required.'
  }
  if (!form.new_password) {
    errors.value.new_password = 'New password is required.'
  } else if (form.new_password.length < 8) {
    errors.value.new_password = 'Password must be at least 8 characters.'
  } else if (!/(?=.*[a-z])/.test(form.new_password)) {
    errors.value.new_password = 'Password must contain at least one lowercase letter.'
  } else if (!/(?=.*[A-Z])/.test(form.new_password)) {
    errors.value.new_password = 'Password must contain at least one uppercase letter.'
  } else if (!/(?=.*\d)/.test(form.new_password)) {
    errors.value.new_password = 'Password must contain at least one number.'
  } else if (!/(?=.*[@$!%*?&])/.test(form.new_password)) {
    errors.value.new_password = 'Password must contain at least one special character (@$!%*?&).'
  }
  if (!form.new_password_confirmation) {
    errors.value.new_password_confirmation = 'Please confirm your new password.'
  } else if (form.new_password !== form.new_password_confirmation) {
    errors.value.new_password_confirmation = 'Passwords do not match.'
  }
  return Object.keys(errors.value).length === 0
}

async function handleSubmit(): Promise<void> {
  if (!validate()) return
  generalError.value = null
  loading.value = true
  try {
    await authStore.changePassword(form.current_password, form.new_password, form.new_password_confirmation)
    uiStore.success('Password changed successfully.')
    form.current_password = ''
    form.new_password = ''
    form.new_password_confirmation = ''
  } catch (err) {
    generalError.value = getErrorMessage(err, 'Failed to change password.')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <form class="space-y-5" @submit.prevent="handleSubmit">
    <div v-if="generalError" class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700">
      {{ generalError }}
    </div>

    <AppInput
      id="pw-current"
      label="Current Password"
      type="password"
      placeholder="Enter current password"
      :model-value="form.current_password"
      :error="errors.current_password"
      required
      @update:model-value="form.current_password = $event; clearError('current_password')"
    />

    <AppInput
      id="pw-new"
      label="New Password"
      type="password"
      placeholder="Min. 8 chars, uppercase, lowercase, number, special"
      :model-value="form.new_password"
      :error="errors.new_password"
      hint="Must include uppercase, lowercase, number, and special character"
      required
      @update:model-value="form.new_password = $event; clearError('new_password'); clearError('new_password_confirmation')"
    />

    <AppInput
      id="pw-confirm"
      label="Confirm New Password"
      type="password"
      placeholder="Repeat new password"
      :model-value="form.new_password_confirmation"
      :error="errors.new_password_confirmation"
      required
      @update:model-value="form.new_password_confirmation = $event; clearError('new_password_confirmation')"
    />

    <div class="flex justify-end pt-3 border-t border-slate-100">
      <AppButton variant="primary" type="submit" :loading="loading">
        Change Password
      </AppButton>
    </div>
  </form>
</template>
