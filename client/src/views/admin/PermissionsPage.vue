<script setup lang="ts">
import { ref, computed } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { usePermissionsStore } from '@/permissions/stores/permissions.store'
import { useUiStore } from '@/stores/ui.store'
import type { UserRole } from '@/constants'
import type { PermissionDefinition } from '@/permissions'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

const permissionsStore = usePermissionsStore()
const uiStore = useUiStore()

const searchQuery = ref('')
const selectedCategory = ref<'all' | 'items' | 'claims' | 'custody' | 'admin'>('all')

const categories = [
  { id: 'all', label: 'All Capabilities' },
  { id: 'items', label: 'Items & Reports' },
  { id: 'claims', label: 'Claims & Decisions' },
  { id: 'custody', label: 'Custody & Handover' },
  { id: 'admin', label: 'System Administration' },
]

const filteredDefinitions = computed(() => {
  let list = permissionsStore.allDefinitions
  if (selectedCategory.value !== 'all') {
    list = list.filter(d => d.category === selectedCategory.value)
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(d => 
      d.label.toLowerCase().includes(q) || 
      d.key.toLowerCase().includes(q) || 
      d.description.toLowerCase().includes(q)
    )
  }
  return list
})

const totalPermissionsCount = computed(() => permissionsStore.allDefinitions.length)

function isAllowed(role: UserRole, permissionKey: string): boolean {
  return permissionsStore.isPermissionAllowed(role, permissionKey)
}

function handleToggle(role: UserRole, def: PermissionDefinition) {
  const newState = permissionsStore.togglePermission(role, def.key)
  uiStore.success(
    `${def.label} ${newState ? 'granted to' : 'revoked from'} ${role.toUpperCase()}.`
  )
}

function handleAllowAll(def: PermissionDefinition) {
  const roles: UserRole[] = ['student', 'staff', 'admin']
  roles.forEach(r => permissionsStore.setPermission(r, def.key, true))
  uiStore.success(`Granted ${def.label} to all roles.`)
}

function handleResetDefaults() {
  uiStore.confirm({
    title: 'Reset Permissions Matrix',
    message: 'Are you sure you want to reset all role permissions to system default settings?',
    confirmText: 'Reset to Defaults',
    variant: 'warning',
    onConfirm: () => {
      permissionsStore.resetToDefaults()
      uiStore.success('Permissions matrix restored to default configuration.')
    },
  })
}

