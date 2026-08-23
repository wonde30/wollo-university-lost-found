/**
 * Frontend validation that mirrors backend rules exactly.
 *
 * StoreLostItemRequest / StoreFoundItemRequest rules:
 *   title:          required, string, min:5, max:150
 *   description:    required, string, min:20, max:2000
 *   category_id:    required, integer, exists:categories,id
 *   campus_id:      nullable
 *   location_id:    nullable
 *   brand:          nullable, max:80
 *   color:          nullable, max:60
 *   serial_number:  nullable, max:100
 *   incident_date:  required, date, before_or_equal:today
 *   incident_time:  nullable, format HH:MM
 *   estimated_value:nullable, numeric, min:0
 *   is_high_value:  nullable, boolean
 *   photos:         nullable, array, max:3
 *   photos.*:       file, mimes:jpeg,png,webp, max:5120 (5 MB each)
 */

import type { StoreLostItemData, StoreFoundItemData } from '../types/item.types'

type FormErrors = Record<string, string>

const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
const MAX_PHOTO_SIZE_BYTES = 5 * 1024 * 1024 // 5 MB
const MAX_PHOTO_COUNT = 3

function today(): string {
  return new Date().toISOString().split('T')[0]
}

function validateCommonFields(
  form: Partial<StoreLostItemData | StoreFoundItemData>,
  errors: FormErrors,
): void {
  // title: required, min:5, max:150
  if (!form.title || !form.title.trim()) {
    errors.title = 'Item title is required.'
  } else if (form.title.trim().length < 5) {
    errors.title = `Title is too short — minimum 5 characters (${form.title.trim().length}/5).`
  } else if (form.title.trim().length > 150) {
    errors.title = `Title is too long — maximum 150 characters (${form.title.trim().length}/150).`
  }

  // description: required, min:20, max:2000
  if (!form.description || !form.description.trim()) {
    errors.description = 'Description is required.'
  } else if (form.description.trim().length < 20) {
    errors.description = `Description is too short — minimum 20 characters (${form.description.trim().length}/20).`
  } else if (form.description.trim().length > 2000) {
    errors.description = `Description is too long — maximum 2000 characters (${form.description.trim().length}/2000).`
  }

  // category_id: required
  if (!form.category_id) {
    errors.category_id = 'Please select a category.'
  }

  // incident_date: required, before_or_equal:today
  if (!form.incident_date) {
    errors.incident_date = 'Date is required.'
  } else if (form.incident_date > today()) {
    errors.incident_date = 'Date cannot be in the future.'
  }

  // photos: max:3, mimes:jpeg,png,webp, max:5120 each
  if (Array.isArray(form.photos) && form.photos.length > 0) {
    if (form.photos.length > MAX_PHOTO_COUNT) {
      errors.photos = `You may upload a maximum of ${MAX_PHOTO_COUNT} photos.`
    } else {
      for (const file of form.photos) {
        if (!ALLOWED_MIME_TYPES.includes(file.type)) {
          errors.photos = `"${file.name}" is not a valid image. Allowed: JPEG, PNG, WebP.`
          break
        }
        if (file.size > MAX_PHOTO_SIZE_BYTES) {
          errors.photos = `"${file.name}" exceeds the 5 MB size limit.`
          break
        }
      }
    }
  }
}

export function validateLostItemForm(form: Partial<StoreLostItemData>): FormErrors {
  const errors: FormErrors = {}
  validateCommonFields(form, errors)
  return errors
}

export function validateFoundItemForm(form: Partial<StoreFoundItemData>): FormErrors {
  const errors: FormErrors = {}
  validateCommonFields(form, errors)
  return errors
}
