import type { App } from 'vue'
import { vCan } from './v-can'
import { vClickOutside } from './v-click-outside'
import { vFocus } from './v-focus'

export { vCan, vClickOutside, vFocus }

export function installDirectives(app: App): void {
  app.directive('can', vCan)
  app.directive('click-outside', vClickOutside)
  app.directive('focus', vFocus)
}
