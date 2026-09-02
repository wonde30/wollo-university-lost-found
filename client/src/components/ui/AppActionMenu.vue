<script setup lang="ts">
import { ref, nextTick, onMounted, onUnmounted } from 'vue'
import { MoreVertical } from 'lucide-vue-next'

interface Props {
  widthClass?: string
  align?: 'left' | 'right'
}

const props = withDefaults(defineProps<Props>(), {
  widthClass: 'w-52',
  align: 'right',
})

const isOpen = ref(false)
const triggerRef = ref<HTMLElement | null>(null)
const menuRef = ref<HTMLElement | null>(null)

const menuStyle = ref<{
  top?: string
  bottom?: string
  left?: string
  right?: string
  maxHeight?: string
}>({})

function updatePosition() {
  if (!triggerRef.value) return
  const rect = triggerRef.value.getBoundingClientRect()
  const viewportHeight = window.innerHeight
  const viewportWidth = window.innerWidth
  const spaceBelow = viewportHeight - rect.bottom
  const spaceAbove = rect.top
  const estimatedHeight = menuRef.value ? menuRef.value.offsetHeight : 240

  const style: {
    top?: string
    bottom?: string
    left?: string
    right?: string
    maxHeight?: string
  } = {}

  // Flip upward if space below is insufficient and space above is larger
  if (spaceBelow < estimatedHeight && spaceAbove > spaceBelow) {
    style.bottom = `${viewportHeight - rect.top + 4}px`
    style.maxHeight = `${Math.min(spaceAbove - 16, 320)}px`
  } else {
    style.top = `${rect.bottom + 4}px`
    style.maxHeight = `${Math.min(spaceBelow - 16, 320)}px`
  }

  // Horizontal alignment
  if (props.align === 'left') {
    style.left = `${Math.max(8, rect.left)}px`
  } else {
    style.right = `${Math.max(8, viewportWidth - rect.right)}px`
  }

  menuStyle.value = style
}

function toggle(event?: Event) {
  if (event) {
    event.stopPropagation()
  }
  if (isOpen.value) {
    close()
  } else {
    open()
  }
}

function open() {
  isOpen.value = true
  updatePosition()
  nextTick(() => {
    updatePosition()
  })
}

function close() {
  isOpen.value = false
}

function handleClickOutside(event: MouseEvent) {
  if (!isOpen.value) return
  const target = event.target as Node | null
  if (
    triggerRef.value &&
    (triggerRef.value === target || triggerRef.value.contains(target))
  ) {
    return
  }
  if (
    menuRef.value &&
    (menuRef.value === target || menuRef.value.contains(target))
  ) {
    return
  }
  close()
}

function handleScroll(event: Event) {
  if (!isOpen.value) return
  // If scrolling inside the menu itself, don't close
  if (menuRef.value && menuRef.value.contains(event.target as Node)) {
    return
  }
  close()
}

function handleKeyDown(event: KeyboardEvent) {
  if (event.key === 'Escape' && isOpen.value) {
    close()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside, true)
  window.addEventListener('scroll', handleScroll, { capture: true, passive: true })
  window.addEventListener('resize', close, { passive: true })
  window.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside, true)
  window.removeEventListener('scroll', handleScroll, { capture: true })
  window.removeEventListener('resize', close)
  window.removeEventListener('keydown', handleKeyDown)
})

defineExpose({
  open,
  close,
  toggle,
  isOpen,
})
</script>

<template>
  <div class="relative inline-flex items-center justify-center">
    <!-- Trigger Button -->
    <div ref="triggerRef" @click.stop="toggle">
      <slot name="trigger" :is-open="isOpen" :toggle="toggle">
        <button
          type="button"
          :class="[
            'h-8 w-8 rounded-full flex items-center justify-center transition-colors cursor-pointer',
            isOpen
              ? 'bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white ring-2 ring-[#0B5D3B]/30 dark:ring-[#75bd97]/30'
              : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-900 dark:hover:text-white',
          ]"
          aria-label="Actions"
        >
          <MoreVertical class="h-4 w-4" />
        </button>
      </slot>
    </div>

    <!-- Teleported Floating Dropdown Menu -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-100 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-75 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <div
          v-if="isOpen"
          ref="menuRef"
          :class="[
            'fixed z-9999 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xl py-1 overflow-y-auto text-left text-sm',
            props.widthClass,
          ]"
          :style="menuStyle"
          @click.stop
        >
          <slot :close="close" />
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
