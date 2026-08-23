import type { Directive, DirectiveBinding } from 'vue'

interface ExtendedElement extends HTMLElement {
  __clickOutsideHandler__?: (event: MouseEvent) => void
}

export const vClickOutside: Directive<ExtendedElement, (event: MouseEvent) => void> = {
  mounted(el: ExtendedElement, binding: DirectiveBinding<(event: MouseEvent) => void>) {
    el.__clickOutsideHandler__ = (event: MouseEvent) => {
      if (!(el === event.target || el.contains(event.target as Node))) {
        binding.value(event)
      }
    }
    document.addEventListener('click', el.__clickOutsideHandler__)
  },
  unmounted(el: ExtendedElement) {
    if (el.__clickOutsideHandler__) {
      document.removeEventListener('click', el.__clickOutsideHandler__)
      delete el.__clickOutsideHandler__
    }
  },
}
