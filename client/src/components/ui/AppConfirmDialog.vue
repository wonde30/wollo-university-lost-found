<script setup lang="ts">
import { computed } from 'vue'
import { useUiStore } from '@/stores/ui.store'
import AppModal from './AppModal.vue'
import AppButton from './AppButton.vue'

const uiStore = useUiStore()

const variantConfirmType = computed(() => {
  switch (uiStore.confirmDialog?.variant) {
    case 'danger': return 'danger'
    case 'warning': return 'gold'
    case 'primary':
    default: return 'primary'
  }
})
</script>

<template>
  <AppModal
    :open="uiStore.isConfirmDialogOpen"
    size="sm"
    :title="uiStore.confirmDialog?.title || 'Confirm Action'"
    :closable="!uiStore.confirmDialogLoading"
    @close="uiStore.handleCancel"
  >
    <div class="space-y-3">
      <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
        {{ uiStore.confirmDialog?.message }}
      </p>
    </div>

    <template #footer>
      <div class="flex items-center justify-end gap-2.5">
        <AppButton
          variant="outline"
          size="sm"
          :disabled="uiStore.confirmDialogLoading"
          @click="uiStore.handleCancel"
        >
          {{ uiStore.confirmDialog?.cancelText || 'Cancel' }}
        </AppButton>
        <AppButton
          :variant="variantConfirmType"
          size="sm"
          :loading="uiStore.confirmDialogLoading"
          @click="uiStore.handleConfirm"
        >
          {{ uiStore.confirmDialog?.confirmText || 'Confirm' }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>
