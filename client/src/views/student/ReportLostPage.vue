<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useItemsStore } from '@/features/items/stores/items.store'
import { validateLostItemForm } from '@/features/items/validation/item.validation'
import { useReferenceData } from '@/features/lookups/composables/useReferenceData'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage, getValidationErrors } from '@/utils/error-handler'
import { toISODateInput, formatDate } from '@/utils/date'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppCheckbox from '@/components/ui/AppCheckbox.vue'
import FormMultiImageUpload from '@/components/forms/FormMultiImageUpload.vue'

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

function validateStep1(): boolean {
  errors.value = {}
  if (!form.title.trim()) {
    errors.value.title = 'Title is required (e.g. Blue Dell Laptop).'
  } else if (form.title.trim().length < 5) {
    errors.value.title = 'Title must be at least 5 characters.'
  }

  if (!form.description.trim()) {
    errors.value.description = 'Please describe the item in detail.'
  } else if (form.description.trim().length < 15) {
    errors.value.description = 'Description must be at least 15 characters.'
  }

  if (!form.category_id) {
    errors.value.category_id = 'Please select a category.'
  }

  if (!form.incident_date) {
    errors.value.incident_date = 'Date lost is required.'
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

const selectedCategoryName = computed(() => {
  return categories.value.find(c => c.id === form.category_id)?.name || 'General'
})

const selectedLocationName = computed(() => {
  return locations.value.find(l => l.id === form.location_id)?.name || 'Campus Grounds'
})

async function handleSubmit() {
  generalError.value = null
  const allErrors = validateLostItemForm(form as any)
  if (Object.keys(allErrors).length > 0) {
    errors.value = allErrors
    currentStep.value = 1
    return
  }

  submitting.value = true
  try {
    const item = await itemsStore.createLostItem(form as any)
    uiStore.success('Lost item report filed successfully.')
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
      generalError.value = getErrorMessage(err, 'Failed to submit report. Please try again.')
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <DashboardLayout>
    <div class="max-w-3xl mx-auto space-y-6">
      <!-- Title -->
      <div>
        <h1 class="text-2xl font-black text-slate-900">Report a Lost Item</h1>
        <p class="text-xs text-slate-500 mt-1">
          Provide accurate details to help campus security and community members identify your belongings.
        </p>
      </div>

      <!-- Step Indicator -->
      <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-3">
          <div :class="['h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors', currentStep >= 1 ? 'bg-[#0F5132] text-white' : 'bg-slate-100 text-slate-400']">
            1
          </div>
          <span :class="['text-xs font-bold', currentStep >= 1 ? 'text-slate-900' : 'text-slate-400']">Basic Info</span>
        </div>

        <div class="h-0.5 flex-1 mx-4 bg-slate-200" />

        <div class="flex items-center gap-3">
          <div :class="['h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors', currentStep >= 2 ? 'bg-[#0F5132] text-white' : 'bg-slate-100 text-slate-400']">
            2
          </div>
          <span :class="['text-xs font-bold', currentStep >= 2 ? 'text-slate-900' : 'text-slate-400']">Location & Photos</span>
        </div>

        <div class="h-0.5 flex-1 mx-4 bg-slate-200" />

        <div class="flex items-center gap-3">
          <div :class="['h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors', currentStep === 3 ? 'bg-[#0F5132] text-white' : 'bg-slate-100 text-slate-400']">
            3
          </div>
          <span :class="['text-xs font-bold', currentStep === 3 ? 'text-slate-900' : 'text-slate-400']">Review & Submit</span>
        </div>
      </div>

      <!-- General Error Notice -->
      <div v-if="generalError" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-medium">
        {{ generalError }}
      </div>

      <!-- Step 1: Basic Information -->
      <div v-if="currentStep === 1" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs space-y-5">
        <h2 class="text-base font-bold text-slate-900 pb-2 border-b border-slate-100">Step 1: Item Overview</h2>

        <AppInput
          label="Item Title *"
          placeholder="e.g. Black Leather Wallet with Student ID"
          :model-value="form.title"
          :error="errors.title"
          required
          :maxlength="150"
          @update:model-value="form.title = $event"
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <AppSelect
            label="Item Category *"
            placeholder="Select Category"
            :options="categories.map(c => ({ label: c.name, value: c.id }))"
            :model-value="form.category_id"
            :error="errors.category_id"
            required
            @update:model-value="form.category_id = Number($event)"
          />

          <AppInput
            label="Date Lost *"
            type="date"
            :model-value="form.incident_date"
            :error="errors.incident_date"
            required
            @update:model-value="form.incident_date = $event"
          />
        </div>

        <AppTextarea
          label="Detailed Description *"
          placeholder="Describe distinctive markings, contents, color shades, stickers, or serial details..."
          :rows="4"
          :model-value="form.description"
          :error="errors.description"
          required
          :maxlength="2000"
          @update:model-value="form.description = $event"
        />

        <div class="flex justify-end pt-3">
          <AppButton variant="primary" size="md" @click="nextStep">
            Next: Location & Photos &rarr;
          </AppButton>
        </div>
      </div>

      <!-- Step 2: Location & Additional Details -->
      <div v-else-if="currentStep === 2" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs space-y-5">
        <h2 class="text-base font-bold text-slate-900 pb-2 border-b border-slate-100">Step 2: Location & Identification Photos</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <AppSelect
            label="Last Known Location"
            placeholder="Select Campus Location"
            :options="locations.map(l => ({ label: l.name, value: l.id }))"
            :model-value="form.location_id"
            @update:model-value="form.location_id = Number($event)"
          />

          <AppInput
            label="Brand / Manufacturer"
            placeholder="e.g. Dell, Apple, Samsung, Nike"
            :model-value="form.brand"
            @update:model-value="form.brand = $event"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <AppInput
            label="Primary Color"
            placeholder="e.g. Matte Black, Navy Blue, Silver"
            :model-value="form.color"
            @update:model-value="form.color = $event"
          />

          <AppInput
            label="Serial Number / Identifier (Optional)"
            placeholder="e.g. SN-8923478912"
            :model-value="form.serial_number"
            @update:model-value="form.serial_number = $event"
          />
        </div>

        <div class="pt-2">
          <FormMultiImageUpload
            label="Upload Reference Photos (Up to 5)"
            :max-files="5"
            @files-updated="form.photos = $event"
          />
        </div>

        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
          <AppCheckbox
            label="High-Value Item"
            description="Check this box if this item contains laptops, passports, jewelry, or critical credentials requiring elevated security verification."
            :model-value="form.is_high_value"
            @update:model-value="form.is_high_value = $event"
          />
        </div>

        <div class="flex items-center justify-between pt-3">
          <AppButton variant="outline" size="md" @click="prevStep">
            &larr; Back
          </AppButton>
          <AppButton variant="primary" size="md" @click="nextStep">
            Next: Review Report &rarr;
          </AppButton>
        </div>
      </div>

      <!-- Step 3: Review & Submit -->
      <div v-else-if="currentStep === 3" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs space-y-6">
        <h2 class="text-base font-bold text-slate-900 pb-2 border-b border-slate-100">Step 3: Review & Finalize Submission</h2>

        <div class="space-y-4 text-xs">
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
            <div>
              <span class="text-slate-400 block font-semibold">Report Title:</span>
              <p class="text-sm font-bold text-slate-900">{{ form.title }}</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <div>
                <span class="text-slate-400 font-semibold block">Category:</span>
                <span class="font-bold text-slate-800">{{ selectedCategoryName }}</span>
              </div>
              <div>
                <span class="text-slate-400 font-semibold block">Date Lost:</span>
                <span class="font-bold text-slate-800">{{ formatDate(form.incident_date) }}</span>
              </div>
              <div>
                <span class="text-slate-400 font-semibold block">Location:</span>
                <span class="font-bold text-slate-800">{{ selectedLocationName }}</span>
              </div>
            </div>

            <div v-if="form.brand || form.color" class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-200/60">
              <div v-if="form.brand">
                <span class="text-slate-400 font-semibold block">Brand:</span>
                <span class="font-bold text-slate-800">{{ form.brand }}</span>
              </div>
              <div v-if="form.color">
                <span class="text-slate-400 font-semibold block">Color:</span>
                <span class="font-bold text-slate-800">{{ form.color }}</span>
              </div>
            </div>

            <div>
              <span class="text-slate-400 font-semibold block mb-1">Description:</span>
              <p class="text-slate-700 whitespace-pre-line leading-relaxed">{{ form.description }}</p>
            </div>

            <div v-if="form.photos.length > 0">
              <span class="text-slate-400 font-semibold block mb-1">Attached Photos:</span>
              <span class="font-bold text-[#0F5132]">{{ form.photos.length }} photo(s) selected</span>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
          <AppButton variant="outline" size="md" :disabled="submitting" @click="prevStep">
            &larr; Edit Details
          </AppButton>
          <AppButton variant="primary" size="md" :loading="submitting" @click="handleSubmit">
            Submit Lost Report
          </AppButton>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
