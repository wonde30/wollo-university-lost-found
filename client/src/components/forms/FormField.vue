<script setup lang="ts">
interface Props {
  label?: string
  error?: string | null
  hint?: string
  required?: boolean
  id?: string
}

withDefaults(defineProps<Props>(), {
  label: '',
  error: null,
  hint: '',
  required: false,
  id: '',
})
</script>

<template>
  <div class="w-full space-y-1.5">
    <label
      v-if="label"
      :for="id"
      class="block text-sm font-medium text-slate-700"
    >
      {{ label }}
      <span v-if="required" class="text-rose-500">*</span>
    </label>

    <slot :has-error="!!error" />

    <p v-if="error" class="text-xs text-rose-600 font-medium">
      {{ error }}
    </p>
    <p v-else-if="hint" class="text-xs text-slate-500">
      {{ hint }}
    </p>
  </div>
</template>
