<script setup lang="ts">
import { reactive, ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useItemsStore } from '@/features/items/stores/items.store'
import { validateLostItemForm } from '@/features/items/validation/item.validation'
import { useReferenceData } from '@/features/lookups/composables/useReferenceData'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage, getValidationErrors } from '@/utils/error-handler'
import { toISODateInput, formatDate } from '@/utils/date'
import { currentLocale, t } from '@/i18n'
import type { StoreLostItemData } from '@/features/items/types/item.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppCheckbox from '@/components/ui/AppCheckbox.vue'
import FormMultiImageUpload from '@/components/forms/FormMultiImageUpload.vue'

import { checkDuplicate, getItem } from '@/features/items/api/items.api'
import AppModal from '@/components/ui/AppModal.vue'

import {
  PackageSearch,
  Package,
  Check,
  ArrowLeft,
  ArrowRight,
  AlertTriangle,
  Tag,
  MapPin,
  CheckCircle2,
  Building2,
  Clock,
  Info,
  ShieldAlert,
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const itemsStore = useItemsStore()
const uiStore = useUiStore()
const { categories, locations } = useReferenceData()

const currentStep = ref(1)
const fromFoundItem = ref<{ id: number; reference_code: string; title: string } | null>(null)
const loadingPreFill = ref(false)

onMounted(async () => {
  const fromFoundId = route.query.from_found_id
  if (fromFoundId) {
    const id = Number(fromFoundId)
    if (!isNaN(id) && id > 0) {
      loadingPreFill.value = true
      try {
        const item = await getItem(id)
        if (item) {
          fromFoundItem.value = {
            id: item.id,
            reference_code: item.reference_code,
            title: item.title,
          }
          if (item.title) form.title = item.title
          if (item.description) form.description = item.description
          if (item.category_id) form.category_id = Number(item.category_id)
          if (item.location_id) form.location_id = Number(item.location_id)
          if (item.campus_id) form.campus_id = Number(item.campus_id)
          if (item.brand) form.brand = item.brand
          if (item.color) form.color = item.color
          if (item.serial_number) form.serial_number = item.serial_number
          if (item.is_high_value) form.is_high_value = true
        }
      } catch (err) {
        console.error('Failed to pre-fill lost item details', err)
      } finally {
        loadingPreFill.value = false
      }
    }
  }
})

const form = reactive({
  title: '',
  description: '',
  category_id: '' as number | '',
  location_id: '' as number | '',
  campus_id: undefined as number | undefined,
  brand: '',
  color: '',
  serial_number: '',
  estimated_value: undefined as number | undefined,
  incident_date: toISODateInput(),
  incident_time: '',
  is_high_value: false,
  photos: [] as File[],
})

const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)
const submitting = ref(false)
const duplicateWarningModalOpen = ref(false)
const duplicateWarningText = ref('')
const proceedWithDuplicate = ref(false)

function validateStep1(): boolean {
  errors.value = {}
  if (!form.title.trim()) {
    errors.value.title = t('validation.required')
  } else if (form.title.trim().length < 5) {
    errors.value.title = t('validation.minLength', { min: 5 })
  }

  if (!form.description.trim()) {
    errors.value.description = t('validation.required')
  } else if (form.description.trim().length < 20) {
    errors.value.description = t('validation.minLength', { min: 20 })
  }

  if (!form.category_id) {
    errors.value.category_id = t('validation.required')
  }

  if (!form.incident_date) {
    errors.value.incident_date = t('validation.required')
  }

  return Object.keys(errors.value).length === 0
}

function nextStep() {
  if (currentStep.value === 1) {
    if (!validateStep1()) return
    currentStep.value = 2
  } else if (currentStep.value === 2) {
    currentStep.value = 3
  }
}

