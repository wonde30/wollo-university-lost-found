<script setup lang="ts">
import { ref, computed } from 'vue'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useProfile } from '../composables/useProfile'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import { resolveStorageUrl } from '@/utils/url'
import { t } from '@/i18n'
import AppAvatar from '@/components/ui/AppAvatar.vue'
import AppButton from '@/components/ui/AppButton.vue'

const authStore = useAuthStore()
const { uploadAvatar, loading } = useProfile()
const uiStore = useUiStore()

const fileInput = ref<HTMLInputElement | null>(null)
const previewUrl = ref<string | null>(null)
const selectedFile = ref<File | null>(null)

const currentAvatarUrl = computed(() => {
  return resolveStorageUrl(authStore.user?.profile_photo_url || authStore.user?.profile_photo || null)
})

function handleFileSelect(event: Event): void {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return
  if (!file.type.startsWith('image/')) {
    uiStore.error(t('common.imageOnlyError'))
    return
  }
  if (file.size > 2 * 1024 * 1024) {
    uiStore.error(t('common.imageSizeError'))
    return
  }
  selectedFile.value = file
  previewUrl.value = URL.createObjectURL(file)
}

async function handleUpload(): Promise<void> {
  if (!selectedFile.value) return
  try {
    await uploadAvatar(selectedFile.value)
    uiStore.success(t('common.photoUpdated'))
    selectedFile.value = null
    previewUrl.value = null
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  }
}
</script>

<template>
  <div class="flex flex-col sm:flex-row items-center gap-6">
    <!-- Avatar Preview -->
    <div class="relative">
      <AppAvatar
        :src="previewUrl || currentAvatarUrl || undefined"
        :name="authStore.user?.full_name || 'User'"
        size="xl"
        class="ring-4 ring-white shadow-md"
      />
      <button
        type="button"
        class="absolute bottom-0 right-0 h-8 w-8 rounded-full bg-[#0B5D3B] text-white shadow flex items-center justify-center hover:bg-[#084C30] transition cursor-pointer"
        :title="t('common.changePhoto')"
        @click="fileInput?.click()"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
        </svg>
      </button>
    </div>

    <div class="space-y-2 text-center sm:text-left">
      <p class="text-xs text-slate-500">
        {{ t('common.uploadHint') }}
      </p>

      <div class="flex items-center justify-center sm:justify-start gap-2">
        <AppButton variant="outline" size="sm" type="button" @click="fileInput?.click()">
          {{ t('common.addPhoto') }}
        </AppButton>
        <AppButton
          v-if="selectedFile"
          variant="primary"
          size="sm"
          :loading="loading"
          type="button"
          @click="handleUpload"
        >
          {{ t('common.save') }}
        </AppButton>
      </div>

      <p v-if="selectedFile" class="text-xs text-[#0B5D3B] dark:text-[#75bd97] font-medium">
        {{ selectedFile.name }} selected
      </p>
    </div>

    <input
      ref="fileInput"
      type="file"
      accept="image/jpeg,image/png,image/webp"
      class="hidden"
      @change="handleFileSelect"
    />
  </div>
</template>
