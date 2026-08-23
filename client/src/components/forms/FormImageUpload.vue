<script setup lang="ts">
import { ref } from 'vue'
import { isValidImage, readFileAsDataURL } from '@/utils/file'

interface Props {
  label?: string
  error?: string | null
  required?: boolean
  currentImage?: string | null
}

const props = withDefaults(defineProps<Props>(), {
  label: 'Upload Image',
  error: null,
  required: false,
  currentImage: null,
})

const emit = defineEmits<{
  (e: 'file-selected', file: File): void
  (e: 'remove'): void
}>()

const previewUrl = ref<string | null>(props.currentImage)
const localError = ref<string | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)

async function handleFileChange(event: Event): Promise<void> {
  localError.value = null
  const input = event.target as HTMLInputElement
  if (!input.files || input.files.length === 0) return

  const file = input.files[0]
  if (!file) return

  const check = isValidImage(file)
  if (!check.valid) {
    localError.value = check.error || 'Invalid image file'
    return
  }

  try {
    previewUrl.value = await readFileAsDataURL(file)
    emit('file-selected', file)
  } catch {
    localError.value = 'Failed to load preview'
  }
}

function handleRemove(): void {
  previewUrl.value = null
  if (fileInput.value) fileInput.value.value = ''
  emit('remove')
}
</script>

<template>
  <div class="w-full space-y-1.5">
    <label v-if="label" class="block text-sm font-medium text-slate-700">
      {{ label }}
      <span v-if="required" class="text-rose-500">*</span>
    </label>

    <div
      class="relative flex flex-col items-center justify-center p-6 border-2 border-dashed rounded-2xl transition-colors cursor-pointer bg-slate-50/50 hover:bg-slate-50 border-slate-300"
      @click="fileInput?.click()"
    >
      <input
        ref="fileInput"
        type="file"
        accept="image/png, image/jpeg, image/webp"
        class="hidden"
        @change="handleFileChange"
      />

      <div v-if="previewUrl" class="relative group">
        <img
          :src="previewUrl"
          alt="Uploaded preview"
          class="max-h-48 rounded-xl object-contain shadow-sm"
        />
        <button
          type="button"
          class="absolute top-2 right-2 p-1.5 rounded-full bg-slate-900/75 text-white hover:bg-rose-600 transition-colors"
          @click.stop="handleRemove"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div v-else class="text-center">
        <svg class="mx-auto h-10 w-10 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="text-xs font-semibold text-slate-700">Click to upload photo</p>
        <p class="text-[11px] text-slate-400 mt-1">PNG, JPG, WebP up to 5MB</p>
      </div>
    </div>

    <p v-if="error || localError" class="text-xs text-rose-600 font-medium">
      {{ error || localError }}
    </p>
  </div>
</template>
