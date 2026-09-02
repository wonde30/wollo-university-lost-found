<script setup lang="ts">
import { computed } from 'vue'
import AppButton from '@/components/ui/AppButton.vue'
import { t } from '@/i18n'

interface Props {
  submitText?: string
  cancelText?: string
  loading?: boolean
  disabled?: boolean
  showCancel?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  submitText: undefined,
  cancelText: undefined,
  loading: false,
  disabled: false,
  showCancel: true,
})

const resolvedSubmitText = computed(() => props.submitText || t('common.save'))
const resolvedCancelText = computed(() => props.cancelText || t('common.cancel'))

defineEmits<{
  (e: 'submit'): void
  (e: 'cancel'): void
}>()
</script>

<template>
  <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
    <AppButton
      v-if="showCancel"
      type="button"
      variant="outline"
      :disabled="loading"
      @click="$emit('cancel')"
    >
      {{ resolvedCancelText }}
    </AppButton>

    <AppButton
      type="submit"
      variant="primary"
      :loading="loading"
      :disabled="disabled || loading"
      @click="$emit('submit')"
    >
      {{ resolvedSubmitText }}
    </AppButton>
  </div>
</template>
