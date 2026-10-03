<script setup lang="ts">
import { onMounted, ref, reactive } from 'vue'
import { useUiStore } from '@/stores/ui.store'
import { useReferencesStore } from '@/features/lookups/stores/references.store'
import { getErrorMessage } from '@/utils/error-handler'
import { t } from '@/i18n'
import type { UniversityDomain } from '@/features/auth/types/auth.types'
import {
  getAdminUniversityDomains,
  createAdminUniversityDomain,
  updateAdminUniversityDomain,
  deleteAdminUniversityDomain,
  toggleAdminUniversityDomainActive,
} from '@/features/auth/api/auth.api'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppModal from '@/components/ui/AppModal.vue'
import {
  Globe,
  Plus,
  Search,
  RotateCcw,
  Edit2,
  Trash2,
  CheckCircle2,
  XCircle,
  ToggleLeft,
  ToggleRight,
} from 'lucide-vue-next'

const uiStore = useUiStore()
const referencesStore = useReferencesStore()

const domains = ref<UniversityDomain[]>([])
const loading = ref(false)
const searchQuery = ref('')
const selectedStatus = ref<'all' | 'active' | 'inactive'>('all')

const isModalOpen = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const editingId = ref<number | null>(null)
const isSubmitting = ref(false)

const form = reactive({
  domain: '',
  institution_name: '',
  campus_id: null as number | null,
  is_active: true,
  description: '',
})

async function fetchDomains(): Promise<void> {
  loading.value = true
  try {
    const params: any = { all: true }
    if (searchQuery.value) params.search = searchQuery.value
    if (selectedStatus.value !== 'all') params.is_active = selectedStatus.value === 'active'

    const res = await getAdminUniversityDomains(params)
    domains.value = res.data
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (!referencesStore.campusesLoaded) {
    referencesStore.fetchCampuses()
  }
  fetchDomains()
})

function openCreateModal(): void {
  modalMode.value = 'create'
  editingId.value = null
  form.domain = ''
  form.institution_name = ''
  form.campus_id = null
  form.is_active = true
  form.description = ''
  isModalOpen.value = true
}

function openEditModal(d: UniversityDomain): void {
  modalMode.value = 'edit'
  editingId.value = d.id
  form.domain = d.domain
  form.institution_name = d.institution_name
  form.campus_id = d.campus_id ?? null
  form.is_active = d.is_active
  form.description = d.description || ''
  isModalOpen.value = true
}

async function handleSave(): Promise<void> {
  if (!form.domain || !form.institution_name) {
    uiStore.error(t('admin.universityDomains.requiredFields'))
    return
  }

  isSubmitting.value = true
  try {
    if (modalMode.value === 'create') {
      await createAdminUniversityDomain(form)
      uiStore.success(t('admin.universityDomains.registeredSuccess'))
    } else if (editingId.value) {
      await updateAdminUniversityDomain(editingId.value, form)
      uiStore.success(t('admin.universityDomains.updatedSuccess'))
    }
    isModalOpen.value = false
    await fetchDomains()
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  } finally {
    isSubmitting.value = false
  }
}

async function handleToggle(d: UniversityDomain): Promise<void> {
  try {
    await toggleAdminUniversityDomainActive(d.id)
    d.is_active = !d.is_active
    uiStore.success(
      t('admin.universityDomains.toggleSuccess', {
        domain: d.domain,
        status: d.is_active ? t('common.active') : t('common.inactive'),
      })
    )
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  }
}

