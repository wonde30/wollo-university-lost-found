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
      return 'bg-slate-50 border border-slate-200/60'
    case 'bordered':
      return 'bg-white border-2 border-slate-200 shadow-none'
    case 'elevated':
      return 'bg-white border border-slate-100 shadow-lg'
    case 'default':
    default:
      return 'bg-white border border-slate-200/80 shadow-xs'
  }
})

const paddingClasses = computed(() => {
  switch (props.padding) {
    case 'none':
      return 'p-0'
    case 'sm':
      return 'p-3.5'
    case 'lg':
      return 'p-6 sm:p-8'
    case 'md':
    default:
      return 'p-5 sm:p-6'
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
      class="flex items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100"
    >
      <div>
        <slot name="header">
          <h3 v-if="title" class="text-base font-bold text-slate-900 leading-snug">{{ title }}</h3>
          <p v-if="subtitle" class="text-xs text-slate-500 mt-0.5">{{ subtitle }}</p>
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
      class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between"
    >
      <slot name="footer" />
    </div>
  </div>
</template>
