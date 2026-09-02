<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  title?: string
  subtitle?: string
  variant?: 'default' | 'bordered' | 'flat' | 'elevated'
  padding?: 'none' | 'sm' | 'md' | 'lg'
  hover?: boolean
  clickable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  title: '',
  subtitle: '',
  variant: 'default',
  padding: 'md',
  hover: false,
  clickable: false,
})

defineEmits<{
  (e: 'click', event: MouseEvent): void
}>()

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'flat':
      return 'bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800 text-slate-800 dark:text-slate-200'
    case 'bordered':
      return 'bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-700 shadow-none text-slate-900 dark:text-slate-100'
    case 'elevated':
      return 'bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 shadow-md text-slate-900 dark:text-slate-100'
    case 'default':
    default:
      return 'bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 shadow-2xs text-slate-900 dark:text-slate-100'
  }
})

const paddingClasses = computed(() => {
  switch (props.padding) {
    case 'none':
      return 'p-0'
    case 'sm':
      return 'p-3 sm:p-3.5'
    case 'lg':
      return 'p-5 sm:p-6'
    case 'md':
    default:
      return 'p-4 sm:p-5'
  }
})
</script>

<template>
  <div
    :class="[
      'rounded-2xl transition-all duration-200',
      variantClasses,
      paddingClasses,
      hover ? 'hover:shadow-md hover:-translate-y-0.5' : '',
      clickable ? 'cursor-pointer select-none active:scale-[0.99]' : '',
    ]"
    @click="$emit('click', $event)"
  >
    <!-- Header -->
    <div
      v-if="title || subtitle || $slots.header || $slots.actions"
      class="flex items-center justify-between gap-3 pb-3 mb-3 border-b border-slate-100 dark:border-slate-800"
    >
      <div>
        <slot name="header">
          <h3 v-if="title" class="text-base sm:text-lg font-bold tracking-tight text-slate-900 dark:text-slate-100 leading-snug">{{ title }}</h3>
          <p v-if="subtitle" class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">{{ subtitle }}</p>
        </slot>
      </div>

      <div v-if="$slots.actions" class="shrink-0 flex items-center gap-2">
        <slot name="actions" />
      </div>
    </div>

    <!-- Body -->
    <slot />

    <!-- Footer -->
    <div
      v-if="$slots.footer"
      class="pt-3 mt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between"
    >
      <slot name="footer" />
    </div>
  </div>
</template>
