<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import SystemSettingsForm from '@/features/admin/components/SystemSettingsForm.vue'
</script>

<template>
  <DashboardLayout>
    <div class="max-w-3xl space-y-6">
      <div>
        <h1 class="text-xl font-black text-slate-900">System Settings</h1>
        <p class="text-sm text-slate-500 mt-0.5">Configure platform-wide operational settings.</p>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <SystemSettingsForm />
      </div>
    </div>
  </DashboardLayout>
</template>
