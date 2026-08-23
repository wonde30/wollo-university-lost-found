<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useItems } from '@/features/items/composables/useItems'
import ClaimForm from '@/features/claims/components/ClaimForm.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'

const route = useRoute()
const router = useRouter()
const { currentItem, loading, loadItem } = useItems()

const itemId = computed(() => Number(route.query.item_id))

onMounted(async () => {
  if (!itemId.value) {
    router.replace('/browse')
    return
  }
  await loadItem(itemId.value)
})
</script>

<template>
  <DashboardLayout>
    <div class="max-w-2xl mx-auto space-y-6">
      <div>
        <h1 class="text-xl font-black text-slate-900">Submit Ownership Claim</h1>
        <p class="text-sm text-slate-500 mt-0.5">
          Prove that this found item belongs to you by providing evidence of ownership.
        </p>
      </div>

      <div v-if="loading" class="space-y-3">
        <AppSkeleton height="5rem" class="rounded-2xl" />
        <AppSkeleton height="10rem" class="rounded-2xl" />
      </div>

      <ClaimForm
        v-else-if="itemId"
        :item-id="itemId"
        :item-title="currentItem?.title"
      />
    </div>
  </DashboardLayout>
</template>
