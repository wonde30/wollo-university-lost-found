import type { Directive, DirectiveBinding } from 'vue'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import type { PermissionKey } from '@/permissions'

export const vCan: Directive<HTMLElement, PermissionKey | string> = {
  mounted(el: HTMLElement, binding: DirectiveBinding<PermissionKey | string>) {
    checkPermission(el, binding)
  },
  updated(el: HTMLElement, binding: DirectiveBinding<PermissionKey | string>) {
    checkPermission(el, binding)
  },
}

function checkPermission(el: HTMLElement, binding: DirectiveBinding<PermissionKey | string>): void {
  const authStore = useAuthStore()
  const permission = binding.value

  if (!permission) return

  const allowed = authStore.can(permission)

  if (!allowed) {
    el.style.display = 'none'
  } else {
    el.style.removeProperty('display')
  }
}
