<script setup lang="ts">
import BlankLayout from '@/layouts/BlankLayout.vue'
</script>

<template>
  <BlankLayout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-slate-50 p-6 text-center">
      <div class="max-w-md w-full space-y-6">
        <div class="text-[7rem] font-black text-amber-500/15 leading-none select-none">403</div>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 mb-2">Access forbidden</h1>
          <p class="text-sm text-slate-500">
            You don't have the necessary permissions to access this page.
            Contact your administrator if you believe this is a mistake.
          </p>
        </div>
        <RouterLink
          to="/"
          class="inline-flex items-center gap-2 rounded-xl bg-[#0F5132] text-white px-5 py-2.5 text-sm font-semibold hover:bg-[#0a3d22] transition-colors"
        >
          ← Return Home
        </RouterLink>
      </div>
    </div>
  </BlankLayout>
</template>
