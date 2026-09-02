<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useItems } from '@/features/items/composables/useItems'
import ClaimForm from '@/features/claims/components/ClaimForm.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import { t } from '@/i18n'

const route = useRoute()
const router = useRouter()
const { currentItem, loading, loadItem } = useItems()

const itemId = computed(() => Number(route.params.id || route.query.item_id))

onMounted(async () => {
  if (!itemId.value || isNaN(itemId.value)) {
    router.replace('/browse')
    return
  }
  await loadItem(itemId.value)
})
</script>

<template>
  <div class="max-w-2xl mx-auto space-y-4 sm:space-y-5">
    <div>
      <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ t('claims.submitClaim') }}</h1>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
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
</template>
