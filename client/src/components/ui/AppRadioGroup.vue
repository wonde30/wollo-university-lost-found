<script setup lang="ts">
import type { RadioOption } from './types'
export type { RadioOption }

interface Props {
  modelValue?: string | number | null
  options: RadioOption[]
  label?: string
  name?: string
  layout?: 'vertical' | 'horizontal'
  error?: string
}

withDefaults(defineProps<Props>(), {
  modelValue: '',
  label: '',
  name: () => `radio-${Math.random().toString(36).substring(2, 9)}`,
  layout: 'vertical',
  error: '',
})

defineEmits<{
  (e: 'update:modelValue', value: string | number): void
  (e: 'change', value: string | number): void
}>()
</script>

<template>
  <div class="w-full">
    <label v-if="label" class="block text-xs font-semibold text-slate-700 mb-2 select-none">
      {{ label }}
    </label>

    <div
      :class="[
        'gap-3 select-none',
        layout === 'horizontal' ? 'flex flex-wrap items-center' : 'flex flex-col',
      ]"
    >
      <label
        v-for="opt in options"
        :key="opt.value"
        :class="[
          'inline-flex items-start gap-2.5 p-3 rounded-xl border transition-all cursor-pointer',
          modelValue === opt.value
            ? 'border-[#0F5132] bg-emerald-50/40 text-slate-900 shadow-xs'
            : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50',
          opt.disabled ? 'opacity-40 cursor-not-allowed pointer-events-none' : '',
        ]"
      >
        <div class="flex items-center pt-0.5">
          <input
            type="radio"
            :name="name"
            :value="opt.value"
            :checked="modelValue === opt.value"
            :disabled="opt.disabled"
            class="h-4 w-4 border-slate-300 text-[#0F5132] focus:ring-[#0F5132] cursor-pointer"
            @change="$emit('update:modelValue', opt.value); $emit('change', opt.value)"
          />
        </div>

        <div class="text-xs">
          <span class="font-semibold block">{{ opt.label }}</span>
          <p v-if="opt.description" class="text-slate-500 mt-0.5">{{ opt.description }}</p>
        </div>
      </label>
    </div>

    <p v-if="error" class="mt-1.5 text-xs text-rose-600 font-medium">
      {{ error }}
    </p>
  </div>
</template>
