<script setup lang="ts">
import AppButton from '@/components/ui/AppButton.vue'

interface Props {
  submitText?: string
  cancelText?: string
  loading?: boolean
  disabled?: boolean
  showCancel?: boolean
}

withDefaults(defineProps<Props>(), {
  submitText: 'Save Changes',
  cancelText: 'Cancel',
  loading: false,
  disabled: false,
  showCancel: true,
})

defineEmits<{
  (e: 'submit'): void
  (e: 'cancel'): void
}>()
</script>

<template>
  <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
    <AppButton
      v-if="showCancel"
      type="button"
      variant="outline"
      :disabled="loading"
      @click="$emit('cancel')"
    >
      {{ cancelText }}
    </AppButton>

    <AppButton
      type="submit"
      variant="primary"
      :loading="loading"
      :disabled="disabled || loading"
      @click="$emit('submit')"
    >
      {{ submitText }}
    </AppButton>
  </div>
</template>
