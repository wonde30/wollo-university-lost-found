<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useUiStore } from '@/stores/ui.store'
import { useSettingsStore } from '@/stores/settings.store'
import { getErrorMessage } from '@/utils/error-handler'
import { t } from '@/i18n'
import * as adminApi from '@/features/admin/api/admin.api'
import type { SystemSetting } from '@/features/admin/types/admin.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import {
  ShieldCheck,
  Palette,
  Globe,
  Building2,
  Phone,
  Sliders,
  Upload,
  Image as ImageIcon,
  Save,
  RotateCcw,
  ExternalLink,
  Info,
} from 'lucide-vue-next'

const uiStore = useUiStore()
const settingsStore = useSettingsStore()

const settings = ref<SystemSetting[]>([])
const loading = ref(false)
const saving = ref(false)
const uploadingLogo = ref(false)
const logoFileInput = ref<HTMLInputElement | null>(null)

// Current active tab
type SettingGroup = 'branding' | 'appearance' | 'locale' | 'contact' | 'behavior' | 'security'
const activeTab = ref<SettingGroup>('branding')

const tabs: { id: SettingGroup; label: string; icon: any; description: string }[] = [
  {
    id: 'branding',
    label: 'Branding & Identity',
    icon: Building2,
    description: 'Institution identity, names, logos, and public presentation.',
  },
  {
    id: 'appearance',
    label: 'Theme & Appearance',
    icon: Palette,
    description: 'Primary colors, light/dark mode accents, and brand palette.',
  },
  {
    id: 'locale',
    label: 'Language & Locale',
    icon: Globe,
    description: 'Default languages and localized scripts (e.g. Amharic).',
  },
  {
    id: 'contact',
    label: 'Contact & Support',
    icon: Phone,
    description: 'Support channels, email footers, and PDF document notices.',
  },
  {
    id: 'behavior',
    label: 'System Behavior',
    icon: Sliders,
    description: 'Item retention rules, expiration windows, and AI matching score.',
  },
  {
    id: 'security',
    label: 'Security & Auth',
    icon: ShieldCheck,
    description: 'Login attempts, lockout periods, OTP durations, and email delivery.',
  },
]

// Grouped settings
const currentTabSettings = computed(() => {
  return settings.value.filter(s => (s.group || 'general') === activeTab.value)
})

// Helper to find setting value by key
function getVal(key: string, fallback = ''): string {
  const item = settings.value.find(s => s.key === key)
  return item && item.value !== null && item.value !== undefined ? String(item.value) : fallback
}

function setVal(key: string, val: string | boolean | number): void {
  const item = settings.value.find(s => s.key === key)
  if (item) {
    item.value = String(val)
  }
}

// Live preview computed data
const previewInstitutionName = computed(() => getVal('institution_name', 'Institution Name'))
const previewSiteName = computed(() => getVal('site_name', 'Lost & Found System'))
const previewTagline = computed(() => getVal('institution_tagline', 'Property Recovery Portal'))
const previewLogoUrl = computed(() => getVal('logo_url', '/images/wu-logo.png'))
const previewPrimaryColor = computed(() => getVal('theme_primary_color', '#0B5D3B'))
const previewAccentColor = computed(() => getVal('theme_accent_color', '#D4AF37'))

async function fetchSettings(): Promise<void> {
  loading.value = true
  try {
    const raw = await adminApi.getSystemSettings({ all: true })
    settings.value = raw
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  } finally {
    loading.value = false
  }
}

async function handleSave(): Promise<void> {
  saving.value = true
  try {
    await Promise.all(
      settings.value.map(s =>
        adminApi.updateSystemSetting(s.key, { value: s.value ?? '' }),
      ),
    )

    // Immediately hydrate the frontend settings store & theme styles
    settingsStore.clearCache()
    await settingsStore.fetchSettings()
    settingsStore.applyThemeColors()

    uiStore.success(t('admin.settings.updatedSuccess'))
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  } finally {
    saving.value = false
  }
}

async function handleLogoUpload(event: Event): Promise<void> {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return

  uploadingLogo.value = true
  try {
    const res = await adminApi.uploadLogo(file)
    setVal('logo_url', res.logo_url)

    // Refresh public settings immediately
    settingsStore.clearCache()
    await settingsStore.fetchSettings()

    uiStore.success('Logo updated successfully!')
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to upload logo.'))
  } finally {
    uploadingLogo.value = false
    if (logoFileInput.value) logoFileInput.value.value = ''
  }
}