function prevStep() {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

function handleLocationChange(val: string | number) {
  const locId = Number(val)
  form.location_id = locId
  const loc = locations.value.find(l => l.id === locId)
  if (loc?.campus_id) {
    form.campus_id = loc.campus_id
  }
}

const categoryOptions = computed(() => {
  return categories.value.map(c => ({
    label: (currentLocale.value === 'am' && c.display_name_am) ? c.display_name_am : (c.display_name || c.name),
    value: c.id,
  }))
})

const locationOptions = computed(() => {
  return locations.value.map(l => ({
    label: (currentLocale.value === 'am' && l.display_name_am) ? l.display_name_am : (l.display_name || l.name),
    value: l.id,
  }))
})

const selectedCategoryName = computed(() => {
  const cat = categories.value.find(c => c.id === form.category_id)
  return (currentLocale.value === 'am' && cat?.display_name_am) ? cat.display_name_am : (cat?.display_name || cat?.name || t('items.category'))
})

const selectedLocationName = computed(() => {
  const loc = locations.value.find(l => l.id === form.location_id)
  return (currentLocale.value === 'am' && loc?.display_name_am) ? loc.display_name_am : (loc?.display_name || loc?.name || t('nav.locations'))
})

async function handleSubmit() {
  generalError.value = null
  const allErrors = validateLostItemForm(form as unknown as Partial<StoreLostItemData>)
  if (Object.keys(allErrors).length > 0) {
    errors.value = allErrors
    currentStep.value = 1
    return
  }

  submitting.value = true

  // FR-63: Pre-submission Duplicate Check (7-day window)
  if (!proceedWithDuplicate.value && form.category_id) {
    try {
      const dup = await checkDuplicate({
        category_id: Number(form.category_id),
        campus_id: form.campus_id,
        serial_number: form.serial_number || undefined,
      })

      if (dup.duplicate_found) {
        duplicateWarningText.value = dup.message || 'A similar item was recently reported in this campus area within the last 7 days.'
        duplicateWarningModalOpen.value = true
        submitting.value = false
        return
      }
    } catch {
      // Continue if duplicate check fails
    }
  }

  try {
    const item = await itemsStore.createLostItem(form as unknown as StoreLostItemData)
    uiStore.success(t('items.createdSuccess'))
    router.push(`/items/${item.id}`)
  } catch (err) {
    const fieldErrors = getValidationErrors(err)
    if (fieldErrors) {
      errors.value = Object.entries(fieldErrors).reduce((acc, [k, v]) => {
        acc[k] = Array.isArray(v) ? v[0] : v
        return acc
      }, {} as Record<string, string>)
      currentStep.value = 1
    } else {
      generalError.value = getErrorMessage(err, t('common.errorOccurred'))
    }
  } finally {
    submitting.value = false
  }
}

function confirmDuplicateSubmission() {
  duplicateWarningModalOpen.value = false
  proceedWithDuplicate.value = true
  handleSubmit()
}
</script>

<template>
  <div class="space-y-4 sm:space-y-5 w-full">
    <!-- Breadcrumb Context -->
    <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
      <PackageSearch class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
      <span>{{ t('nav.portal') || 'Student Portal' }}</span>
      <span>&rsaquo;</span>
      <RouterLink to="/student/my-items" class="hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
        {{ t('nav.myItems') }}
      </RouterLink>
      <span>&rsaquo;</span>
      <span class="text-slate-900 dark:text-slate-100 font-extrabold">{{ t('items.reportLost') }}</span>
    </div>

    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <div class="flex items-center gap-2.5">
          <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
            {{ t('items.reportLost') }}
          </h1>
          <span
            class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800"
          >
            {{ t('common.stepCount', { current: currentStep, total: 3 }) || `Step ${currentStep} of 3` }}
          </span>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">
          {{ t('reportWizard.step1DescLost') }}
        </p>
      </div>

      <div class="flex items-center gap-2 shrink-0">
        <RouterLink
          to="/student/my-items"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111827] text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-2xs transition-colors"
        >
          <Package class="h-4 w-4 text-slate-400" />
          <span>{{ t('nav.myItems') }}</span>
        </RouterLink>
      </div>
    </div>

    <!-- Pre-fill Banner -->
    <div
      v-if="fromFoundItem"
      class="flex items-center justify-between gap-3 p-3.5 sm:p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs sm:text-sm text-amber-800 dark:text-amber-300"
    >
      <div class="flex items-center gap-2.5">
        <Info class="h-4 w-4 sm:h-5 sm:w-5 text-amber-600 dark:text-amber-400 shrink-0" />
        <span class="font-bold">
          {{ t('items.crossLinkLostBanner', { ref: fromFoundItem.reference_code }) }}
        </span>
      </div>
      <RouterLink
        :to="`/items/${fromFoundItem.id}`"
        class="underline font-extrabold hover:text-amber-950 dark:hover:text-amber-200 shrink-0 font-mono"
      >
        #{{ fromFoundItem.reference_code }}
      </RouterLink>
    </div>

    <!-- Main Content 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">
      <!-- Left Column: Unified Form Wizard Card (lg:col-span-8) -->
      <div class="lg:col-span-8 bg-white dark:bg-[#111827] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs overflow-hidden transition-colors duration-150">
        <!-- Integrated Stepper Navigation Bar -->
        <div class="bg-slate-50/70 dark:bg-slate-900/60 border-b border-slate-200/80 dark:border-slate-800/80 px-4 sm:px-8 py-3.5 sm:py-4">
          <div class="flex items-center justify-between">
            <!-- Step 1 -->
            <div
              class="flex items-center gap-2 cursor-pointer group"
              @click="currentStep > 1 && (currentStep = 1)"
            >
              <div
                class="h-7 w-7 sm:h-8 sm:w-8 rounded-full flex items-center justify-center text-xs font-black transition-all shadow-xs shrink-0"
                :class="currentStep === 1
                  ? 'bg-[#0B5D3B] text-white ring-4 ring-[#0B5D3B]/20 dark:ring-[#0B5D3B]/40'
                  : currentStep > 1
                    ? 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 group-hover:scale-105'
                    : 'bg-slate-200/70 dark:bg-slate-800 text-slate-400 dark:text-slate-500'"
              >
                <Check v-if="currentStep > 1" class="h-4 w-4 stroke-[2.5]" />
                <span v-else>1</span>
              </div>
              <span
                class="text-xs font-bold transition-colors hidden sm:inline"
                :class="currentStep === 1 ? 'text-[#0B5D3B] dark:text-[#75bd97]' : currentStep > 1 ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500'"
              >
                {{ t('reportWizard.basicInfo') }}
              </span>
            </div>

            <div
              class="h-0.5 flex-1 mx-3 sm:mx-6 transition-colors duration-200"
              :class="currentStep > 1 ? 'bg-emerald-500' : 'bg-slate-200 dark:bg-slate-800'"
            />

            <!-- Step 2 -->
            <div
              class="flex items-center gap-2 cursor-pointer group"
              @click="currentStep === 3 && (currentStep = 2)"
            >
              <div
                class="h-7 w-7 sm:h-8 sm:w-8 rounded-full flex items-center justify-center text-xs font-black transition-all shadow-xs shrink-0"
                :class="currentStep === 2
                  ? 'bg-[#0B5D3B] text-white ring-4 ring-[#0B5D3B]/20 dark:ring-[#0B5D3B]/40'
                  : currentStep > 2
                    ? 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 group-hover:scale-105'
                    : 'bg-slate-200/70 dark:bg-slate-800 text-slate-400 dark:text-slate-500'"
              >
                <Check v-if="currentStep > 2" class="h-4 w-4 stroke-[2.5]" />
                <span v-else>2</span>
              </div>
              <span
                class="text-xs font-bold transition-colors hidden sm:inline"
                :class="currentStep === 2 ? 'text-[#0B5D3B] dark:text-[#75bd97]' : currentStep > 2 ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500'"
              >
                {{ t('reportWizard.locationAndPhotos') }}
              </span>
            </div>

            <div
              class="h-0.5 flex-1 mx-3 sm:mx-6 transition-colors duration-200"
              :class="currentStep === 3 ? 'bg-emerald-500' : 'bg-slate-200 dark:bg-slate-800'"
            />

            <!-- Step 3 -->
            <div class="flex items-center gap-2">
              <div
                class="h-7 w-7 sm:h-8 sm:w-8 rounded-full flex items-center justify-center text-xs font-black transition-all shadow-xs shrink-0"
                :class="currentStep === 3
                  ? 'bg-[#0B5D3B] text-white ring-4 ring-[#0B5D3B]/20 dark:ring-[#0B5D3B]/40'
                  : 'bg-slate-200/70 dark:bg-slate-800 text-slate-400 dark:text-slate-500'"
              >
                3
              </div>
              <span
                class="text-xs font-bold transition-colors hidden sm:inline"
                :class="currentStep === 3 ? 'text-[#0B5D3B] dark:text-[#75bd97]' : 'text-slate-400 dark:text-slate-500'"
              >
                {{ t('reportWizard.reviewAndSubmit') }}
              </span>
            </div>
          </div>
        </div>

        <!-- General Error Alert -->
        <div v-if="generalError" class="m-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-400 font-bold flex items-center gap-2.5">
          <AlertTriangle class="h-4 w-4 shrink-0" />
          <span>{{ generalError }}</span>
        </div>

        <!-- ========================================== -->
        <!-- STEP 1: Basic Information                 -->
        <!-- ========================================== -->
        <div v-if="currentStep === 1" class="p-6 sm:p-8 space-y-6">
          <div class="flex items-start gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="h-9 w-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-[#0B5D3B] dark:text-[#75bd97] flex items-center justify-center shrink-0">
              <Tag class="h-4.5 w-4.5" />
            </div>
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ t('reportWizard.step1') }}</h2>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ t('reportWizard.step1DescLost') }}</p>
            </div>
          </div>

          <div class="space-y-5">
            <AppInput
              :label="t('items.form.title')"
              :placeholder="t('reportWizard.placeholders.lostTitle')"
              :model-value="form.title"
              :error="errors.title"
              required
              :maxlength="150"
              @update:model-value="form.title = $event"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <AppSelect
                :label="t('items.myItems.category')"
                :placeholder="t('items.myItems.category')"
                :options="categoryOptions"
                :model-value="form.category_id"
                :error="errors.category_id"
                required
                @update:model-value="form.category_id = Number($event)"
              />

              <AppInput
                :label="t('items.form.incidentDate')"
                type="date"
                :model-value="form.incident_date"
                :error="errors.incident_date"
                required
                @update:model-value="form.incident_date = $event"
              />
            </div>

            <AppTextarea
              :label="t('items.form.description')"
              :placeholder="t('items.form.description')"
              :rows="4"
              :model-value="form.description"
              :error="errors.description"
              required
              :maxlength="2000"
              @update:model-value="form.description = $event"
            />
          </div>
        </div>

        <!-- ========================================== -->
        <!-- STEP 2: Location & Identification Details -->
        <!-- ========================================== -->
        <div v-else-if="currentStep === 2" class="p-6 sm:p-8 space-y-6">
          <div class="flex items-start gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="h-9 w-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-[#0B5D3B] dark:text-[#75bd97] flex items-center justify-center shrink-0">
              <MapPin class="h-4.5 w-4.5" />
            </div>
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ t('reportWizard.step2Lost') }}</h2>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ t('reportWizard.step2DescLost') }}</p>
            </div>
          </div>

          <div class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <AppSelect
                :label="t('reportWizard.lastKnownLocation')"
                :placeholder="t('nav.locations')"
                :options="locationOptions"
                :model-value="form.location_id"
                @update:model-value="handleLocationChange"
              />

              <AppInput
                :label="t('reportWizard.brandModel')"
                :placeholder="t('reportWizard.placeholders.brandLost')"
                :model-value="form.brand"
                @update:model-value="form.brand = $event"
              />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <AppInput
                :label="t('reportWizard.color')"
                :placeholder="t('reportWizard.placeholders.colorLost')"
                :model-value="form.color"
                @update:model-value="form.color = $event"
              />

              <AppInput
                :label="t('reportWizard.serialNumber')"
                :placeholder="t('reportWizard.placeholders.serialNumber')"
                :model-value="form.serial_number"
                @update:model-value="form.serial_number = $event"
              />
            </div>

            <div class="pt-1">
              <FormMultiImageUpload
                :label="t('reportWizard.uploadPhotos')"
                :max-files="3"
                @files-updated="form.photos = $event"
              />
            </div>

            <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-900/50 border border-slate-200/90 dark:border-slate-800">
              <AppCheckbox
                :label="t('reportWizard.highValue')"
                :description="t('reportWizard.highValueDesc')"
                :model-value="form.is_high_value"
                @update:model-value="form.is_high_value = $event"
              />
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- STEP 3: Review & Finalize Submission      -->
        <!-- ========================================== -->
        <div v-else-if="currentStep === 3" class="p-6 sm:p-8 space-y-6">
          <div class="flex items-start gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="h-9 w-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-[#0B5D3B] dark:text-[#75bd97] flex items-center justify-center shrink-0">
              <CheckCircle2 class="h-4.5 w-4.5" />
            </div>
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ t('reportWizard.step3Lost') }}</h2>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ t('reportWizard.step3DescLost') }}</p>
            </div>
          </div>

          <div class="p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-900/50 border border-slate-200/90 dark:border-slate-800 space-y-5">
            <div>
              <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">{{ t('reportWizard.reportTitle') }}</span>
              <p class="text-base font-extrabold text-slate-900 dark:text-white mt-1">{{ form.title }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-3 border-t border-slate-200/70 dark:border-slate-800">
              <div class="p-3 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800/80">
                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">{{ t('items.myItems.category') }}</span>
                <span class="font-bold text-xs sm:text-sm text-slate-900 dark:text-slate-100 mt-0.5 block">{{ selectedCategoryName }}</span>
              </div>
              <div class="p-3 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800/80">
                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">{{ t('items.form.incidentDate') }}</span>
                <span class="font-bold text-xs sm:text-sm text-slate-900 dark:text-slate-100 mt-0.5 block">{{ formatDate(form.incident_date) }}</span>
              </div>
              <div class="p-3 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800/80">
                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">{{ t('nav.locations') }}</span>
                <span class="font-bold text-xs sm:text-sm text-slate-900 dark:text-slate-100 mt-0.5 block">{{ selectedLocationName }}</span>
              </div>
            </div>

            <div v-if="form.brand || form.color || form.serial_number" class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-200/70 dark:border-slate-800 text-xs">
              <div v-if="form.brand">
                <span class="text-slate-400 dark:text-slate-500 font-semibold block">{{ t('reportWizard.brandModel') }}:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 mt-0.5 block">{{ form.brand }}</span>
              </div>
              <div v-if="form.color">
                <span class="text-slate-400 dark:text-slate-500 font-semibold block">{{ t('reportWizard.color') }}:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 mt-0.5 block">{{ form.color }}</span>
              </div>
              <div v-if="form.serial_number">
                <span class="text-slate-400 dark:text-slate-500 font-semibold block">{{ t('reportWizard.serialNumber') }}:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 mt-0.5 block">{{ form.serial_number }}</span>
              </div>
            </div>

            <div class="pt-3 border-t border-slate-200/70 dark:border-slate-800">
              <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1.5">{{ t('items.form.description') }}</span>
              <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line leading-relaxed p-3.5 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800/80">
                {{ form.description }}
              </p>
            </div>

            <div v-if="form.photos.length > 0" class="pt-3 border-t border-slate-200/70 dark:border-slate-800">
              <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1.5">{{ t('reportWizard.attachedPhotos') }}</span>
              <span class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0B5D3B] dark:text-[#75bd97] px-3 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800">
                <Check class="h-3.5 w-3.5" />
                {{ t('reportWizard.photosAttached', { count: form.photos.length }) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Integrated Card Footer Action Bar -->
        <div class="bg-slate-50/70 dark:bg-slate-900/60 border-t border-slate-200/80 dark:border-slate-800/80 px-6 sm:px-8 py-4 flex items-center justify-between gap-3">
          <div>
            <AppButton
              v-if="currentStep > 1"
              variant="outline"
              size="md"
              :disabled="submitting"
              @click="prevStep"
            >
              <ArrowLeft class="h-4 w-4 mr-1.5" />
              <span>{{ t('common.back') }}</span>
            </AppButton>
            <RouterLink v-else to="/student/my-items">
              <AppButton variant="outline" size="md">
                {{ t('common.cancel') }}
              </AppButton>
            </RouterLink>
          </div>

          <div>
            <AppButton
              v-if="currentStep === 1"
              variant="primary"
              size="md"
              @click="nextStep"
            >
              <span>{{ t('reportWizard.locationAndPhotos') }}</span>
              <ArrowRight class="h-4 w-4 ml-1.5" />
            </AppButton>

            <AppButton
              v-else-if="currentStep === 2"
              variant="primary"
              size="md"
              @click="nextStep"
            >
              <span>{{ t('reportWizard.reviewAndSubmit') }}</span>
              <ArrowRight class="h-4 w-4 ml-1.5" />
            </AppButton>

            <AppButton
              v-else-if="currentStep === 3"
              variant="primary"
              size="md"
              :loading="submitting"
              @click="handleSubmit"
            >
              <Check class="h-4 w-4 mr-1.5" />
              <span>{{ t('items.reportLost') }}</span>
            </AppButton>
          </div>
        </div>
      </div>

      <!-- Right Column: Live Form Summary & Guidelines Side Panel (lg:col-span-4) -->
      <div class="lg:col-span-4 space-y-4 sm:space-y-5">
        <!-- Live Form Progress Card -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75" />
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500" />
              </span>
              <span class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">
                {{ t('reportWizard.liveOverview') }}
              </span>
            </div>
            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800">
              {{ t('reportWizard.lostSummary') }}
            </span>
          </div>

          <div class="p-3.5 rounded-xl bg-slate-50/80 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-800 space-y-2.5">
            <div>
              <p class="text-xs font-extrabold text-slate-900 dark:text-white truncate">
                {{ form.title || t('reportWizard.untitledLost') }}
              </p>
              <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                {{ selectedCategoryName }} &bull; {{ formatDate(form.incident_date) }}
              </p>
            </div>

            <div v-if="form.location_id" class="pt-2 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between text-xs">
              <span class="text-slate-500 dark:text-slate-400 text-[11px] font-medium">{{ t('reportWizard.lastKnown') }}:</span>
              <span class="font-bold text-slate-800 dark:text-slate-200 text-[11px] truncate max-w-[140px]">
                {{ selectedLocationName }}
              </span>
            </div>

            <div v-if="form.is_high_value" class="flex items-center justify-between text-xs">
              <span class="text-slate-500 dark:text-slate-400 text-[11px] font-medium">{{ t('reportWizard.priority') }}:</span>
              <span class="font-bold text-rose-600 dark:text-rose-400 text-[11px]">
                {{ t('reportWizard.highValuePriority') }}
              </span>
            </div>
          </div>
        </div>

        <!-- Campus Lost Item Recovery Guidelines -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-3.5">
          <div class="flex items-center gap-2">
            <Building2 class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
              {{ t('reportWizard.lostPropertyTips') }}
            </h3>
          </div>

          <ul class="text-xs text-slate-600 dark:text-slate-400 space-y-2.5">
            <li class="flex items-start gap-2">
              <ShieldAlert class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97] shrink-0 mt-0.5" />
              <span>{{ t('reportWizard.lostTip1') }}</span>
            </li>
            <li class="flex items-start gap-2">
              <Clock class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" />
              <span>{{ t('reportWizard.lostTip2') }}</span>
            </li>
            <li class="flex items-start gap-2">
              <Info class="h-4 w-4 text-sky-500 shrink-0 mt-0.5" />
              <span>{{ t('reportWizard.lostTip3') }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Duplicate Item Warning Modal (FR-63) -->
    <AppModal
      :open="duplicateWarningModalOpen"
      :title="t('reportWizard.duplicateWarning')"
      size="md"
      @close="duplicateWarningModalOpen = false"
    >
      <div class="space-y-4 text-xs">
        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-300 font-medium leading-relaxed">
          {{ duplicateWarningText }}
        </div>
        <p class="text-slate-600 dark:text-slate-400">
          {{ t('reportWizard.duplicateWarningDesc') }}
        </p>
      </div>

      <template #footer>
        <div class="flex items-center justify-between w-full">
          <AppButton variant="outline" size="sm" @click="duplicateWarningModalOpen = false">
            {{ t('common.cancel') }}
          </AppButton>
          <div class="flex items-center gap-2">
            <RouterLink to="/browse" target="_blank">
              <AppButton variant="secondary" size="sm">
                {{ t('nav.browse') }}
              </AppButton>
            </RouterLink>
            <AppButton variant="primary" size="sm" @click="confirmDuplicateSubmission">
              {{ t('common.confirm') }}
            </AppButton>
          </div>
        </div>
      </template>
    </AppModal>
  </div>
</template>
