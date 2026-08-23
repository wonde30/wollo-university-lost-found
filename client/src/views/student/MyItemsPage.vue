<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { storeToRefs } from 'pinia'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useItemsStore } from '@/features/items/stores/items.store'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import { formatDate } from '@/utils/date'
import type { Item } from '@/features/items/types/item.types'
import AppTabs from '@/components/ui/AppTabs.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppEmptyState from '@/components/ui/AppEmptyState.vue'

const itemsStore = useItemsStore()
const { items, loading, pagination } = storeToRefs(itemsStore)
const uiStore = useUiStore()

const activeTab = ref<'all' | 'lost' | 'found'>('all')

const tabs = [
  { id: 'all', label: 'All My Items' },
  { id: 'lost', label: 'Reported Lost' },
  { id: 'found', label: 'Reported Found' },
]

const filteredItems = computed(() => {
  if (activeTab.value === 'all') return items.value
  return items.value.filter(i => i.type === activeTab.value)
})

const editModalOpen = ref(false)
const editingItem = ref<Item | null>(null)
const editForm = ref({ title: '', description: '' })
const editErrors = ref<Record<string, string>>({})
const saving = ref(false)

async function load(page = 1) {
  await itemsStore.fetchItems({ page })
}

function handleTabChange(tabId: string | number) {
  activeTab.value = tabId as any
}

function handleEdit(item: Item) {
  editingItem.value = item
  editForm.value = { title: item.title, description: item.description }
  editErrors.value = {}
  editModalOpen.value = true
}

async function handleSaveEdit() {
  if (!editingItem.value) return

  editErrors.value = {}
  if (!editForm.value.title.trim()) {
    editErrors.value.title = 'Title is required.'
  } else if (editForm.value.title.trim().length < 5) {
    editErrors.value.title = 'Title must be at least 5 characters.'
  }
  if (!editForm.value.description.trim()) {
    editErrors.value.description = 'Description is required.'
  } else if (editForm.value.description.trim().length < 15) {
    editErrors.value.description = 'Description must be at least 15 characters.'
  }
  if (Object.keys(editErrors.value).length > 0) return

  saving.value = true
  try {
    await itemsStore.updateItem(editingItem.value.id, {
      title: editForm.value.title.trim(),
      description: editForm.value.description.trim(),
    })
    uiStore.success('Item updated successfully')
    editModalOpen.value = false
    editingItem.value = null
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to update item'))
  } finally {
    saving.value = false
  }
}

function handleDelete(item: Item) {
  uiStore.confirm({
    title: 'Delete Item Report',
    message: `Are you sure you want to delete "${item.title}"? This action cannot be undone.`,
    confirmText: 'Delete Report',
    variant: 'danger',
    onConfirm: async () => {
      try {
        await itemsStore.deleteItem(item.id)
        uiStore.success('Item deleted successfully')
      } catch (err) {
        uiStore.error(getErrorMessage(err, 'Failed to delete item'))
      }
    },
  })
}

function getItemBadgeVariant(status: string): any {
  switch (status) {
    case 'returned': return 'success'
    case 'found_claimed': return 'purple'
    case 'found_unclaimed': return 'primary'
    case 'lost': return 'warning'
    case 'disposed': case 'expired': return 'danger'
    default: return 'default'
  }
}

onMounted(() => load())
</script>

<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-black text-slate-900">My Reported Items</h1>
          <p class="text-xs text-slate-500 mt-1">Manage and track your filed lost and found items.</p>
        </div>

        <div class="flex items-center gap-2">
          <RouterLink to="/student/report-lost">
            <AppButton variant="outline" size="sm">+ Report Lost</AppButton>
          </RouterLink>
          <RouterLink to="/student/report-found">
            <AppButton variant="primary" size="sm">+ Report Found</AppButton>
          </RouterLink>
        </div>
      </div>

      <!-- Filter Tabs -->
      <AppTabs
        :tabs="tabs"
        :model-value="activeTab"
        variant="pills"
        @change="handleTabChange"
      />

      <!-- Table / Cards List -->
      <div v-if="filteredItems.length === 0 && !loading" class="pt-4">
        <AppEmptyState
          title="No reported items"
          description="You have not submitted any items under this category yet."
        >
          <RouterLink to="/student/report-lost">
            <AppButton variant="primary" size="sm">Report an Item Now</AppButton>
          </RouterLink>
        </AppEmptyState>
      </div>

      <div v-else class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-600">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
              <tr>
                <th class="px-5 py-3.5">Item & Reference</th>
                <th class="px-5 py-3.5">Category</th>
                <th class="px-5 py-3.5">Type</th>
                <th class="px-5 py-3.5">Date</th>
                <th class="px-5 py-3.5">Status</th>
                <th class="px-5 py-3.5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="item in filteredItems" :key="item.id" class="hover:bg-slate-50/75 transition-colors">
                <td class="px-5 py-4">
                  <div class="font-bold text-slate-900 text-sm truncate max-w-xs">{{ item.title }}</div>
                  <span class="text-[11px] font-mono text-slate-400">#{{ item.reference_code }}</span>
                </td>
                <td class="px-5 py-4 font-medium text-slate-700">
                  {{ item.category?.name || 'General' }}
                </td>
                <td class="px-5 py-4">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider',
                      item.type === 'found' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800',
                    ]"
                  >
                    {{ item.type }}
                  </span>
                </td>
                <td class="px-5 py-4 text-slate-500">
                  {{ formatDate(item.incident_date) }}
                </td>
                <td class="px-5 py-4">
                  <AppBadge :variant="getItemBadgeVariant(item.status)" size="sm">
                    {{ item.status.replace('_', ' ') }}
                  </AppBadge>
                </td>
                <td class="px-5 py-4 text-right space-x-2">
                  <RouterLink :to="`/items/${item.id}`">
                    <button type="button" class="text-xs text-[#0F5132] font-semibold hover:underline">
                      View
                    </button>
                  </RouterLink>
                  <button
                    type="button"
                    class="text-xs text-slate-600 font-semibold hover:text-slate-900"
                    @click="handleEdit(item)"
                  >
                    Edit
                  </button>
                  <button
                    type="button"
                    class="text-xs text-rose-600 font-semibold hover:underline"
                    @click="handleDelete(item)"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <AppPagination
          v-if="pagination && pagination.last_page > 1"
          :current-page="pagination.current_page"
          :last-page="pagination.last_page"
          :total="pagination.total"
          :per-page="pagination.per_page"
          @change="load"
        />
      </div>

      <!-- Edit Modal -->
      <AppModal
        :open="editModalOpen"
        title="Edit Reported Item"
        @close="editModalOpen = false"
      >
        <div class="space-y-4">
          <AppInput
            label="Title *"
            :model-value="editForm.title"
            :error="editErrors.title"
            required
            @update:model-value="editForm.title = $event"
          />
          <AppTextarea
            label="Description *"
            :rows="4"
            :model-value="editForm.description"
            :error="editErrors.description"
            required
            @update:model-value="editForm.description = $event"
          />
        </div>

        <template #footer>
          <div class="flex items-center gap-2">
            <AppButton variant="outline" size="sm" @click="editModalOpen = false">Cancel</AppButton>
            <AppButton variant="primary" size="sm" :loading="saving" @click="handleSaveEdit">Save Changes</AppButton>
          </div>
        </template>
      </AppModal>
    </div>
  </DashboardLayout>
</template>
