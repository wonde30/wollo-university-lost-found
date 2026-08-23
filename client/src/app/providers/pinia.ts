import type { App } from 'vue'
import { createPinia } from 'pinia'

export function installPinia(app: App): void {
  const pinia = createPinia()
  app.use(pinia)
}