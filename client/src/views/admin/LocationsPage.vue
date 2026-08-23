<script setup lang="ts">
import { onMounted, ref, reactive, computed } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useReferencesStore } from '@/features/lookups/stores/references.store'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import type { Location } from '@/types/common.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppButton from '@/components/ui/AppButton.vue'

const uiStore = useUiStore()
const referencesStore = useReferencesStore()

const locations = computed(() => referencesStore.locations)
const campuses = computed(() => referencesStore.campuses)
const loading = computed(() => referencesStore.loading && locations.value.length === 0)

const creating = ref(false)
const editingId = ref<number | null>(null)
const editingSaving = ref(false)

const form = reactive({
  campus_id: '' as number | '',
  name: '',
  code: '',
  building: '',
  floor: '',
})

const editForm = reactive({
  campus_id: '' as number | '',
  name: '',
  code: '',
  building: '',
  floor: '',
})

onMounted(async () => {
  await Promise.all([
    referencesStore.fetchCampuses(),
    referencesStore.fetchLocations(),
  ])
  if (campuses.value.length > 0 && !form.campus_id) {
    form.campus_id = campuses.value[0].id
  }
})

async function handleCreate() {
  if (!form.name.trim() || !form.code.trim() || !form.campus_id) {
    uiStore.error('Location name, code, and Campus are required.')
    return
  }
  creating.value = true
  try {
    await referencesStore.createLocation({
      campus_id: Number(form.campus_id),
      name: form.name.trim(),
      code: form.code.trim(),
      building: form.building.trim() || null,
      floor: form.floor.trim() || null,
    })
    uiStore.success('Location created successfully.')
    form.name = ''
    form.code = ''
    form.building = ''
    form.floor = ''
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to create location.'))
  } finally {
    creating.value = false
  }
}

function startEdit(location: Location) {
  editingId.value = location.id
  editForm.campus_id = location.campus_id
  editForm.name = location.name
  editForm.code = location.code || ''
  editForm.building = location.building || ''
  editForm.floor = location.floor || ''
}

function cancelEdit() {
  editingId.value = null
  editForm.campus_id = ''
  editForm.name = ''
  editForm.code = ''
  editForm.building = ''
  editForm.floor = ''
}

async function handleUpdate() {
  if (!editingId.value || !editForm.name.trim() || !editForm.code.trim() || !editForm.campus_id) {
    uiStore.error('Location name, code, and Campus are required.')
    return
  }
  
  editingSaving.value = true
  try {
    await referencesStore.updateLocation(editingId.value, {
      campus_id: Number(editForm.campus_id),
      name: editForm.name.trim(),
      code: editForm.code.trim(),
      building: editForm.building.trim() || null,
      floor: editForm.floor.trim() || null,
    })
    
    uiStore.success('Location updated successfully.')
    cancelEdit()
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to update location.'))
  } finally {
    editingSaving.value = false
  }
}

async function handleDelete(id: number) {
  uiStore.confirm({
    title: 'Delete Location',
    message: 'Are you sure you want to delete this location?',
    confirmText: 'Delete',
    variant: 'danger',
    onConfirm: async () => {
      try {
        await referencesStore.deleteLocation(id)
        uiStore.success('Location deleted.')
      } catch (err) {
        uiStore.error(getErrorMessage(err, 'Failed to delete location.'))
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
          <h1 class="text-xl font-black text-slate-900">Manage Locations</h1>
          <p class="text-xs text-slate-500 mt-0.5">Configure campus facilities, buildings, and specific drop points.</p>
        </div>
      </div>

      <!-- Add New Location Form -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
        <h2 class="text-sm font-bold text-slate-700">Add New Location</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
          <AppSelect
            label="Campus *"
            :options="campuses.map(c => ({ label: `${c.name} (${c.code})`, value: c.id }))"
            :model-value="form.campus_id"
            required
            @update:model-value="form.campus_id = $event ? Number($event) : ''"
          />
          <AppInput id="loc-name" label="Location Name *" placeholder="Library – 2nd Floor" :model-value="form.name" required @update:model-value="form.name = $event" />
          <AppInput id="loc-code" label="Code *" placeholder="DSS-LIB-2F" :model-value="form.code" required @update:model-value="form.code = $event" />
          <AppInput id="loc-building" label="Building" placeholder="Main Block" :model-value="form.building" @update:model-value="form.building = $event" />
          <AppInput id="loc-floor" label="Floor / Room" placeholder="2F, Room 201" :model-value="form.floor" @update:model-value="form.floor = $event" />
        </div>
        <AppButton variant="primary" size="sm" :loading="creating" @click="handleCreate">
          Create Location
        </AppButton>
      </div>

      <!-- Table View -->
      <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-xs">
        <table class="w-full text-xs">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50">
              <th class="px-4 py-3 text-left font-semibold text-slate-600">Name</th>
              <th class="px-4 py-3 text-left font-semibold text-slate-600">Code</th>
              <th class="px-4 py-3 text-left font-semibold text-slate-600">Campus</th>
              <th class="px-4 py-3 text-left font-semibold text-slate-600">Building / Floor</th>
              <th class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
            </tr>
          </thead>
          <tbody v-if="loading && locations.length === 0">
            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Loading locations...</td></tr>
          </tbody>
          <tbody v-else-if="locations.length === 0">
            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No locations found. Add your first location above.</td></tr>
          </tbody>
          <tbody v-else class="divide-y divide-slate-100">
            <tr v-for="loc in locations" :key="loc.id" class="hover:bg-slate-50/50">
              <template v-if="editingId === loc.id">
                <!-- Edit Mode Row -->
                <td class="px-4 py-3">
                  <AppInput
                    id="edit-name"
                    placeholder="Location name"
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
                  <select
                    v-model="editForm.campus_id"
                    class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#0F5132]/30"
                  >
                    <option v-for="c in campuses" :key="c.id" :value="c.id">
                      {{ c.name }} ({{ c.code }})
                    </option>
                  </select>
                </td>
                <td class="px-4 py-3">
                  <div class="flex gap-1">
                    <AppInput
                      id="edit-building"
                      placeholder="Building"
                      :model-value="editForm.building"
                      class="text-xs"
                      @update:model-value="editForm.building = $event"
                    />
                    <AppInput
                      id="edit-floor"
                      placeholder="Floor"
                      :model-value="editForm.floor"
                      class="text-xs"
                      @update:model-value="editForm.floor = $event"
                    />
                  </div>
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
                <td class="px-4 py-3 font-semibold text-slate-900">{{ loc.name }}</td>
                <td class="px-4 py-3 text-slate-600 font-mono text-[11px]">{{ loc.code }}</td>
                <td class="px-4 py-3 text-slate-600">{{ loc.campus?.name || 'Dessie Campus' }}</td>
                <td class="px-4 py-3 text-slate-600">
                  {{ [loc.building, loc.floor].filter(Boolean).join(' · ') || '—' }}
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="flex gap-2 justify-end">
                    <AppButton variant="ghost" size="xs" @click="startEdit(loc)">Edit</AppButton>
                    <AppButton variant="danger" size="xs" @click="handleDelete(loc.id)">Delete</AppButton>
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
