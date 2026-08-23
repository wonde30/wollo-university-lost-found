<script setup lang="ts">
import { onMounted, ref } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useCustody } from '@/features/custody/composables/useCustody'
import CustodyEventList from '@/features/custody/components/CustodyEventList.vue'
import CustodyTransferModal from '@/features/custody/components/CustodyTransferModal.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppPagination from '@/components/ui/AppPagination.vue'

const { events, pagination, loadEvents } = useCustody()
const showTransferModal = ref(false)
const transferItemId = ref<number>(0)
const transferItemTitle = ref<string>('')

async function load(page = 1) {
  await loadEvents({ page })
}

onMounted(() => load())
</script>

<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-black text-slate-900">Manage Item Custody</h1>
          <p class="text-sm text-slate-500 mt-0.5">Track storage and physical custody of all found items.</p>
        </div>
        <AppButton variant="primary" size="sm" @click="transferItemId = 0; transferItemTitle = ''; showTransferModal = true">
          + Transfer Item
        </AppButton>
      </div>

      <CustodyEventList
        :events="events"
        @transfer="(item) => { transferItemId = item.id; transferItemTitle = item.title || ''; showTransferModal = true }"
      />

      <AppPagination
        v-if="pagination && pagination.last_page > 1"
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        @change="load"
      />
    </div>

    <CustodyTransferModal
      :open="showTransferModal"
      :item-id="transferItemId"
      :item-title="transferItemTitle"
      @close="showTransferModal = false"
      @transferred="load()"
    />
  </DashboardLayout>
</template>
