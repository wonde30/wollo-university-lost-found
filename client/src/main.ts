import { createApp } from 'vue'
import '@/assets/styles/main.css'
import App from '@/app/App.vue'
import { installRouter } from '@/app/providers/router'
import { installPinia } from '@/app/providers/pinia'
import { installDirectives } from '@/directives'
import '@/lib/http/interceptors'

const app = createApp(App)

installPinia(app)
installRouter(app)
installDirectives(app)

/**
 * Global Vue error handler.
 * Catches unhandled errors from component render functions, watchers,
 * lifecycle hooks, and event handlers.
 */
app.config.errorHandler = (err, instance, info) => {
  console.error('[Vue Error]', err)
  if (import.meta.env.DEV) {
    console.error('[Component]', instance)
    console.error('[Info]', info)
  }
}

app.mount('#app')
