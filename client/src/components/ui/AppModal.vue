<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'

interface Props {
  modelValue?: boolean
  open?: boolean
  show?: boolean
  isOpen?: boolean
  title?: string
  size?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'full'
  maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'full'
  closable?: boolean
  persistent?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: undefined,
  open: undefined,
  show: false,
  isOpen: undefined,
  title: '',
  size: 'md',
  maxWidth: undefined,
  closable: true,
  persistent: false,
})

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'update:modelValue', value: boolean): void
  (e: 'update:open', value: boolean): void
  (e: 'update:isOpen', value: boolean): void
}>()

const modalContentRef = ref<HTMLElement | null>(null)
let previousActiveElement: HTMLElement | null = null

const effectiveSize = computed(() => props.maxWidth || props.size)

function isOpen(): boolean {
  if (props.modelValue !== undefined) return props.modelValue
  if (props.isOpen !== undefined) return props.isOpen
  if (props.open !== undefined) return props.open
  return props.show
}

function handleBackdropClick(event: MouseEvent): void {
  if (props.closable && !props.persistent && event.target === event.currentTarget) {
    closeModal()
  }
}

function handleKeydown(event: KeyboardEvent): void {
  if (props.closable && !props.persistent && isOpen() && event.key === 'Escape') {
    closeModal()
  }
}

function closeModal() {
  emit('close')
  emit('update:modelValue', false)
  emit('update:open', false)
  emit('update:isOpen', false)
}

watch(
  () => isOpen(),
  (openState) => {
    if (typeof document !== 'undefined') {
      if (openState) {
        previousActiveElement = document.activeElement as HTMLElement | null
        document.body.style.overflow = 'hidden'
        nextTick(() => {
          if (modalContentRef.value) {
            const focusable = modalContentRef.value.querySelector<HTMLElement>(
              'input:not([type="hidden"]):not([disabled]), textarea:not([disabled]), select:not([disabled]), button:not([disabled]):not([aria-label="Close"])'
            )
            if (focusable) {
              focusable.focus()
            } else {
              modalContentRef.value.focus()
            }
          }
        })
      } else {
        document.body.style.overflow = ''
        if (previousActiveElement && typeof previousActiveElement.focus === 'function') {
          previousActiveElement.focus()
        }
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
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen()"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm overflow-y-auto"
        role="dialog"
        aria-modal="true"
        @click="handleBackdropClick"
      >
        <div
          ref="modalContentRef"
          tabindex="-1"
          :class="[
            'relative w-full rounded-2xl bg-white dark:bg-[#111827] p-5 sm:p-6 shadow-2xl transition-all my-6 max-h-[90vh] flex flex-col border border-slate-200/80 dark:border-slate-800 focus:outline-none',
            effectiveSize === 'sm' ? 'max-w-md' : '',
            effectiveSize === 'md' ? 'max-w-lg' : '',
            effectiveSize === 'lg' ? 'max-w-2xl' : '',
            effectiveSize === 'xl' ? 'max-w-4xl' : '',
            effectiveSize === '2xl' ? 'max-w-5xl' : '',
            effectiveSize === 'full' ? 'max-w-full m-4 h-[calc(100vh-2rem)]' : '',
          ]"
        >
          <!-- Header -->
          <div v-if="title || $slots.header || closable" class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800 shrink-0">
            <slot name="header">
              <h3 class="text-base sm:text-lg font-bold tracking-tight text-slate-900 dark:text-white">{{ title }}</h3>
            </slot>
            <button
              v-if="closable"
              type="button"
              aria-label="Close"
              class="rounded-lg p-1.5 text-slate-400 dark:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:text-slate-200 transition-colors cursor-pointer"
              @click="closeModal"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="py-3.5 overflow-y-auto flex-1 text-slate-700 dark:text-slate-300 text-sm">
            <slot />
          </div>

          <!-- Footer -->
          <div v-if="$slots.footer" class="pt-3.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3 shrink-0">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
