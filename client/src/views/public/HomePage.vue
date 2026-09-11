<script setup lang="ts">
/**
 * Public Landing Page (Home)
 *
 * Assembles all production-grade landing sections matching the Wollo University
 * official visual design:
 * 1. Hero with real Wollo University campus gate photograph and action CTAs
 * 2. Overlapping floating statistics pill bar (data-driven with fallbacks)
 * 3. Feature showcase (Smart Search, Report Items, Track Progress, Get Notified)
 * 4. Step-by-step "How It Works" chain
 * 5. Browse by Category with item counts
 * 6. Recently Found Items cards with photos and location
 * 7. Mid-page campus-backed blue CTA banner
 * 8. Community testimonials with ratings
 * 9. Frequently Asked Questions (2-column accordion)
 */

import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { useLandingData } from './landing/useLandingData'
import LandingHero from './landing/LandingHero.vue'
import LandingStats from './landing/LandingStats.vue'
import LandingFeatures from './landing/LandingFeatures.vue'
import LandingHowItWorks from './landing/LandingHowItWorks.vue'
import LandingCategories from './landing/LandingCategories.vue'
import LandingRecentItems from './landing/LandingRecentItems.vue'
import LandingFinalCta from './landing/LandingFinalCta.vue'
import LandingTestimonials from './landing/LandingTestimonials.vue'
import LandingFaq from './landing/LandingFaq.vue'

const {
  statistics,
  statsLoading,
  categories,
  categoriesLoading,
  recentItems,
  itemsLoading,
} = useLandingData()
</script>

<template>
  <DefaultLayout full-width>
    <div class="landing-page w-full">
      <!-- 1. Hero Section (Edge-to-Edge with Real Campus Gate) -->
      <LandingHero />

      <!-- 2. Overlapping Floating Statistics Pill Bar -->
      <LandingStats :statistics="statistics" :loading="statsLoading" />

      <!-- 3. Our Features 4-Card Grid -->
      <LandingFeatures />

      <!-- 4. How It Works 4-Step Chain -->
      <LandingHowItWorks />

      <!-- 5. Browse By Category 8-Card Grid -->
      <LandingCategories :categories="categories" :loading="categoriesLoading" />

      <!-- 6. Recently Found Items 5-Card Row -->
      <LandingRecentItems :items="recentItems" :loading="itemsLoading" />

      <!-- 7. Lost or Found Something Campus-Backed Blue Banner CTA -->
      <LandingFinalCta />

      <!-- 8. What Our Community Says Testimonials -->
      <LandingTestimonials />

      <!-- 9. Frequently Asked Questions Accordion -->
      <LandingFaq />
    </div>
  </DefaultLayout>
</template>
