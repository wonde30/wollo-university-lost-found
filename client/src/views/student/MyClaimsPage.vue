<script setup lang="ts">
import { onMounted, ref } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useClaims } from '@/features/claims/composables/useClaims'
import type { Claim } from '@/features/claims/types/claim.types'
import AppEmptyState from '@/components/ui/AppEmptyState.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { formatDate } from '@/utils/date'

const { claims, loading, pagination, loadClaims } = useClaims()

const selectedClaim = ref<Claim | null>(null)
const detailModalOpen = ref(false)

async function load(page = 1) {
  await loadClaims({ page })
}

function openClaimDetail(claim: Claim) {
  selectedClaim.value = claim
  detailModalOpen.value = true
}

function getClaimBadge(status: string): any {
  switch (status) {
    case 'approved': return 'success'
    case 'rejected': return 'danger'
    case 'reversed': return 'warning'
    case 'pending':
    default: return 'primary'
  }
}

onMounted(() => load())
</script>

<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-black text-slate-900">My Ownership Claims</h1>
          <p class="text-xs text-slate-500 mt-1">Track the verification and decision status of your submitted property claims.</p>
        </div>

        <RouterLink to="/browse">
          <AppButton variant="outline" size="sm">
            Browse More Found Items
          </AppButton>
        </RouterLink>
      </div>

      <!-- Loading State -->
      <div v-if="loading && claims.length === 0" class="space-y-3">
        <AppSkeleton v-for="n in 3" :key="n" height="5rem" class="rounded-2xl" />
      </div>

      <!-- Empty State -->
      <AppEmptyState
        v-else-if="claims.length === 0"
        title="No claims submitted yet"
        description="Browse campus found items and submit an ownership claim to reclaim your belongings."
      >
        <RouterLink to="/browse">
          <AppButton variant="primary" size="sm">Browse Found Items</AppButton>
        </RouterLink>
      </AppEmptyState>

      <!-- Table of Claims -->
      <div v-else class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-600">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
              <tr>
                <th class="px-5 py-3.5">Claim ID</th>
                <th class="px-5 py-3.5">Claimed Item</th>
                <th class="px-5 py-3.5">Submitted Date</th>
                <th class="px-5 py-3.5">Verification Status</th>
                <th class="px-5 py-3.5 text-right">Details</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="claim in claims" :key="claim.id" class="hover:bg-slate-50/75 transition-colors">
                <td class="px-5 py-4 font-mono font-bold text-slate-900">
                  #CLM-{{ claim.id }}
                </td>
                <td class="px-5 py-4">
                  <div class="font-bold text-slate-900 text-sm truncate max-w-xs">
                    {{ claim.item?.title || `Item #${claim.item_id}` }}
                  </div>
                  <span class="text-[11px] font-mono text-slate-400">Ref: {{ claim.item?.reference_code || '—' }}</span>
                </td>
                <td class="px-5 py-4 text-slate-500">
                  {{ formatDate(claim.created_at) }}
                </td>
                <td class="px-5 py-4">
                  <AppBadge :variant="getClaimBadge(claim.status)" size="sm">
                    {{ claim.status.toUpperCase() }}
                  </AppBadge>
                </td>
                <td class="px-5 py-4 text-right">
                  <AppButton variant="ghost" size="xs" @click="openClaimDetail(claim)">
                    View Decision &rarr;
                  </AppButton>
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

      <!-- Claim Detail Modal -->
      <AppModal
        :open="detailModalOpen"
        :title="`Claim #CLM-${selectedClaim?.id} Details`"
        size="md"
        @close="detailModalOpen = false"
      >
        <div v-if="selectedClaim" class="space-y-4 text-xs">
          <!-- Item Summary -->
          <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
            <span class="text-slate-400 block font-semibold">Claimed Item:</span>
            <p class="text-sm font-bold text-slate-900 mt-0.5">
              {{ selectedClaim.item?.title || `Item #${selectedClaim.item_id}` }}
            </p>
          </div>

          <!-- Status Box -->
          <div class="flex items-center justify-between p-3.5 rounded-xl border" :class="selectedClaim.status === 'approved' ? 'bg-emerald-50 border-emerald-200' : (selectedClaim.status === 'rejected' ? 'bg-rose-50 border-rose-200' : 'bg-slate-50 border-slate-200')">
            <div>
              <span class="text-slate-500 font-semibold block">Decision Status:</span>
              <span class="font-bold text-sm uppercase" :class="selectedClaim.status === 'approved' ? 'text-emerald-800' : (selectedClaim.status === 'rejected' ? 'text-rose-700' : 'text-slate-800')">
                {{ selectedClaim.status }}
              </span>
            </div>
            <span class="text-slate-400 text-[11px]">{{ formatDate(selectedClaim.created_at) }}</span>
          </div>

          <!-- Reviewer Note if available -->
          <div v-if="selectedClaim.review_note" class="p-3.5 rounded-xl bg-amber-50 border border-amber-200">
            <span class="font-bold text-amber-900 block mb-1">Staff / Security Verification Note:</span>
            <p class="text-amber-800 italic leading-relaxed">{{ selectedClaim.review_note }}</p>
          </div>

          <!-- Description Evidence -->
          <div>
            <span class="font-semibold text-slate-700 block mb-1">Your Ownership Description & Proof:</span>
            <p class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 whitespace-pre-line leading-relaxed">
              {{ selectedClaim.explanation || 'No description provided.' }}
            </p>
          </div>
        </div>

        <template #footer>
          <AppButton variant="outline" size="sm" @click="detailModalOpen = false">
            Close
          </AppButton>
        </template>
      </AppModal>
    </div>
  </DashboardLayout>
</template>
