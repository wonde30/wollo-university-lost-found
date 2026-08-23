<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import ReturnForm from '@/features/returns/components/ReturnForm.vue'
import { useRoute } from 'vue-router'
import { computed } from 'vue'

const route = useRoute()
const itemId = computed(() => route.query.item_id ? Number(route.query.item_id) : undefined)
const claimId = computed(() => route.query.claim_id ? Number(route.query.claim_id) : undefined)
</script>

<template>
  <DashboardLayout>
    <div class="max-w-2xl mx-auto space-y-6">
      <div>
        <h1 class="text-xl font-black text-slate-900">Process Item Return</h1>
        <p class="text-sm text-slate-500 mt-0.5">
          Record the physical handover of an item to its verified owner or claimant.
        </p>
      </div>

      <ReturnForm :item-id="itemId" :claim-id="claimId" />
    </div>
  </DashboardLayout>
</template>
