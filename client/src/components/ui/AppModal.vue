<script setup lang="ts">
import { watch, onMounted, onUnmounted } from 'vue'

interface Props {
  open?: boolean
  show?: boolean
  title?: string
  size?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'full'
  closable?: boolean
  persistent?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  open: undefined,
  show: false,
  title: '',
  size: 'md',
  closable: true,
  persistent: false,
})

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'update:open', value: boolean): void
}>()

function isOpen(): boolean {
  if (props.open !== undefined) return props.open
  return props.show
}

function handleBackdropClick(event: MouseEvent): void {
  if (props.closable && !props.persistent && event.target === event.currentTarget) {
    emit('close')
    emit('update:open', false)
  }
}

function handleKeydown(event: KeyboardEvent): void {
  if (props.closable && !props.persistent && isOpen() && event.key === 'Escape') {
    emit('close')
    emit('update:open', false)
  }
}

watch(
  () => isOpen(),
  (openState) => {
    if (typeof document !== 'undefined') {
      if (openState) {
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
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen()"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm overflow-y-auto"
        @click="handleBackdropClick"
      >
        <div
          :class="[
            'relative w-full rounded-2xl bg-white p-6 shadow-2xl transition-all my-8 max-h-[90vh] flex flex-col border border-slate-200/80',
            size === 'sm' ? 'max-w-md' : '',
            size === 'md' ? 'max-w-lg' : '',
            size === 'lg' ? 'max-w-2xl' : '',
            size === 'xl' ? 'max-w-4xl' : '',
            size === '2xl' ? 'max-w-5xl' : '',
            size === 'full' ? 'max-w-full m-4 h-[calc(100vh-2rem)]' : '',
          ]"
        >
          <!-- Header -->
          <div v-if="title || $slots.header || closable" class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
            <slot name="header">
              <h3 class="text-base font-bold text-slate-900">{{ title }}</h3>
            </slot>
            <button
              v-if="closable"
              type="button"
              class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors cursor-pointer"
              @click="emit('close'); emit('update:open', false)"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="py-4 overflow-y-auto flex-1 text-slate-700 text-sm">
            <slot />
          </div>

          <!-- Footer -->
          <div v-if="$slots.footer" class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
