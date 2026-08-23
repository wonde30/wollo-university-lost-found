<script setup lang="ts">
import { onMounted, ref, reactive, computed } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useReferencesStore } from '@/features/lookups/stores/references.store'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import type { Category } from '@/types/common.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

const uiStore = useUiStore()
const referencesStore = useReferencesStore()

const categories = computed(() => referencesStore.categories)
const loading = computed(() => referencesStore.loading && categories.value.length === 0)

const creating = ref(false)
const editingId = ref<number | null>(null)
const editingSaving = ref(false)

const form = reactive({ name: '', icon: '' })
const editForm = reactive({ name: '', icon: '' })

onMounted(async () => {
  await referencesStore.fetchCategories()
})

async function handleCreate() {
  if (!form.name.trim()) {
    uiStore.error('Category name is required.')
    return
  }
  creating.value = true
  try {
    await referencesStore.createCategory({ 
      name: form.name.trim(), 
      icon: form.icon.trim() || null 
    })
    uiStore.success('Category created successfully.')
    form.name = ''
    form.icon = ''
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to create category.'))
  } finally {
    creating.value = false
  }
}

function startEdit(category: Category) {
  editingId.value = category.id
  editForm.name = category.name
  editForm.icon = category.icon || ''
}

function cancelEdit() {
  editingId.value = null
  editForm.name = ''
  editForm.icon = ''
}

async function handleUpdate() {
  if (!editingId.value || !editForm.name.trim()) {
    uiStore.error('Category name is required.')
    return
  }
  
  editingSaving.value = true
  try {
    await referencesStore.updateCategory(editingId.value, {
      name: editForm.name.trim(),
      icon: editForm.icon.trim() || null,
    })
    
    uiStore.success('Category updated successfully.')
    cancelEdit()
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to update category.'))
  } finally {
    editingSaving.value = false
  }
}

async function handleDelete(id: number) {
  uiStore.confirm({
    title: 'Delete Category',
    message: 'Are you sure you want to delete this category?',
    confirmText: 'Delete',
    variant: 'danger',
    onConfirm: async () => {
      try {
        await referencesStore.deleteCategory(id)
        uiStore.success('Category deleted.')
      } catch (err) {
        uiStore.error(getErrorMessage(err, 'Failed to delete category.'))
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
          <h1 class="text-xl font-black text-slate-900">Manage Item Categories</h1>
          <p class="text-xs text-slate-500 mt-0.5">Organize property classifications, emojis, and labels.</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
        <h2 class="text-sm font-bold text-slate-700">Add New Category</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <AppInput id="cat-name" label="Category Name *" placeholder="e.g. Electronics, Documents, Keys" :model-value="form.name" required @update:model-value="form.name = $event" />
          <AppInput id="cat-icon" label="Icon (Emoji)" placeholder="💻" :model-value="form.icon" @update:model-value="form.icon = $event" />
        </div>
        <AppButton variant="primary" size="sm" :loading="creating" @click="handleCreate">
          Create Category
        </AppButton>
      </div>

      <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-xs">
        <table class="w-full text-xs">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50">
              <th class="px-4 py-3 text-left font-semibold text-slate-600">Icon</th>
              <th class="px-4 py-3 text-left font-semibold text-slate-600">Category Name</th>
              <th class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
            </tr>
          </thead>
          <tbody v-if="loading && categories.length === 0">
            <tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">Loading categories...</td></tr>
          </tbody>
          <tbody v-else-if="categories.length === 0">
            <tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">No categories found. Add your first category above.</td></tr>
          </tbody>
          <tbody v-else class="divide-y divide-slate-100">
            <tr v-for="cat in categories" :key="cat.id" class="hover:bg-slate-50/50">
              <template v-if="editingId === cat.id">
                <!-- Edit Mode Row -->
                <td class="px-4 py-3">
                  <AppInput
                    id="edit-icon"
                    placeholder="💻"
                    :model-value="editForm.icon"
                    class="text-xs"
                    @update:model-value="editForm.icon = $event"
                  />
                </td>
                <td class="px-4 py-3">
                  <AppInput
                    id="edit-name"
                    placeholder="Category name"
                    :model-value="editForm.name"
                    required
                    class="text-xs"
                    @update:model-value="editForm.name = $event"
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
                <td class="px-4 py-3 text-xl">{{ cat.icon || '📁' }}</td>
                <td class="px-4 py-3 font-semibold text-slate-900">{{ cat.name }}</td>
                <td class="px-4 py-3 text-right">
                  <div class="flex gap-2 justify-end">
                    <AppButton variant="ghost" size="xs" @click="startEdit(cat)">Edit</AppButton>
                    <AppButton variant="danger" size="xs" @click="handleDelete(cat.id)">Delete</AppButton>
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
