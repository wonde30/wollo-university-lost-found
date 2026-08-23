<script setup lang="ts">
const currentYear = new Date().getFullYear()
</script>

<template>
  <footer class="border-t border-slate-200 bg-white py-10 px-4 sm:px-6 lg:px-8 text-slate-500 text-xs">
    <div class="max-w-7xl mx-auto space-y-6">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-center md:text-left">
        <div class="flex items-center gap-3">
          <img
            src="/images/wu-logo.png"
            alt="Wollo University Emblem"
            class="h-10 w-10 shrink-0 object-contain rounded-full bg-white shadow-xs ring-1 ring-[#D4AF37]/40 p-0.5"
          />
          <div>
            <span class="font-bold text-slate-900 block">
              Wollo University Lost & Found System
            </span>
            <span class="text-[11px] text-slate-400">
              Dessie Campus & Kombolcha Institute of Technology (KIoT)
            </span>
          </div>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-6 text-slate-600 font-medium">
          <RouterLink to="/" class="hover:text-[#0F5132] transition-colors">Home</RouterLink>
          <RouterLink to="/browse" class="hover:text-[#0F5132] transition-colors">Browse Items</RouterLink>
          <RouterLink to="/track" class="hover:text-[#0F5132] transition-colors">Track Reference</RouterLink>
          <a href="https://wu.edu.et" target="_blank" rel="noopener noreferrer" class="hover:text-[#0F5132] transition-colors">
            University Main Site
          </a>
        </div>
      </div>

      <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-slate-400 text-center sm:text-left">
        <p>&copy; {{ currentYear }} Wollo University. All rights reserved.</p>
        <p class="font-medium text-slate-500">
          Powered by WONDATIR FETENE (IT)
        </p>
      </div>
    </div>
  </footer>
</template>
