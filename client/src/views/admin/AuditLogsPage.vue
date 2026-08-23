<script setup lang="ts">
import { onMounted, reactive, computed } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAdminStore } from '@/features/admin/stores/admin.store'
import AuditLogViewer from '@/features/admin/components/AuditLogViewer.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { useUiStore } from '@/stores/ui.store'

const uiStore = useUiStore()
const adminStore = useAdminStore()

const logs = computed(() => adminStore.auditLogs)
const loading = computed(() => adminStore.auditLogsLoading && logs.value.length === 0)
const pagination = computed(() => adminStore.auditLogsPagination)

const filters = reactive({ search: '', page: 1 })

async function load(page = 1) {
  filters.page = page
  await adminStore.fetchAuditLogs({
    page,
    per_page: 20,
    search: filters.search || undefined,
  })
}

let timer: ReturnType<typeof setTimeout>
function onSearch() {
  clearTimeout(timer)
  timer = setTimeout(() => load(1), 300)
}

function exportCsv() {
  if (logs.value.length === 0) {
    uiStore.warning('No audit logs to export.')
    return
  }

  const headers = ['ID', 'Action', 'Auditable Type', 'Auditable ID', 'Actor ID', 'IP Address', 'Created At']
  const rows = logs.value.map(log => [
    log.id,
    `"${log.action || ''}"`,
    `"${log.auditable_type || ''}"`,
    log.auditable_id || '',
    log.actor_id || '',
    `"${log.ip_address || ''}"`,
    `"${log.created_at || ''}"`,
  ])

  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `wollo_audit_logs_${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  uiStore.success('Audit logs exported successfully.')
}

onMounted(() => load())
</script>

<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-black text-slate-900">Security Audit Logs</h1>
          <p class="text-xs text-slate-500 mt-1">
            Immutable tracking of user activities, administrative actions, and property handovers.
          </p>
        </div>

        <div class="flex items-center gap-3">
          <AppInput
            id="audit-search"
            placeholder="Search action, model or actor..."
            :model-value="filters.search"
            class="w-64"
            @update:model-value="filters.search = $event; onSearch()"
          />
          <AppButton variant="outline" size="sm" @click="exportCsv">
            Export CSV
          </AppButton>
        </div>
      </div>

      <AuditLogViewer
        :logs="logs"
        :loading="loading"
      />

      <div v-if="pagination.last_page > 1" class="flex justify-center pt-2">
        <AppPagination
          :current-page="pagination.current_page"
          :total-pages="pagination.last_page"
          :total-items="pagination.total"
          :per-page="pagination.per_page"
          @page-change="load"
        />
      </div>
    </div>
  </DashboardLayout>
</template>
