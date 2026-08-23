<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useClaims } from '@/features/claims/composables/useClaims'
import ClaimCard from '@/features/claims/components/ClaimCard.vue'
import ClaimReviewModal from '@/features/claims/components/ClaimReviewModal.vue'
import AppEmptyState from '@/components/ui/AppEmptyState.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import AppButton from '@/components/ui/AppButton.vue'
import type { Claim, ClaimStatus } from '@/features/claims/types/claim.types'

const router = useRouter()
const { claims, loading, pagination, loadClaims } = useClaims()
const selectedClaim = ref<Claim | null>(null)
const showReviewModal = ref(false)
const activeTab = ref<'pending' | 'approved' | 'all'>('pending')

async function load(page = 1) {
  const statusParam = activeTab.value === 'all' ? undefined : (activeTab.value as ClaimStatus)
  await loadClaims({ status: statusParam, page })
}

function setTab(tab: 'pending' | 'approved' | 'all') {
  activeTab.value = tab
  load(1)
}

function openReview(claim: Claim) {
  selectedClaim.value = claim
  showReviewModal.value = true
}

function goToProcessReturn(claim: Claim) {
  router.push({
    path: '/staff/process-return',
    query: { claim_id: claim.id, item_id: claim.item_id },
  })
}

onMounted(() => load())
</script>

<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-xl font-black text-slate-900">Review Ownership Claims</h1>
          <p class="text-sm text-slate-500 mt-0.5">Verify and process ownership claims submitted by students.</p>
        </div>

        <!-- Filter tabs -->
        <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-xl border border-slate-200 text-xs font-medium self-start">
          <button
            type="button"
            :class="[
              'px-3 py-1.5 rounded-lg transition-all',
              activeTab === 'pending' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900',
            ]"
            @click="setTab('pending')"
          >
            Pending
          </button>
          <button
            type="button"
            :class="[
              'px-3 py-1.5 rounded-lg transition-all',
              activeTab === 'approved' ? 'bg-white text-emerald-800 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900',
            ]"
            @click="setTab('approved')"
          >
            Approved
          </button>
          <button
            type="button"
            :class="[
              'px-3 py-1.5 rounded-lg transition-all',
              activeTab === 'all' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900',
            ]"
            @click="setTab('all')"
          >
            All Claims
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading && claims.length === 0" class="space-y-3">
        <AppSkeleton v-for="n in 4" :key="n" height="7rem" class="rounded-2xl" />
      </div>

      <!-- Empty -->
      <AppEmptyState
        v-else-if="claims.length === 0"
        :title="activeTab === 'pending' ? 'No pending claims' : (activeTab === 'approved' ? 'No approved claims' : 'No claims found')"
        description="There are no claims matching the current filter."
      />

      <!-- Claims List -->
      <div v-else class="space-y-4">
        <div
          v-for="claim in claims"
          :key="claim.id"
          class="relative"
        >
          <ClaimCard :claim="claim" />
          <div class="absolute top-4 right-4 flex items-center gap-2">
            <AppButton
              v-if="claim.status === 'approved'"
              size="sm"
              variant="primary"
              @click="goToProcessReturn(claim)"
            >
              Process Return →
            </AppButton>
            <AppButton
              :size="claim.status === 'approved' ? 'xs' : 'sm'"
              :variant="claim.status === 'approved' ? 'outline' : 'primary'"
              @click="openReview(claim)"
            >
              {{ claim.status === 'approved' ? 'Re-review' : 'Review' }}
            </AppButton>
          </div>
        </div>
      </div>

      <AppPagination
        v-if="pagination && pagination.last_page > 1"
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        @change="load"
      />
    </div>

    <!-- Review Modal -->
    <ClaimReviewModal
      :open="showReviewModal"
      :claim="selectedClaim"
      @close="showReviewModal = false; selectedClaim = null"
      @reviewed="load()"
    />
  </DashboardLayout>
</template>
