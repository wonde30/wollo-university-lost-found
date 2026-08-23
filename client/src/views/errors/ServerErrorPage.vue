<script setup lang="ts">
import BlankLayout from '@/layouts/BlankLayout.vue'
</script>

<template>
  <BlankLayout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-slate-50 p-6 text-center">
      <div class="max-w-md w-full space-y-6">
        <div class="text-[7rem] font-black text-slate-300 leading-none select-none">500</div>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 mb-2">Server error</h1>
          <p class="text-sm text-slate-500">
            Something went wrong on our end. Our team has been notified. Please try again in a few moments.
          </p>
        </div>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-xl bg-[#0F5132] text-white px-5 py-2.5 text-sm font-semibold hover:bg-[#0a3d22] transition-colors"
            @click="$router.go(0)"
          >
            Try again
          </button>
          <RouterLink
            to="/"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white text-slate-700 px-5 py-2.5 text-sm font-semibold hover:bg-slate-50 transition-colors"
          >
            Return Home
          </RouterLink>
        </div>
      </div>
    </div>
  </BlankLayout>
</template>