function triggerLogoInput(): void {
  logoFileInput.value?.click()
}

onMounted(fetchSettings)
</script>

<template>
  <div class="space-y-6">
    <!-- Live Branding & Appearance Preview -->
    <div
      class="relative overflow-hidden rounded-2xl p-5 sm:p-6 border transition-all duration-200"
      :style="{
        borderColor: `${previewPrimaryColor}40`,
        backgroundColor: '#0F172A',
      }"
    >
      <div
        class="absolute -top-16 -right-16 h-64 w-64 rounded-full blur-3xl opacity-30 pointer-events-none"
        :style="{ backgroundColor: previewPrimaryColor }"
      />
      <div
        class="absolute -bottom-16 -left-16 h-64 w-64 rounded-full blur-3xl opacity-20 pointer-events-none"
        :style="{ backgroundColor: previewAccentColor }"
      />

      <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <!-- Live Header Representation -->
        <div class="flex items-center gap-3.5">
          <div class="relative group">
            <img
              :src="previewLogoUrl"
              :alt="previewInstitutionName"
              class="h-12 w-12 rounded-full object-contain bg-white dark:bg-slate-800 p-1 shadow-md ring-2"
              :style="{ borderColor: previewPrimaryColor }"
              @error="($event.target as HTMLImageElement).src = '/images/wu-logo.png'"
            />
            <button
              type="button"
              class="absolute -bottom-1 -right-1 p-1 rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow text-slate-700 dark:text-slate-200 hover:scale-105 transition-transform cursor-pointer"
              title="Upload New Logo"
              @click="triggerLogoInput"
            >
              <Upload class="h-3 w-3" />
            </button>
          </div>

          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <span class="text-xs font-black tracking-tight text-white uppercase truncate">
                {{ previewInstitutionName }}
              </span>
              <span class="text-[10px] font-extrabold uppercase px-1.5 py-0.5 rounded text-white" :style="{ backgroundColor: previewPrimaryColor }">
                Live Preview
              </span>
            </div>
            <p class="text-xs font-semibold text-slate-300 truncate">
              {{ previewSiteName }}
            </p>
            <p class="text-[11px] text-slate-400 truncate">
              {{ previewTagline }}
            </p>
          </div>
        </div>

        <!-- Action / Colors Preview -->
        <div class="flex items-center gap-2 self-start md:self-auto">
          <input
            ref="logoFileInput"
            type="file"
            accept="image/png,image/jpeg,image/svg+xml,image/webp"
            class="hidden"
            @change="handleLogoUpload"
          />

          <AppButton
            variant="outline"
            size="sm"
            class="text-xs text-white border-slate-700 hover:bg-slate-800/80"
            :loading="uploadingLogo"
            @click="triggerLogoInput"
          >
            <template #icon-left>
              <ImageIcon class="h-3.5 w-3.5 text-slate-300 mr-1" />
            </template>
            Change Logo
          </AppButton>

          <a
            :href="getVal('institution_website', '#')"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-1 text-xs font-bold text-slate-300 hover:text-white px-2.5 py-1.5 rounded-lg hover:bg-slate-800/60 transition-colors"
          >
            <ExternalLink class="h-3.5 w-3.5" />
            Website
          </a>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 border-b border-slate-200 dark:border-slate-800 no-scrollbar">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        type="button"
        :class="[
          'flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all shrink-0 cursor-pointer',
          activeTab === tab.id
            ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-sm'
            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60',
        ]"
        @click="activeTab = tab.id"
      >
        <component :is="tab.icon" class="h-4 w-4 shrink-0" />
        {{ tab.label }}
      </button>
    </div>

    <!-- Tab Description -->
    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 px-1">
      <Info class="h-4 w-4 shrink-0 text-slate-400" />
      <span>{{ tabs.find(t => t.id === activeTab)?.description }}</span>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="loading" class="space-y-4">
      <div
        v-for="n in 4"
        :key="n"
        class="p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 space-y-3 animate-pulse"
      >
        <div class="flex items-center justify-between">
          <div class="h-4 w-44 bg-slate-200 dark:bg-slate-700 rounded" />
          <div class="h-4 w-20 bg-slate-200 dark:bg-slate-700 rounded" />
        </div>
        <div class="h-3 w-72 bg-slate-100 dark:bg-slate-800 rounded" />
        <div class="h-9 w-full bg-slate-200 dark:bg-slate-700 rounded-xl" />
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="currentTabSettings.length === 0" class="p-8 text-center rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 text-xs text-slate-400">
      No configuration keys defined for this section.
    </div>

    <!-- Settings Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="setting in currentTabSettings"
        :key="setting.id"
        :class="[
          'p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-[#111827] shadow-2xs hover:border-slate-300 dark:hover:border-slate-700 transition-all space-y-2.5',
          setting.key.includes('description') || setting.key.includes('footer_text') || setting.key.includes('campuses_text')
            ? 'md:col-span-2'
            : '',
        ]"
      >
        <!-- Field Header -->
        <div class="flex items-center justify-between gap-2">
          <label :for="`setting-${setting.key}`" class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 truncate">
            {{ setting.display_name || setting.key }}
          </label>

          <div class="flex items-center gap-1.5 shrink-0">
            <span
              v-if="setting.is_public"
              class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800"
            >
              Public
            </span>
            <span
              v-else
              class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700"
            >
              Internal
            </span>
            <span class="text-[10px] font-mono text-slate-400 bg-slate-50 dark:bg-slate-900 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-800">
              {{ setting.key }}
            </span>
          </div>
        </div>

        <!-- Description -->
        <p v-if="setting.description" class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
          {{ setting.description }}
        </p>

        <!-- Dynamic Input Variant 1: Color Picker -->
        <div v-if="setting.key.includes('color') || setting.key.includes('theme_primary') || setting.key.includes('accent')" class="flex items-center gap-2">
          <input
            type="color"
            :value="setting.value || '#0B5D3B'"
            class="h-9 w-10 p-1 rounded-lg border border-slate-300 dark:border-slate-700 cursor-pointer bg-white dark:bg-slate-900"
            @input="setting.value = ($event.target as HTMLInputElement).value"
          />
          <AppInput
            :id="`setting-${setting.key}`"
            :model-value="String(setting.value || '')"
            placeholder="#0B5D3B"
            class="text-xs font-mono flex-1"
            @update:model-value="setting.value = $event"
          />
        </div>

        <!-- Dynamic Input Variant 2: Boolean Toggle -->
        <div v-else-if="setting.type === 'boolean'" class="pt-1">
          <label class="relative inline-flex items-center cursor-pointer gap-3">
            <input
              type="checkbox"
              :checked="setting.value === 'true' || setting.value === '1' || setting.value === true"
              class="sr-only peer"
              @change="setting.value = ($event.target as HTMLInputElement).checked ? 'true' : 'false'"
            />
            <div
              class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-600"
            />
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
              {{ setting.value === 'true' || setting.value === '1' || setting.value === true ? 'Enabled' : 'Disabled' }}
            </span>
          </label>
        </div>

        <!-- Dynamic Input Variant 3: Textarea for Long Text -->
        <div v-else-if="setting.key.includes('description') || setting.key.includes('footer_text') || setting.key.includes('campuses_text')">
          <textarea
            :id="`setting-${setting.key}`"
            :value="String(setting.value || '')"
            rows="2"
            class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-slate-900 dark:focus:ring-white transition"
            @input="setting.value = ($event.target as HTMLTextAreaElement).value"
          />
        </div>

        <!-- Dynamic Input Variant 4: Number Input -->
        <div v-else-if="setting.type === 'integer' || setting.type === 'float'">
          <input
            :id="`setting-${setting.key}`"
            type="number"
            :step="setting.type === 'float' ? '0.1' : '1'"
            :value="setting.value"
            class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-slate-900 dark:focus:ring-white transition"
            @input="setting.value = ($event.target as HTMLInputElement).value"
          />
        </div>

        <!-- Dynamic Input Variant 5: Standard Text Input -->
        <div v-else>
          <AppInput
            :id="`setting-${setting.key}`"
            :model-value="String(setting.value ?? '')"
            class="text-xs"
            @update:model-value="setting.value = $event"
          />
        </div>
      </div>
    </div>

    <!-- Action Bar -->
    <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-800">
      <AppButton
        variant="outline"
        size="sm"
        :disabled="loading || saving"
        @click="fetchSettings"
      >
        <template #icon-left>
          <RotateCcw class="h-3.5 w-3.5 mr-1" />
        </template>
        Reset Changes
      </AppButton>

      <AppButton
        variant="primary"
        size="sm"
        :loading="saving"
        @click="handleSave"
      >
        <template #icon-left>
          <Save class="h-3.5 w-3.5 mr-1" />
        </template>
        Save All Settings
      </AppButton>
    </div>
  </div>
</template>
