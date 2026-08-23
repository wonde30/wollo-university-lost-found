<script setup lang="ts">
import type { AuditLog } from '@/features/admin/types/admin.types'
import { formatDateTime } from '@/utils/date'
import AppBadge from '@/components/ui/AppBadge.vue'

interface Props {
  logs: AuditLog[]
  loading?: boolean
}

withDefaults(defineProps<Props>(), { loading: false })

function methodVariant(action: string): 'success' | 'warning' | 'danger' | 'info' {
  const act = (action || '').toLowerCase()
  if (act.includes('create') || act.includes('store') || act.includes('approved')) return 'success'
  if (act.includes('delete') || act.includes('destroy') || act.includes('rejected')) return 'danger'
  if (act.includes('update') || act.includes('edit')) return 'warning'
  return 'info'
}
</script>

<template>
  <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-xs">
    <table class="w-full min-w-[700px] text-xs">
      <thead>
        <tr class="border-b border-slate-200 bg-slate-50">
          <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Action</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Resource</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Actor</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">IP Address</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Timestamp</th>
        </tr>
      </thead>
      <tbody v-if="loading && logs.length === 0">
        <tr>
          <td colspan="5" class="px-4 py-8 text-center text-slate-400">Loading audit logs...</td>
        </tr>
      </tbody>
      <tbody v-else-if="logs.length === 0">
        <tr>
          <td colspan="5" class="px-4 py-8 text-center text-slate-400">No audit logs found.</td>
        </tr>
      </tbody>
      <tbody v-else class="divide-y divide-slate-100">
        <tr
          v-for="log in logs"
          :key="log.id"
          class="hover:bg-slate-50/50 transition-colors"
        >
          <td class="px-4 py-3">
            <AppBadge :variant="methodVariant(log.action)" size="sm">
              {{ log.action }}
            </AppBadge>
          </td>
          <td class="px-4 py-3 font-mono text-slate-700">
            {{ log.auditable_type?.split('\\').pop() }} #{{ log.auditable_id }}
          </td>
          <td class="px-4 py-3 text-slate-700">
            {{ log.actor?.full_name || log.user?.full_name || `Actor #${log.actor_id}` }}
          </td>
          <td class="px-4 py-3 text-slate-500 font-mono">
            {{ log.ip_address || '—' }}
          </td>
          <td class="px-4 py-3 text-slate-500">
            {{ formatDateTime(log.created_at) }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
