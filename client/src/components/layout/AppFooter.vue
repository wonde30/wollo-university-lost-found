<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useSettingsStore } from '@/stores/settings.store'
import { t } from '@/i18n'
import {
  MapPin,
  Mail,
  Phone,
  Facebook,
  Twitter,
  Youtube,
  Linkedin,
  Heart,
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const settingsStore = useSettingsStore()
const currentYear = new Date().getFullYear()

function handleReportLost() {
  if (authStore.isAuthenticated) {
    router.push('/report-lost')
  } else {
    router.push({ name: 'login', query: { redirect: '/report-lost' } })
  }
}

function handleReportFound() {
  if (authStore.isAuthenticated) {
    router.push('/report-found')
  } else {
    router.push({ name: 'login', query: { redirect: '/report-found' } })
  }
}
</script>

<template>
  <footer class="bg-[#0b1329] text-slate-300 pt-14 pb-8 border-t border-slate-800/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- 4 Column Main Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 pb-12 border-b border-slate-800/80">
        <!-- Col 1: Brand & Social -->
        <div class="space-y-4">
          <div class="flex items-center gap-3">
            <img
              :src="settingsStore.logoUrl"
              :alt="settingsStore.institutionName + ' Emblem'"
              class="h-10 w-10 shrink-0 object-contain rounded-full bg-white p-0.5 shadow"
              @error="($event.target as HTMLImageElement).src = '/images/wu-logo.png'"
            />
            <div>
              <div class="text-sm font-black tracking-tight text-white leading-tight">
                {{ settingsStore.institutionName }}
              </div>
              <div class="text-[11px] font-bold text-[#D4AF37] uppercase tracking-wider">
                {{ t('common.appName') }}
              </div>
            </div>
          </div>

          <p class="text-xs text-slate-400 leading-relaxed max-w-xs">
            {{ t('footer.brandDescription') }}
          </p>

          <!-- Social Icons -->
          <div class="flex items-center gap-2.5 pt-1">
            <a
              href="https://facebook.com"
              target="_blank"
              rel="noopener noreferrer"
              class="h-8 w-8 rounded-full bg-slate-800/90 hover:bg-[#0B5D3B] text-slate-300 hover:text-white flex items-center justify-center transition-colors"
              :aria-label="t('footer.socialFacebook')"
            >
              <Facebook class="h-4 w-4" />
            </a>
            <a
              href="https://twitter.com"
              target="_blank"
              rel="noopener noreferrer"
              class="h-8 w-8 rounded-full bg-slate-800/90 hover:bg-[#0B5D3B] text-slate-300 hover:text-white flex items-center justify-center transition-colors"
              :aria-label="t('footer.socialTwitter')"
            >
              <Twitter class="h-4 w-4" />
            </a>
            <a
              href="https://youtube.com"
              target="_blank"
              rel="noopener noreferrer"
              class="h-8 w-8 rounded-full bg-slate-800/90 hover:bg-[#0B5D3B] text-slate-300 hover:text-white flex items-center justify-center transition-colors"
              :aria-label="t('footer.socialYoutube')"
            >
              <Youtube class="h-4 w-4" />
            </a>
            <a
              href="https://linkedin.com"
              target="_blank"
              rel="noopener noreferrer"
              class="h-8 w-8 rounded-full bg-slate-800/90 hover:bg-[#0B5D3B] text-slate-300 hover:text-white flex items-center justify-center transition-colors"
              :aria-label="t('footer.socialLinkedin')"
            >
              <Linkedin class="h-4 w-4" />
            </a>
          </div>
        </div>

        <!-- Col 2: Quick Links -->
        <div class="space-y-3.5">
          <h4 class="text-xs font-black uppercase tracking-wider text-white">
            {{ t('footer.quickLinks') }}
          </h4>
          <ul class="space-y-2 text-xs text-slate-400">
            <li>
              <RouterLink to="/" class="hover:text-white transition-colors">
                {{ t('nav.home') }}
              </RouterLink>
            </li>
            <li>
              <RouterLink to="/browse" class="hover:text-white transition-colors">
                {{ t('nav.browseItems') }}
              </RouterLink>
            </li>
            <li>
              <button
                type="button"
                class="hover:text-white transition-colors cursor-pointer text-left"
                @click="handleReportLost"
              >
                {{ t('nav.reportLost') }}
              </button>
            </li>
            <li>
              <button
                type="button"
                class="hover:text-white transition-colors cursor-pointer text-left"
                @click="handleReportFound"
              >
                {{ t('nav.reportFound') }}
              </button>
            </li>
            <li>
              <RouterLink to="/track" class="hover:text-white transition-colors">
                {{ t('nav.trackItem') }}
              </RouterLink>
            </li>
          </ul>
        </div>

        <!-- Col 3: Resources -->
        <div class="space-y-3.5">
          <h4 class="text-xs font-black uppercase tracking-wider text-white">
            {{ t('footer.resources') }}
          </h4>
          <ul class="space-y-2 text-xs text-slate-400">
            <li>
              <RouterLink to="/browse" class="hover:text-white transition-colors">
                {{ t('nav.about') }}
              </RouterLink>
            </li>
            <li>
              <RouterLink to="/track" class="hover:text-white transition-colors">
                {{ t('footer.helpSupport') }}
              </RouterLink>
            </li>
            <li>
              <a :href="settingsStore.institutionWebsite" target="_blank" rel="noopener noreferrer" class="hover:text-white transition-colors">
                {{ t('footer.termsOfService') }}
              </a>
            </li>
            <li>
              <a :href="settingsStore.institutionWebsite" target="_blank" rel="noopener noreferrer" class="hover:text-white transition-colors">
                {{ t('footer.privacyPolicy') }}
              </a>
            </li>
            <li>
              <a :href="'mailto:' + (settingsStore.contactEmail || 'lostfound@wu.edu.et')" class="hover:text-white transition-colors">
                {{ t('footer.contactUs') }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Col 4: Contact Us -->
        <div class="space-y-3.5">
          <h4 class="text-xs font-black uppercase tracking-wider text-white">
            {{ t('footer.contactUs') }}
          </h4>
          <ul class="space-y-3 text-xs text-slate-400">
            <li class="flex items-start gap-2.5">
              <MapPin class="h-4 w-4 text-[#D4AF37] shrink-0 mt-0.5" />
              <span>
                {{ t('footer.locationText', { institution: settingsStore.institutionName }) }}
              </span>
            </li>
            <li class="flex items-center gap-2.5">
              <Mail class="h-4 w-4 text-[#D4AF37] shrink-0" />
              <a :href="'mailto:' + (settingsStore.contactEmail || 'lostfound@wu.edu.et')" class="hover:text-white transition-colors">
                {{ settingsStore.contactEmail || 'lostfound@wu.edu.et' }}
              </a>
            </li>
            <li class="flex items-center gap-2.5">
              <Phone class="h-4 w-4 text-[#D4AF37] shrink-0" />
              <a :href="'tel:' + (settingsStore.contactPhone || '+251333115200')" class="hover:text-white transition-colors">
                {{ settingsStore.contactPhone || '+251 33 311 5200' }}
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
        <p>
          &copy; {{ currentYear }} {{ settingsStore.institutionName }}. {{ t('footer.rightsReserved') }}
        </p>
        <p class="flex items-center gap-1.5 font-medium text-slate-400">
          {{ t('footer.motto') }}
          <Heart class="h-3.5 w-3.5 text-rose-500 fill-rose-500 inline shrink-0" />
        </p>
      </div>
    </div>
  </footer>
</template>
