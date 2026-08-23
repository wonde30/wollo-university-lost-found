<script setup lang="ts">
import AppToast from '@/components/ui/AppToast.vue'
import LoadingOverlay from '@/components/feedback/LoadingOverlay.vue'
import NetworkStatus from '@/components/feedback/NetworkStatus.vue'
</script>

<template>
  <div class="min-h-screen flex flex-col lg:flex-row bg-slate-950 text-slate-900">
    <NetworkStatus />

    <!-- Left Brand Panel (Desktop) -->
    <div class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-[#0B3822] via-[#072516] to-[#04140B] text-white p-12 flex-col justify-between overflow-hidden">
      <!-- Decorative radial glow -->
      <div class="absolute -top-24 -left-24 w-96 h-96 bg-[#D4AF37]/15 rounded-full blur-3xl pointer-events-none" />
      <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-[#0F5132]/30 rounded-full blur-3xl pointer-events-none" />

      <!-- Top Header Brand -->
      <div class="relative z-10">
        <RouterLink to="/" class="inline-flex items-center gap-3.5 group">
          <img
            src="/images/wu-logo.png"
            alt="Wollo University Emblem"
            class="h-12 w-12 shrink-0 object-contain rounded-full bg-white shadow-xl ring-2 ring-[#D4AF37]/70 p-0.5 group-hover:scale-105 transition-transform"
          />
          <div class="flex flex-col text-left">
            <span class="text-xl font-black tracking-tight text-white leading-none">WOLLO UNIVERSITY</span>
            <span class="text-xs font-bold tracking-widest text-[#D4AF37] uppercase mt-1">Lost & Found Portal</span>
          </div>
        </RouterLink>
      </div>

      <!-- Feature Highlights -->
      <div class="relative z-10 my-auto max-w-lg space-y-8">
        <div>
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#D4AF37]/10 text-[#D4AF37] border border-[#D4AF37]/30 uppercase tracking-wider mb-4">
            Campus Property Management
          </span>
          <h1 class="text-3xl font-extrabold tracking-tight text-white leading-tight">
            Secure, Verified & Centralized Campus Property Recovery
          </h1>
          <p class="mt-3 text-sm text-slate-300 leading-relaxed">
            Serving students, faculty, and administrative staff across Dessie Main Campus and Kombolcha Institute of Technology (KIoT).
          </p>
        </div>

        <div class="space-y-4 pt-2">
          <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
            <div class="h-9 w-9 rounded-xl bg-[#0F5132] text-[#D4AF37] flex items-center justify-center shrink-0 border border-[#D4AF37]/30">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <div>
              <h4 class="text-sm font-bold text-white">Verified Ownership Verification</h4>
              <p class="text-xs text-slate-300 mt-0.5">High-security claim resolution and evidence submission workflow.</p>
            </div>
          </div>

          <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
            <div class="h-9 w-9 rounded-xl bg-[#0F5132] text-[#D4AF37] flex items-center justify-center shrink-0 border border-[#D4AF37]/30">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <div>
              <h4 class="text-sm font-bold text-white">Physical Custody Tracking</h4>
              <p class="text-xs text-slate-300 mt-0.5">Real-time inventory of items in campus security offices and custody vaults.</p>
            </div>
          </div>

          <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm">
            <div class="h-9 w-9 rounded-xl bg-[#0F5132] text-[#D4AF37] flex items-center justify-center shrink-0 border border-[#D4AF37]/30">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </div>
            <div>
              <h4 class="text-sm font-bold text-white">Real-Time Match Alerts</h4>
              <p class="text-xs text-slate-300 mt-0.5">Automated notification alerts when reported lost items match found property.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Info -->
      <div class="relative z-10 pt-6 border-t border-white/10 flex items-center justify-between text-xs text-slate-400">
        <span>&copy; {{ new Date().getFullYear() }} Wollo University</span>
        <span class="font-medium text-slate-300">Powered by WONDATIR (IT)</span>
      </div>
    </div>

    <!-- Right Form Panel -->
    <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-6 sm:p-12 bg-slate-50 relative overflow-y-auto">
      <!-- Mobile Logo Header (visible on mobile only) -->
      <div class="lg:hidden text-center mb-8">
        <RouterLink to="/" class="inline-flex items-center gap-2.5">
          <img
            src="/images/wu-logo.png"
            alt="Wollo University Emblem"
            class="h-11 w-11 shrink-0 object-contain rounded-full bg-white shadow-md ring-1 ring-[#D4AF37]/50 p-0.5"
          />
          <div class="flex flex-col text-left">
            <span class="text-base font-black tracking-tight text-slate-900 leading-none">WOLLO UNIVERSITY</span>
            <span class="text-[10px] font-bold tracking-wider text-[#0F5132] uppercase mt-0.5">Lost & Found</span>
          </div>
        </RouterLink>
      </div>

      <!-- Form Container Card -->
      <div class="w-full max-w-md bg-white p-6 sm:p-10 rounded-3xl shadow-xl border border-slate-200/80">
        <slot />
      </div>

      <!-- Mobile Footer -->
      <div class="mt-8 text-center text-xs text-slate-400">
        <p class="lg:hidden text-[11px] mb-2">Powered by WONDATIR (IT)</p>
        <RouterLink to="/" class="hover:text-slate-700 transition-colors inline-flex items-center gap-1 font-medium">
          <span>&larr;</span> Back to Home
        </RouterLink>
      </div>
    </div>

    <AppToast />
    <LoadingOverlay />
  </div>
</template>
