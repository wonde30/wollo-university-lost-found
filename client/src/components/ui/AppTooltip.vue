<script setup lang="ts">
import { ref, computed } from 'vue'

interface Props {
  text: string
  position?: 'top' | 'bottom' | 'left' | 'right'
  delay?: number
}

const props = withDefaults(defineProps<Props>(), {
  position: 'top',
  delay: 200,
})

const isVisible = ref(false)
let timer: ReturnType<typeof setTimeout> | null = null

function show() {
  if (timer) clearTimeout(timer)
  timer = setTimeout(() => {
    isVisible.value = true
  }, props.delay)
}

function hide() {
  if (timer) clearTimeout(timer)
  isVisible.value = false
}

const positionClasses = computed(() => {
  switch (props.position) {
    case 'bottom':
      return 'top-full left-1/2 -translate-x-1/2 mt-1.5'
    case 'left':
      return 'right-full top-1/2 -translate-y-1/2 mr-1.5'
    case 'right':
      return 'left-full top-1/2 -translate-y-1/2 ml-1.5'
    case 'top':
    default:
      return 'bottom-full left-1/2 -translate-x-1/2 mb-1.5'
  }
})
</script>

<template>
  <div
    class="relative inline-flex"
    @mouseenter="show"
    @mouseleave="hide"
    @focusin="show"
    @focusout="hide"
  >
    <slot />

    <Transition name="fade">
      <div
        v-if="isVisible && text"
        :class="[
          'absolute z-50 px-2 py-1 text-[11px] font-semibold text-white bg-slate-900 rounded-lg shadow-lg whitespace-nowrap pointer-events-none select-none',
          positionClasses,
        ]"
      >
        {{ text }}
      </div>
    </Transition>
  </div>
</template>
