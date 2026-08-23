import type { Directive, DirectiveBinding } from 'vue'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { usePermissionsStore } from '@/permissions/stores/permissions.store'
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
  const permissionsStore = usePermissionsStore()
  const permission = binding.value

  if (!permission) return

  const userRole = authStore.user?.role
  const allowed = permissionsStore.isPermissionAllowed(userRole, permission)

  if (!allowed) {
    el.style.display = 'none'
  } else {
    el.style.removeProperty('display')
  }
}
