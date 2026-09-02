<script setup lang="ts">
import { ref } from 'vue'
import { isValidImage, readFileAsDataURL } from '@/utils/file'
import { t } from '@/i18n'

interface Props {
  label?: string
  error?: string | null
  maxFiles?: number
}

const props = withDefaults(defineProps<Props>(), {
  label: undefined,
  error: null,
  maxFiles: 5,
})

const emit = defineEmits<{
  (e: 'files-updated', files: File[]): void
}>()

const files = ref<File[]>([])
const previews = ref<string[]>([])
const localError = ref<string | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)

async function handleFileChange(event: Event): Promise<void> {
  localError.value = null
  const input = event.target as HTMLInputElement
  if (!input.files || input.files.length === 0) return

  const selected = Array.from(input.files)

  if (files.value.length + selected.length > props.maxFiles) {
    localError.value = t('validation.maxLength', { max: props.maxFiles })
    return
  }

  for (const file of selected) {
    const check = isValidImage(file)
    if (!check.valid) {
      localError.value = t('validation.invalidFileType')
      return
    }

    if (file.size > 5 * 1024 * 1024) {
      localError.value = t('validation.fileTooLarge', { size: 5 })
      return
    }
  }

  for (const file of selected) {
    try {
      const url = await readFileAsDataURL(file)
      files.value.push(file)
      previews.value.push(url)
    } catch {
      localError.value = t('common.errorOccurred')
    }
  }

  emit('files-updated', files.value)
  if (fileInput.value) fileInput.value.value = ''
}

function removeFile(index: number): void {
  files.value.splice(index, 1)
  previews.value.splice(index, 1)
  emit('files-updated', files.value)
}
</script>

<template>
  <div class="space-y-2">
    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
      {{ label || t('reportWizard.uploadPhotos') }}
    </label>

    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
      <!-- Upload trigger box -->
      <div
        v-if="files.length < maxFiles"
        class="h-28 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-[#0B5D3B] dark:hover:border-[#3e9e70] bg-slate-50 dark:bg-slate-800/50 hover:bg-[#E8F4EE]/30 dark:hover:bg-[#153C2D]/20 flex flex-col items-center justify-center cursor-pointer transition-colors p-2 text-center"
        @click="fileInput?.click()"
      >
        <input
          ref="fileInput"
          type="file"
          accept="image/png, image/jpeg, image/webp"
          multiple
          class="hidden"
          @change="handleFileChange"
        />
        <svg class="h-6 w-6 text-slate-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span class="text-[11px] font-medium text-slate-600 dark:text-slate-300">{{ t('common.addPhoto') }}</span>
      </div>

      <!-- Preview items -->
      <div
        v-for="(url, index) in previews"
        :key="index"
        class="relative h-28 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 group bg-slate-100 dark:bg-slate-800"
      >
        <img :src="url" :alt="t('common.uploadedPhoto')" class="h-full w-full object-cover" />
        <button
          type="button"
          class="absolute top-1.5 right-1.5 p-1 rounded-full bg-slate-900/80 text-white hover:bg-rose-600 transition-colors"
          @click.stop="removeFile(index)"
        >
          <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <p v-if="error || localError" class="text-xs text-rose-600 font-medium">
      {{ error || localError }}
    </p>
  </div>
</template>