function handleExport() {
  const jsonStr = JSON.stringify(permissionsStore.matrix, null, 2)
  const blob = new Blob([jsonStr], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `wu_permissions_matrix_${new Date().toISOString().slice(0, 10)}.json`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
  uiStore.success('Permissions matrix configuration exported.')
}

// Interactive Live Simulator
const simRole = ref<UserRole>('student')
const simQuery = ref('')
const simResults = computed(() => {
  let list = permissionsStore.allDefinitions
  if (simQuery.value.trim()) {
    const q = simQuery.value.toLowerCase().trim()
    list = list.filter(d => d.label.toLowerCase().includes(q) || d.key.toLowerCase().includes(q))
  }
  return list.map(d => ({
    ...d,
    allowed: isAllowed(simRole.value, d.key),
  }))
})
</script>

<template>
  <DashboardLayout>
    <div class="space-y-8">
      <!-- Header Banner -->
      <div class="rounded-3xl bg-gradient-to-br from-[#0F5132] via-[#0B3822] to-[#04140B] p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-[#D4AF37]/15 rounded-full blur-2xl pointer-events-none" />

        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-bold bg-[#D4AF37]/20 text-[#D4AF37] border border-[#D4AF37]/30 mb-2">
              Security & RBAC Management
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white">
              Dynamic Role Permissions Matrix
            </h1>
            <p class="text-xs sm:text-sm text-slate-200/90 mt-1 max-w-xl leading-relaxed">
              Granular access control governing capabilities for Students, Staff, and Administrators in real-time.
            </p>
          </div>

          <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <AppButton variant="outline" size="sm" class="bg-white/10 text-white border-white/20 hover:bg-white/20" @click="handleExport">
              Export Matrix JSON
            </AppButton>
            <AppButton variant="gold" size="sm" @click="handleResetDefaults">
              Reset to Defaults
            </AppButton>
          </div>
        </div>
      </div>

      <!-- Top Summary Metrics -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Total Capabilities</span>
            <p class="text-2xl font-black text-slate-900">{{ totalPermissionsCount }}</p>
            <span class="text-[11px] text-emerald-600 font-semibold mt-1 inline-block">Active in RBAC Engine</span>
          </div>
          <div class="h-12 w-12 rounded-2xl bg-[#0F5132]/10 text-[#0F5132] flex items-center justify-center text-xl font-bold">
            🛡️
          </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Student Role Access</span>
            <p class="text-2xl font-black text-slate-900">
              {{ permissionsStore.allDefinitions.filter(d => isAllowed('student', d.key)).length }}
            </p>
            <span class="text-[11px] text-slate-500 font-semibold mt-1 inline-block">Capabilities Enabled</span>
          </div>
          <div class="h-12 w-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl font-bold">
            🎓
          </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Staff Role Access</span>
            <p class="text-2xl font-black text-slate-900">
              {{ permissionsStore.allDefinitions.filter(d => isAllowed('staff', d.key)).length }}
            </p>
            <span class="text-[11px] text-slate-500 font-semibold mt-1 inline-block">Capabilities Enabled</span>
          </div>
          <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
            👔
          </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Administrator Access</span>
            <p class="text-2xl font-black text-slate-900">
              {{ permissionsStore.allDefinitions.filter(d => isAllowed('admin', d.key)).length }}
            </p>
            <span class="text-[11px] text-[#0F5132] font-semibold mt-1 inline-block">Full System Oversight</span>
          </div>
          <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
            ⚡
          </div>
        </div>
      </div>

      <!-- Filters & Category Navigation -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-1.5 flex-wrap">
          <button
            v-for="cat in categories"
            :key="cat.id"
            type="button"
            :class="[
              'px-3 py-1.5 rounded-xl text-xs font-bold transition-all',
              selectedCategory === cat.id
                ? 'bg-[#0F5132] text-white shadow-xs'
                : 'text-slate-600 hover:bg-slate-100',
            ]"
            @click="selectedCategory = cat.id as any"
          >
            {{ cat.label }}
          </button>
        </div>

        <div class="w-full sm:w-72">
          <AppInput
            id="perm-search"
            placeholder="Search capabilities or keys..."
            :model-value="searchQuery"
            class="text-xs"
            @update:model-value="searchQuery = $event"
          />
        </div>
      </div>

      <!-- Matrix Table -->
      <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-xs">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50/80">
              <th class="px-5 py-3.5 font-bold text-slate-700 w-1/2">Capability & Description</th>
              <th class="px-4 py-3.5 font-bold text-slate-700 text-center w-28">
                <span class="inline-flex items-center gap-1.5">
                  <span class="h-2 w-2 rounded-full bg-sky-500" /> Student
                </span>
              </th>
              <th class="px-4 py-3.5 font-bold text-slate-700 text-center w-28">
                <span class="inline-flex items-center gap-1.5">
                  <span class="h-2 w-2 rounded-full bg-amber-500" /> Staff
                </span>
              </th>
              <th class="px-4 py-3.5 font-bold text-slate-700 text-center w-28">
                <span class="inline-flex items-center gap-1.5">
                  <span class="h-2 w-2 rounded-full bg-[#0F5132]" /> Admin
                </span>
              </th>
              <th class="px-5 py-3.5 font-bold text-slate-700 text-right w-28">Quick Action</th>
            </tr>
          </thead>
          <tbody v-if="filteredDefinitions.length === 0">
            <tr>
              <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                No capabilities matching your search criteria.
              </td>
            </tr>
          </tbody>
          <tbody v-else class="divide-y divide-slate-100">
            <tr v-for="def in filteredDefinitions" :key="def.key" class="hover:bg-slate-50/60 transition-colors">
              <!-- Label, description, key -->
              <td class="px-5 py-4">
                <div class="flex items-start gap-3">
                  <div class="h-7 w-7 rounded-lg bg-slate-100 flex items-center justify-center shrink-0 mt-0.5 text-xs font-mono font-bold text-slate-500">
                    {{ def.category.slice(0, 1).toUpperCase() }}
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <span class="font-bold text-slate-900">{{ def.label }}</span>
                      <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-slate-100 text-slate-500 font-semibold border border-slate-200">
                        {{ def.key }}
                      </span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">{{ def.description }}</p>
                  </div>
                </div>
              </td>

              <!-- Student Toggle -->
              <td class="px-4 py-4 text-center">
                <button
                  type="button"
                  :class="[
                    'relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none',
                    isAllowed('student', def.key) ? 'bg-[#0F5132]' : 'bg-slate-200',
                  ]"
                  @click="handleToggle('student', def)"
                >
                  <span
                    :class="[
                      'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out',
                      isAllowed('student', def.key) ? 'translate-x-4' : 'translate-x-0',
                    ]"
                  />
                </button>
              </td>

              <!-- Staff Toggle -->
              <td class="px-4 py-4 text-center">
                <button
                  type="button"
                  :class="[
                    'relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none',
                    isAllowed('staff', def.key) ? 'bg-[#0F5132]' : 'bg-slate-200',
                  ]"
                  @click="handleToggle('staff', def)"
                >
                  <span
                    :class="[
                      'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out',
                      isAllowed('staff', def.key) ? 'translate-x-4' : 'translate-x-0',
                    ]"
                  />
                </button>
              </td>

              <!-- Admin Toggle -->
              <td class="px-4 py-4 text-center">
                <button
                  type="button"
                  :class="[
                    'relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none',
                    isAllowed('admin', def.key) ? 'bg-[#0F5132]' : 'bg-slate-200',
                  ]"
                  @click="handleToggle('admin', def)"
                >
                  <span
                    :class="[
                      'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out',
                      isAllowed('admin', def.key) ? 'translate-x-4' : 'translate-x-0',
                    ]"
                  />
                </button>
              </td>

              <!-- Quick action -->
              <td class="px-5 py-4 text-right">
                <AppButton variant="ghost" size="xs" @click="handleAllowAll(def)">
                  Allow All
                </AppButton>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Role Access Simulator & Live Preview -->
      <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h3 class="text-sm font-bold text-slate-900">Role Capabilities Simulator</h3>
            <p class="text-xs text-slate-500 mt-0.5">Test real-time access outcomes for specific user personas.</p>
          </div>

          <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-500">Simulate as:</span>
            <select
              v-model="simRole"
              class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0F5132]/30"
            >
              <option value="student">Student Persona</option>
              <option value="staff">Staff Persona</option>
              <option value="admin">Administrator Persona</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <div
            v-for="item in simResults"
            :key="item.key"
            :class="[
              'p-3 rounded-xl border flex items-center justify-between text-xs transition-colors',
              item.allowed
                ? 'bg-emerald-50/50 border-emerald-200 text-emerald-950'
                : 'bg-slate-50/60 border-slate-200 text-slate-400 opacity-60',
            ]"
          >
            <div class="min-w-0 pr-2">
              <span class="font-bold block truncate">{{ item.label }}</span>
              <span class="font-mono text-[10px] text-slate-500 truncate block">{{ item.key }}</span>
            </div>
            <span
              :class="[
                'px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0',
                item.allowed ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600',
              ]"
            >
              {{ item.allowed ? 'ALLOWED' : 'DENIED' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
