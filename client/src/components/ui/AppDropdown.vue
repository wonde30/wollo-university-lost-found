<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

export interface DropdownItem {
  label: string
  key?: string
  icon?: string
  danger?: boolean
  disabled?: boolean
  divider?: boolean
  onClick?: () => void
}

interface Props {
  items?: DropdownItem[]
  align?: 'left' | 'right'
  width?: string
}

withDefaults(defineProps<Props>(), {
  items: () => [],
  align: 'right',
  width: 'w-48',
})

const isOpen = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)

function toggle() {
  isOpen.value = !isOpen.value
}

function close() {
  isOpen.value = false
}

function handleClickOutside(event: MouseEvent) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    close()
  }
}

onMounted(() => {
  if (typeof document !== 'undefined') {
    document.addEventListener('click', handleClickOutside)
  }
})

onUnmounted(() => {
  if (typeof document !== 'undefined') {
    document.removeEventListener('click', handleClickOutside)
  }
})
</script>

<template>
  <div ref="dropdownRef" class="relative inline-block text-left">
    <div @click="toggle">
      <slot :is-open="isOpen" />
    </div>

    <Transition name="dropdown">
      <div
        v-if="isOpen"
        :class="[
          'absolute z-50 mt-2 rounded-2xl bg-white p-1.5 shadow-xl border border-slate-200/90 ring-1 ring-black/5 focus:outline-none select-none',
          align === 'right' ? 'right-0 origin-top-right' : 'left-0 origin-top-left',
          width,
        ]"
        @click="close"
      >
        <slot name="menu">
          <template v-for="(item, index) in items" :key="item.key || index">
            <div v-if="item.divider" class="my-1 border-t border-slate-100" />
            <button
              v-else
              type="button"
              :disabled="item.disabled"
              :class="[
                'w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium rounded-xl transition-colors text-left cursor-pointer',
                item.danger
                  ? 'text-rose-600 hover:bg-rose-50'
                  : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900',
                item.disabled ? 'opacity-40 cursor-not-allowed pointer-events-none' : '',
              ]"
              @click="item.onClick && item.onClick()"
            >
              <span>{{ item.label }}</span>
            </button>
          </template>
        </slot>
      </div>
    </Transition>
  </div>
</template>
