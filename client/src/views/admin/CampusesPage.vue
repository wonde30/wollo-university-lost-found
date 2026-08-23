<script setup lang="ts">
import { onMounted, ref, reactive, computed } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useReferencesStore } from '@/features/lookups/stores/references.store'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import type { Campus } from '@/types/common.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

const uiStore = useUiStore()
const referencesStore = useReferencesStore()

const campuses = computed(() => referencesStore.campuses)
const loading = computed(() => referencesStore.loading && campuses.value.length === 0)

const creating = ref(false)
const editingId = ref<number | null>(null)
const editingSaving = ref(false)

const form = reactive({ name: '', code: '', address: '', description: '' })
const editForm = reactive({ name: '', code: '', address: '', description: '' })

onMounted(async () => {
  await referencesStore.fetchCampuses()
})

async function handleCreate() {
  if (!form.name.trim() || !form.code.trim()) {
    uiStore.error('Campus name and code are required.')
    return
  }
  creating.value = true
  try {
    await referencesStore.createCampus({
      name: form.name.trim(),
      code: form.code.trim().toUpperCase(),
      address: form.address.trim() || null,
      description: form.description.trim() || null,
    })
    uiStore.success('Campus created successfully.')
    form.name = ''
    form.code = ''
    form.address = ''
    form.description = ''
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to create campus.'))
  } finally {
    creating.value = false
  }
}

function startEdit(campus: Campus) {
  editingId.value = campus.id
  editForm.name = campus.name
  editForm.code = campus.code
  editForm.address = campus.address || ''
  editForm.description = campus.description || ''
}

function cancelEdit() {
  editingId.value = null
  editForm.name = ''
  editForm.code = ''
  editForm.address = ''
  editForm.description = ''
}

async function handleUpdate() {
  if (!editingId.value || !editForm.name.trim() || !editForm.code.trim()) {
    uiStore.error('Campus name and code are required.')
    return
  }
  
  editingSaving.value = true
  try {
    await referencesStore.updateCampus(editingId.value, {
      name: editForm.name.trim(),
      code: editForm.code.trim().toUpperCase(),
      address: editForm.address.trim() || null,
      description: editForm.description.trim() || null,
    })
    
    uiStore.success('Campus updated successfully.')
    cancelEdit()
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to update campus.'))
  } finally {
    editingSaving.value = false
  }
}

async function handleToggleActive(campus: Campus) {
  const action = campus.is_active ? 'deactivate' : 'activate'
  const actionTitle = campus.is_active ? 'Deactivate' : 'Activate'
  
  uiStore.confirm({
    title: `${actionTitle} Campus`,
    message: `Are you sure you want to ${action} "${campus.name}"?`,
    confirmText: actionTitle,
    variant: campus.is_active ? 'danger' : 'primary',
    onConfirm: async () => {
      try {
        if (campus.is_active) {
          await referencesStore.deleteCampus(campus.id)
          uiStore.success('Campus deactivated successfully.')
        } else {
          await referencesStore.restoreCampus(campus.id)
          uiStore.success('Campus activated successfully.')
        }
      } catch (err) {
        uiStore.error(getErrorMessage(err, `Failed to ${action} campus.`))
      }
    },
  })
}
</script>

<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-black text-slate-900">Manage Campuses</h1>
          <p class="text-xs text-slate-500 mt-0.5">Configure institutional campuses across Dessie and Kombolcha.</p>
        </div>
      </div>

      <!-- Create Form -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
        <h2 class="text-sm font-bold text-slate-700">Add New Campus</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <AppInput id="campus-name" label="Campus Name *" placeholder="Dessie Main Campus" :model-value="form.name" required @update:model-value="form.name = $event" />
          <AppInput id="campus-code" label="Code *" placeholder="DMC" :model-value="form.code" required @update:model-value="form.code = $event" />
          <AppInput id="campus-address" label="Address" placeholder="Dessie, Ethiopia" :model-value="form.address" @update:model-value="form.address = $event" />
          <AppInput id="campus-desc" label="Description" placeholder="Main academic campus" :model-value="form.description" @update:model-value="form.description = $event" />
        </div>
        <AppButton variant="primary" size="sm" :loading="creating" @click="handleCreate">
          Create Campus
        </AppButton>
      </div>

      <!-- List -->
      <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-xs">
        <table class="w-full text-xs">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50">
              <th class="px-4 py-3 text-left font-semibold text-slate-600">Name</th>
              <th class="px-4 py-3 text-left font-semibold text-slate-600">Code</th>
              <th class="px-4 py-3 text-left font-semibold text-slate-600">Address / Location</th>
              <th class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
            </tr>
          </thead>
          <tbody v-if="loading && campuses.length === 0">
            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Loading campuses...</td></tr>
          </tbody>
          <tbody v-else-if="campuses.length === 0">
            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">No campuses found. Add your first campus above.</td></tr>
          </tbody>
          <tbody v-else class="divide-y divide-slate-100">
            <tr v-for="c in campuses" :key="c.id" class="hover:bg-slate-50/50">
              <template v-if="editingId === c.id">
                <!-- Edit Mode Row -->
                <td class="px-4 py-3">
                  <AppInput
                    id="edit-name"
                    placeholder="Campus name"
                    :model-value="editForm.name"
                    required
                    class="text-xs"
                    @update:model-value="editForm.name = $event"
                  />
                </td>
                <td class="px-4 py-3">
                  <AppInput
                    id="edit-code"
                    placeholder="Code"
                    :model-value="editForm.code"
                    required
                    class="text-xs"
                    @update:model-value="editForm.code = $event"
                  />
                </td>
                <td class="px-4 py-3">
                  <AppInput
                    id="edit-address"
                    placeholder="Address/Description"
                    :model-value="editForm.address || editForm.description"
                    class="text-xs"
                    @update:model-value="editForm.address = $event"
                  />
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="flex gap-2 justify-end">
                    <AppButton variant="primary" size="xs" :loading="editingSaving" @click="handleUpdate">
                      Save
                    </AppButton>
                    <AppButton variant="ghost" size="xs" @click="cancelEdit">
                      Cancel
                    </AppButton>
                  </div>
                </td>
              </template>
              
              <template v-else>
                <!-- View Mode Row -->
                <td class="px-4 py-3 font-semibold text-slate-900">
                  {{ c.name }}
                  <span v-if="!c.is_active" class="ml-2 text-[10px] px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 font-semibold">INACTIVE</span>
                </td>
                <td class="px-4 py-3 font-mono text-slate-600">{{ c.code }}</td>
                <td class="px-4 py-3 text-slate-600">{{ c.address || c.description || '—' }}</td>
                <td class="px-4 py-3 text-right">
                  <div class="flex gap-2 justify-end">
                    <AppButton variant="ghost" size="xs" @click="startEdit(c)">Edit</AppButton>
                    <AppButton 
                      :variant="c.is_active ? 'danger' : 'primary'" 
                      size="xs" 
                      @click="handleToggleActive(c)"
                    >
                      {{ c.is_active ? 'Deactivate' : 'Activate' }}
                    </AppButton>
                  </div>
                </td>
              </template>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </DashboardLayout>
</template>
