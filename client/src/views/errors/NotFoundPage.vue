<script setup lang="ts">
import BlankLayout from '@/layouts/BlankLayout.vue'
</script>

<template>
  <BlankLayout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-slate-50 p-6 text-center">
      <div class="max-w-md w-full space-y-6">
        <div class="text-[7rem] font-black text-[#0F5132]/10 leading-none select-none">404</div>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 mb-2">Page not found</h1>
          <p class="text-sm text-slate-500">
            Sorry, we couldn't find the page you're looking for. It may have been moved or deleted.
          </p>
        </div>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
          <RouterLink
            to="/"
            class="inline-flex items-center gap-2 rounded-xl bg-[#0F5132] text-white px-5 py-2.5 text-sm font-semibold hover:bg-[#0a3d22] transition-colors"
          >
            ← Return Home
          </RouterLink>
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white text-slate-700 px-5 py-2.5 text-sm font-semibold hover:bg-slate-50 transition-colors"
            @click="$router.back()"
          >
            Go back
          </button>
        </div>
      </div>
    </div>
  </BlankLayout>
</template>
