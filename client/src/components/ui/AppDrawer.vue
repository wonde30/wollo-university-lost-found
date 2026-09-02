<script setup lang="ts">
import { watch, onMounted, onUnmounted, computed } from 'vue'

interface Props {
  open: boolean
  position?: 'left' | 'right' | 'bottom'
  size?: 'sm' | 'md' | 'lg' | 'full'
  title?: string
  closable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  open: false,
  position: 'right',
  size: 'md',
  title: '',
  closable: true,
})

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'update:open', value: boolean): void
}>()

function handleBackdropClick(event: MouseEvent) {
  if (props.closable && event.target === event.currentTarget) {
    emit('close')
    emit('update:open', false)
  }
}

function handleKeydown(event: KeyboardEvent) {
  if (props.closable && props.open && event.key === 'Escape') {
    emit('close')
    emit('update:open', false)
  }
}

watch(
  () => props.open,
  (isOpen) => {
    if (typeof document !== 'undefined') {
      if (isOpen) {
        document.body.style.overflow = 'hidden'
      } else {
        document.body.style.overflow = ''
      }
    }
  }
)

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.addEventListener('keydown', handleKeydown)
  }
})

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('keydown', handleKeydown)
    document.body.style.overflow = ''
  }
})

const sizeClasses = computed(() => {
  if (props.position === 'bottom') {
    switch (props.size) {
      case 'sm': return 'h-1/3'
      case 'lg': return 'h-2/3'
      case 'full': return 'h-full'
      case 'md':
      default: return 'h-1/2'
    }
  }

  switch (props.size) {
    case 'sm': return 'max-w-xs'
    case 'lg': return 'max-w-lg'
    case 'full': return 'max-w-full'
    case 'md':
    default: return 'max-w-md'
  }
})

const positionClasses = computed(() => {
  switch (props.position) {
    case 'left':
      return 'inset-y-0 left-0 border-r border-slate-200 dark:border-slate-800'
    case 'bottom':
      return 'inset-x-0 bottom-0 border-t border-slate-200 dark:border-slate-800 rounded-t-2xl'
    case 'right':
    default:
      return 'inset-y-0 right-0 border-l border-slate-200 dark:border-slate-800'
  }
})

const transitionName = computed(() => {
  return `drawer-${props.position}`
})
</script>

<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="open"
        class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex"
        :class="position === 'bottom' ? 'items-end' : (position === 'left' ? 'justify-start' : 'justify-end')"
        @click="handleBackdropClick"
      >
        <Transition :name="transitionName" appear>
          <div
            v-if="open"
            :class="[
              'w-full bg-white dark:bg-slate-900 shadow-2xl flex flex-col',
              positionClasses,
              sizeClasses,
            ]"
            @click.stop
          >
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800 shrink-0">
              <slot name="header">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ title }}</h3>
              </slot>

              <button
                v-if="closable"
                type="button"
                class="rounded-lg p-1.5 text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-200 transition-colors cursor-pointer"
                @click="emit('close'); emit('update:open', false)"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Body -->
            <div class="flex-1 overflow-y-auto p-6 text-sm text-slate-700 dark:text-slate-300">
              <slot />
            </div>

            <!-- Footer -->
            <div v-if="$slots.footer" class="p-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3 shrink-0 bg-slate-50/50 dark:bg-slate-800/40">
              <slot name="footer" />
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
