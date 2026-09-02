<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useItemsStore } from '@/features/items/stores/items.store'
import { validateFoundItemForm } from '@/features/items/validation/item.validation'
import { useReferenceData } from '@/features/lookups/composables/useReferenceData'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage, getValidationErrors } from '@/utils/error-handler'
import { toISODateInput, formatDate } from '@/utils/date'
import { currentLocale, t } from '@/i18n'
import type { ItemHeldAt } from '@/features/items/types/item.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import AppButton from '@/components/ui/AppButton.vue'
import FormMultiImageUpload from '@/components/forms/FormMultiImageUpload.vue'

import { checkDuplicate } from '@/features/items/api/items.api'
import AppModal from '@/components/ui/AppModal.vue'

const router = useRouter()
const itemsStore = useItemsStore()
const uiStore = useUiStore()
const { categories, locations } = useReferenceData()

const currentStep = ref(1)

const form = reactive({
  title: '',
  description: '',
  category_id: '' as number | '',
  location_id: '' as number | '',
  location_detail: '',
  campus_id: undefined as number | undefined,
  held_at: 'security_office' as ItemHeldAt,
  brand: '',
  color: '',
  serial_number: '',
  incident_date: toISODateInput(),
  incident_time: '',
  photos: [] as File[],
})

const heldAtOptions = computed(() => [
  { label: t('custody.securityVault'), value: 'security_office' },
  { label: t('custody.withFinder'), value: 'with_finder' },
  { label: t('custody.otherLocation'), value: 'unknown' },
])


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

const selectedHeldAtLabel = computed(() => {
  const match = heldAtOptions.value.find(h => h.value === form.held_at)
  return match ? match.label : form.held_at.replace('_', ' ')
})

