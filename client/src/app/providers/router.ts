/**
 * Router provider for Vue application.
 * Installs Vue Router with the configured router instance.
 */

import type { App } from 'vue'
import router from '@/router'

export function installRouter(app: App): void {
  app.use(router)
}