async function handleDelete(d: UniversityDomain): Promise<void> {
  if (!confirm(t('admin.universityDomains.deleteConfirm', { domain: d.domain }))) return

  try {
    await deleteAdminUniversityDomain(d.id)
    uiStore.success(t('admin.universityDomains.removedSuccess'))
    await fetchDomains()
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <Globe class="w-6 h-6 text-[#0B5D3B] dark:text-[#75bd97]" />
          {{ t('admin.universityDomains.title') }}
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          {{ t('admin.universityDomains.subtitle') }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <AppButton variant="secondary" size="sm" @click="fetchDomains">
          <RotateCcw class="w-3.5 h-3.5 mr-1.5" />
          {{ t('common.refresh') }}
        </AppButton>
        <AppButton variant="primary" size="sm" @click="openCreateModal">
          <Plus class="w-3.5 h-3.5 mr-1.5" />
          {{ t('admin.universityDomains.addDomain') }}
        </AppButton>
      </div>
    </div>

    <!-- Filters & Search -->
    <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row gap-3 items-center justify-between">
      <div class="relative w-full sm:w-80">
        <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="t('admin.universityDomains.searchPlaceholder')"
          class="w-full h-9 pl-9 pr-3 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0B5D3B]"
          @input="fetchDomains"
        />
      </div>

      <div class="flex items-center gap-2 w-full sm:w-auto">
        <label class="text-xs text-slate-500">{{ t('common.status') }}:</label>
        <select
          v-model="selectedStatus"
          class="h-9 px-3 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100"
          @change="fetchDomains"
        >
          <option value="all">{{ t('common.all') }}</option>
          <option value="active">{{ t('common.active') }}</option>
          <option value="inactive">{{ t('common.inactive') }}</option>
        </select>
      </div>
    </div>

    <!-- Domains Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
      <div v-if="loading" class="p-8 text-center text-xs text-slate-500">
        {{ t('common.loading') }}
      </div>
      <div v-else-if="domains.length === 0" class="p-8 text-center text-xs text-slate-500">
        {{ t('admin.universityDomains.noDomains') }}
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-semibold">
            <tr>
              <th class="py-3 px-4">{{ t('admin.universityDomains.domain') }}</th>
              <th class="py-3 px-4">{{ t('admin.universityDomains.institutionName') }}</th>
              <th class="py-3 px-4">{{ t('admin.universityDomains.campus') }}</th>
              <th class="py-3 px-4">{{ t('common.status') }}</th>
              <th class="py-3 px-4">{{ t('admin.universityDomains.description') }}</th>
              <th class="py-3 px-4 text-right">{{ t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-for="d in domains" :key="d.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
              <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
                @{{ d.domain }}
              </td>
              <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-200">
                {{ d.institution_name }}
              </td>
              <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                {{ d.campus?.name || t('admin.universityDomains.allCampuses') }}
              </td>
              <td class="py-3 px-4">
                <span
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold"
                  :class="d.is_active
                    ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800'
                    : 'bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700'"
                >
                  <CheckCircle2 v-if="d.is_active" class="w-3 h-3" />
                  <XCircle v-else class="w-3 h-3" />
                  {{ d.is_active ? t('common.active') : t('common.inactive') }}
                </span>
              </td>
              <td class="py-3 px-4 text-slate-500 dark:text-slate-400 max-w-xs truncate">
                {{ d.description || '—' }}
              </td>
              <td class="py-3 px-4 text-right space-x-1">
                <button
                  type="button"
                  class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
                  :title="d.is_active ? t('common.deactivate') : t('common.activate')"
                  @click="handleToggle(d)"
                >
                  <ToggleRight v-if="d.is_active" class="w-4 h-4 text-emerald-600" />
                  <ToggleLeft v-else class="w-4 h-4 text-slate-400" />
                </button>
                <button
                  type="button"
                  class="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
                  :title="t('common.edit')"
                  @click="openEditModal(d)"
                >
                  <Edit2 class="w-3.5 h-3.5" />
                </button>
                <button
                  type="button"
                  class="p-1.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
                  :title="t('common.delete')"
                  @click="handleDelete(d)"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <AppModal
      v-model:is-open="isModalOpen"
      :title="modalMode === 'create' ? t('admin.universityDomains.createDomain') : t('admin.universityDomains.editDomain')"
    >
      <form class="space-y-4" @submit.prevent="handleSave">
        <AppInput
          id="modal-domain"
          :label="t('admin.universityDomains.domainLabel')"
          :placeholder="t('admin.universityDomains.placeholders.domain')"
          :model-value="form.domain"
          required
          @update:model-value="form.domain = $event"
        />

        <AppInput
          id="modal-institution"
          :label="t('admin.universityDomains.institutionName')"
          :placeholder="t('admin.universityDomains.placeholders.institution')"
          :model-value="form.institution_name"
          required
          @update:model-value="form.institution_name = $event"
        />

        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
            {{ t('admin.universityDomains.associatedCampus') }}
          </label>
          <select
            :value="form.campus_id || ''"
            class="w-full h-10 px-3 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200"
            @change="form.campus_id = ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null"
          >
            <option value="">{{ t('admin.universityDomains.allCampuses') }}</option>
            <option v-for="c in referencesStore.campuses" :key="c.id" :value="c.id">
              {{ c.name }} ({{ c.short_code }})
            </option>
          </select>
        </div>

        <AppInput
          id="modal-desc"
          :label="t('admin.universityDomains.descriptionOptional')"
          :placeholder="t('admin.universityDomains.placeholders.description')"
          :model-value="form.description"
          @update:model-value="form.description = $event"
        />

        <div class="flex items-center gap-2 pt-1">
          <input
            id="modal-active"
            v-model="form.is_active"
            type="checkbox"
            class="rounded border-slate-300 text-[#0B5D3B] focus:ring-[#0B5D3B]"
          />
          <label for="modal-active" class="text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
            {{ t('admin.universityDomains.activeForRegistration') }}
          </label>
        </div>

        <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <AppButton type="button" variant="secondary" size="sm" @click="isModalOpen = false">
            {{ t('common.cancel') }}
          </AppButton>
          <AppButton type="submit" variant="primary" size="sm" :loading="isSubmitting">
            {{ t('common.save') }}
          </AppButton>
        </div>
      </form>
    </AppModal>
  </div>
</template>