async function handleSubmit() {
  generalError.value = null
  const allErrors = validateFoundItemForm(form as any)
  if (Object.keys(allErrors).length > 0) {
    errors.value = allErrors
    const step1Fields = ['title', 'description', 'category_id', 'incident_date']
    const hasStep1Error = Object.keys(allErrors).some(f => step1Fields.includes(f))
    currentStep.value = hasStep1Error ? 1 : 2
    return
  }

  submitting.value = true

  // FR-63: Pre-submission Duplicate Check
  if (!proceedWithDuplicate.value && form.category_id) {
    try {
      const dup = await checkDuplicate({
        category_id: Number(form.category_id),
        campus_id: form.campus_id,
        serial_number: form.serial_number || undefined,
      })

      if (dup.duplicate_found) {
        duplicateWarningText.value = dup.message || t('reportWizard.duplicateWarningDesc')
        duplicateWarningModalOpen.value = true
        submitting.value = false
        return
      }
    } catch {
      // Non-blocking fallback
    }
  }

  try {
    const item = await itemsStore.createFoundItem(form as any)
    uiStore.success(t('items.createdSuccess'))
    router.push(`/items/${item.id}`)
  } catch (err) {
    const fieldErrors = getValidationErrors(err)
    if (fieldErrors) {
      errors.value = Object.entries(fieldErrors).reduce((acc, [k, v]) => {
        acc[k] = Array.isArray(v) ? v[0] : v
        return acc
      }, {} as Record<string, string>)
      const step1Fields = ['title', 'description', 'category_id', 'incident_date']
      const hasStep1Error = Object.keys(errors.value).some(f => step1Fields.includes(f))
      currentStep.value = hasStep1Error ? 1 : 2
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
  <div class="max-w-3xl mx-auto space-y-4 sm:space-y-5">
      <!-- Title -->
      <div>
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ t('items.reportFound') }}</h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
          {{ t('browse.subtitle') }}
        </p>
      </div>

      <!-- Step Indicator -->
      <div class="flex items-center justify-between p-3.5 sm:p-4 bg-white dark:bg-[#111827] rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-2xs transition-colors duration-150">
        <div class="flex items-center gap-3">
          <div :class="['h-8 w-8 rounded-full flex items-center justify-center text-xs font-black transition-colors', currentStep >= 1 ? 'bg-[#0B5D3B] text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400']">
            1
          </div>
          <span :class="['text-xs font-bold', currentStep >= 1 ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500']">{{ t('reportWizard.basicInfo') }}</span>
        </div>

        <div class="h-0.5 flex-1 mx-4 bg-slate-200 dark:bg-slate-800" />

        <div class="flex items-center gap-3">
          <div :class="['h-8 w-8 rounded-full flex items-center justify-center text-xs font-black transition-colors', currentStep >= 2 ? 'bg-[#0B5D3B] text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400']">
            2
          </div>
          <span :class="['text-xs font-bold', currentStep >= 2 ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500']">{{ t('reportWizard.custodyAndDetails') }}</span>
        </div>

        <div class="h-0.5 flex-1 mx-4 bg-slate-200 dark:bg-slate-800" />

        <div class="flex items-center gap-3">
          <div :class="['h-8 w-8 rounded-full flex items-center justify-center text-xs font-black transition-colors', currentStep === 3 ? 'bg-[#0B5D3B] text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400']">
            3
          </div>
          <span :class="['text-xs font-bold', currentStep === 3 ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500']">{{ t('reportWizard.reviewAndSubmit') }}</span>
        </div>
      </div>

      <!-- General Error Notice -->
      <div v-if="generalError" class="p-3.5 sm:p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-400 font-bold">
        {{ generalError }}
      </div>

      <!-- Step 1: Basic Information -->
      <div v-if="currentStep === 1" class="bg-white dark:bg-[#111827] p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-4 transition-colors duration-150">
        <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white pb-2.5 border-b border-slate-100 dark:border-slate-800">{{ t('reportWizard.step1') }}</h2>

        <AppInput
          :label="t('items.form.title') + ' *'"
          placeholder="e.g. Scientific Calculator Casio fx-991EX"
          :model-value="form.title"
          :error="errors.title"
          required
          :maxlength="150"
          @update:model-value="form.title = $event"
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <AppSelect
            :label="t('items.myItems.category') + ' *'"
            :placeholder="t('items.myItems.category')"
            :options="categoryOptions"
            :model-value="form.category_id"
            :error="errors.category_id"
            required
            @update:model-value="form.category_id = Number($event)"
          />

          <AppInput
            :label="t('reportWizard.dateFound') + ' *'"
            type="date"
            :model-value="form.incident_date"
            :error="errors.incident_date"
            required
            @update:model-value="form.incident_date = $event"
          />
        </div>

        <AppTextarea
          :label="t('items.form.description') + ' *'"
          :placeholder="t('items.form.description')"
          :rows="4"
          :model-value="form.description"
          :error="errors.description"
          required
          :maxlength="2000"
          @update:model-value="form.description = $event"
        />

        <div class="flex justify-end pt-3">
          <AppButton variant="primary" size="md" @click="nextStep">
            {{ t('reportWizard.custodyAndDetails') }} &rarr;
          </AppButton>
        </div>
      </div>

      <!-- Step 2: Custody, Location & Photos -->
      <div v-else-if="currentStep === 2" class="bg-white dark:bg-[#111827] p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-4 transition-colors duration-150">
        <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white pb-2.5 border-b border-slate-100 dark:border-slate-800">{{ t('reportWizard.step2Found') }}</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <AppSelect
            :label="t('reportWizard.foundLocation')"
            :placeholder="t('nav.locations')"
            :options="locationOptions"
            :model-value="form.location_id"
            :error="errors.location_id"
            @update:model-value="handleLocationChange"
          />

          <AppSelect
            :label="t('reportWizard.custodyLocation') + ' *'"
            :options="heldAtOptions"
            :model-value="form.held_at"
            :error="errors.held_at"
            required
            @update:model-value="form.held_at = $event as ItemHeldAt"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <AppInput
            :label="t('reportWizard.brandModel')"
            :placeholder="t('reportWizard.brandModel')"
            :model-value="form.brand"
            :error="errors.brand"
            @update:model-value="form.brand = $event"
          />

          <AppInput
            :label="t('reportWizard.color')"
            :placeholder="t('reportWizard.color')"
            :model-value="form.color"
            :error="errors.color"
            @update:model-value="form.color = $event"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <AppInput
            :label="t('reportWizard.serialNumber')"
            :placeholder="t('reportWizard.serialNumber')"
            :model-value="form.serial_number"
            :error="errors.serial_number"
            @update:model-value="form.serial_number = $event"
          />

          <AppInput
            :label="t('items.form.locationDetail')"
            placeholder="e.g. Near table #4, 2nd floor hallway"
            :model-value="form.location_detail"
            :error="errors.location_detail"
            @update:model-value="form.location_detail = $event"
          />
        </div>

        <div class="pt-2">
          <FormMultiImageUpload
            :label="t('reportWizard.uploadPhotos')"
            :max-files="3"
            @files-updated="form.photos = $event"
          />
          <p v-if="errors.photos" class="text-xs text-rose-600 dark:text-rose-400 font-medium mt-1">
            {{ errors.photos }}
          </p>
        </div>

        <div class="flex items-center justify-between pt-3">
          <AppButton variant="outline" size="md" @click="prevStep">
            &larr; {{ t('common.back') }}
          </AppButton>
          <AppButton variant="primary" size="md" @click="nextStep">
            {{ t('reportWizard.reviewAndSubmit') }} &rarr;
          </AppButton>
        </div>
      </div>

      <!-- Step 3: Review & Submit -->
      <div v-else-if="currentStep === 3" class="bg-white dark:bg-[#111827] p-6 sm:p-8 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-6 transition-colors duration-150">
        <h2 class="text-base font-bold text-slate-900 dark:text-white pb-2 border-b border-slate-100 dark:border-slate-800">{{ t('reportWizard.step3Found') }}</h2>

        <div class="space-y-4 text-xs">
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
            <div>
              <span class="text-slate-400 dark:text-slate-400 block font-semibold">{{ t('reportWizard.foundTitle') }}:</span>
              <p class="text-sm font-bold text-slate-900 dark:text-white">{{ form.title }}</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <div>
                <span class="text-slate-400 dark:text-slate-400 font-semibold block">{{ t('items.myItems.category') }}:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ selectedCategoryName }}</span>
              </div>
              <div>
                <span class="text-slate-400 dark:text-slate-400 font-semibold block">{{ t('reportWizard.dateFound') }}:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ formatDate(form.incident_date) }}</span>
              </div>
              <div>
                <span class="text-slate-400 dark:text-slate-400 font-semibold block">{{ t('reportWizard.foundAt') }}:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ selectedLocationName }}</span>
              </div>
            </div>

            <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60">
              <span class="text-slate-400 dark:text-slate-400 font-semibold block">{{ t('reportWizard.custodyHeldAt') }}:</span>
              <span class="font-bold text-[#0B5D3B] dark:text-[#75bd97] uppercase">{{ selectedHeldAtLabel }}</span>
            </div>

            <div v-if="form.brand || form.color || form.serial_number" class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2 border-t border-slate-200/60 dark:border-slate-700/60">
              <div v-if="form.brand">
                <span class="text-slate-400 dark:text-slate-400 font-semibold block">{{ t('reportWizard.brandModel') }}:</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ form.brand }}</span>
              </div>
              <div v-if="form.color">
                <span class="text-slate-400 dark:text-slate-400 font-semibold block">{{ t('reportWizard.color') }}:</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ form.color }}</span>
              </div>
              <div v-if="form.serial_number">
                <span class="text-slate-400 dark:text-slate-400 font-semibold block">{{ t('reportWizard.serialNumber') }}:</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ form.serial_number }}</span>
              </div>
            </div>

            <div>
              <span class="text-slate-400 dark:text-slate-400 font-semibold block mb-1">{{ t('items.form.description') }}:</span>
              <p class="text-slate-700 dark:text-slate-300 whitespace-pre-line leading-relaxed">{{ form.description }}</p>
            </div>

            <div v-if="form.photos.length > 0">
              <span class="text-slate-400 dark:text-slate-400 font-semibold block mb-1">{{ t('reportWizard.attachedPhotos') }}:</span>
              <span class="font-bold text-[#0B5D3B] dark:text-[#75bd97]">{{ t('reportWizard.photosAttached', { count: form.photos.length }) }}</span>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
          <AppButton variant="outline" size="md" :disabled="submitting" @click="prevStep">
            &larr; {{ t('common.back') }}
          </AppButton>
          <AppButton variant="primary" size="md" :loading="submitting" @click="handleSubmit">
            {{ t('items.reportFound') }}
          </AppButton>
        </div>
      </div>

      <!-- Duplicate Item Warning Modal (FR-63) -->
      <AppModal
        :open="duplicateWarningModalOpen"
        :title="t('reportWizard.duplicateFoundWarning')"
        size="md"
        @close="duplicateWarningModalOpen = false"
      >
        <div class="space-y-4 text-xs">
          <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-300 font-medium leading-relaxed">
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
