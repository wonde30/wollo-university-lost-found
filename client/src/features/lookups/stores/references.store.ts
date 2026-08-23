import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Category, Location, Campus, Department } from '@/types/common.types'
import * as lookupsApi from '../api/lookups.api'
import * as adminApi from '@/features/admin/api/admin.api'
import { getCachedReferenceData, setCachedReferenceData, invalidateReferenceCache } from '../cache/reference-cache'

const DEFAULT_CAMPUSES: Campus[] = [
  {
    id: 1,
    name: 'Dessie Main Campus',
    code: 'DMC',
    address: 'Dessie, Amhara Region',
    description: 'Main University Campus',
    is_active: true,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  },
  {
    id: 2,
    name: 'Kombolcha Institute of Technology',
    code: 'KIoT',
    address: 'Kombolcha, Amhara Region',
    description: 'Institute of Technology',
    is_active: true,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  },
]

export const useReferencesStore = defineStore('references', () => {
  const categories = ref<Category[]>([])
  const locations = ref<Location[]>([])
  const campuses = ref<Campus[]>(DEFAULT_CAMPUSES)
  const departments = ref<Department[]>([])

  const categoriesLoaded = ref(false)
  const locationsLoaded = ref(false)
  const campusesLoaded = ref(false)
  const departmentsLoaded = ref(false)

  const loading = ref(false)
  const initialized = ref(false)

  // Seed with initial cache if available
  const initialCache = getCachedReferenceData()
  if (initialCache) {
    if (initialCache.categories && initialCache.categories.length > 0) {
      categories.value = initialCache.categories
      categoriesLoaded.value = true
    }
    if (initialCache.locations && initialCache.locations.length > 0) {
      locations.value = initialCache.locations
      locationsLoaded.value = true
    }
    if (initialCache.campuses && initialCache.campuses.length > 0) {
      campuses.value = initialCache.campuses
      campusesLoaded.value = true
    }
  }

  async function fetchCampuses(force = false): Promise<Campus[]> {
    if (!force && campusesLoaded.value && campuses.value.length > 0) {
      return campuses.value
    }
    try {
      const res = await adminApi.getCampuses()
      const list = Array.isArray(res) ? res : (res as any).data || []
      if (list.length > 0) {
        campuses.value = list
        campusesLoaded.value = true
        setCachedReferenceData({ campuses: list })
      }
    } catch {
      if (campuses.value.length === 0) {
        campuses.value = DEFAULT_CAMPUSES
      }
    }
    return campuses.value
  }

  async function fetchCategories(force = false): Promise<Category[]> {
    if (!force && categoriesLoaded.value && categories.value.length > 0) {
      return categories.value
    }
    try {
      const data = await lookupsApi.getCategories()
      categories.value = data
      categoriesLoaded.value = true
      setCachedReferenceData({ categories: data })
      return data
    } catch {
      return categories.value
    }
  }

  async function fetchLocations(force = false): Promise<Location[]> {
    if (!force && locationsLoaded.value && locations.value.length > 0) {
      return locations.value
    }
    try {
      const data = await lookupsApi.getLocations()
      locations.value = data
      locationsLoaded.value = true
      setCachedReferenceData({ locations: data })
      return data
    } catch {
      return locations.value
    }
  }

  async function fetchReferences(force: boolean = false): Promise<void> {
    if (!force && initialized.value) return

    const cached = getCachedReferenceData()
    if (!force && cached && cached.categories && cached.locations) {
      categories.value = cached.categories
      locations.value = cached.locations
      if (cached.campuses) campuses.value = cached.campuses
      categoriesLoaded.value = true
      locationsLoaded.value = true
      initialized.value = true
      return
    }

    if (categories.value.length === 0 || locations.value.length === 0) {
      loading.value = true
    }

    try {
      const [categoriesRes, locationsRes] = await Promise.all([
        fetchCategories(force),
        fetchLocations(force),
        fetchCampuses(force),
      ])

      categories.value = categoriesRes
      locations.value = locationsRes
      categoriesLoaded.value = true
      locationsLoaded.value = true
      initialized.value = true

      setCachedReferenceData({
        categories: categoriesRes,
        locations: locationsRes,
        campuses: campuses.value,
      })
    } finally {
      loading.value = false
    }
  }

  // ==========================================
  // Location CRUD
  // ==========================================

  async function createLocation(data: Partial<Location>): Promise<Location> {
    const newLoc = await adminApi.createLocation(data)
    locations.value.unshift(newLoc)
    invalidateReferenceCache()
    return newLoc
  }

  async function updateLocation(id: number, data: Partial<Location>): Promise<Location> {
    const updated = await adminApi.updateLocation(id, data)
    const index = locations.value.findIndex(l => l.id === id)
    if (index !== -1) locations.value[index] = updated
    invalidateReferenceCache()
    return updated
  }

  async function deleteLocation(id: number): Promise<void> {
    await adminApi.deleteLocation(id)
    locations.value = locations.value.filter(l => l.id !== id)
    invalidateReferenceCache()
  }

  // ==========================================
  // Campus CRUD
  // ==========================================

  async function createCampus(data: Partial<Campus>): Promise<Campus> {
    const newCampus = await adminApi.createCampus(data)
    campuses.value.unshift(newCampus)
    invalidateReferenceCache()
    return newCampus
  }

  async function updateCampus(id: number, data: Partial<Campus>): Promise<Campus> {
    const updated = await adminApi.updateCampus(id, data)
    const index = campuses.value.findIndex(c => c.id === id)
    if (index !== -1) campuses.value[index] = updated
    invalidateReferenceCache()
    return updated
  }

  async function deleteCampus(id: number): Promise<void> {
    await adminApi.deleteCampus(id)
    const index = campuses.value.findIndex(c => c.id === id)
    if (index !== -1) campuses.value[index].is_active = false
    invalidateReferenceCache()
  }

  async function restoreCampus(id: number): Promise<Campus> {
    const restored = await adminApi.restoreCampus(id)
    const index = campuses.value.findIndex(c => c.id === id)
    if (index !== -1) campuses.value[index] = restored
    invalidateReferenceCache()
    return restored
  }

  // ==========================================
  // Category CRUD
  // ==========================================

  async function createCategory(data: Partial<Category>): Promise<Category> {
    const newCat = await adminApi.createCategory(data)
    categories.value.unshift(newCat)
    invalidateReferenceCache()
    return newCat
  }

  async function updateCategory(id: number, data: Partial<Category>): Promise<Category> {
    const updated = await adminApi.updateCategory(id, data)
    const index = categories.value.findIndex(c => c.id === id)
    if (index !== -1) categories.value[index] = updated
    invalidateReferenceCache()
    return updated
  }

  async function deleteCategory(id: number): Promise<void> {
    await adminApi.deleteCategory(id)
    categories.value = categories.value.filter(c => c.id !== id)
    invalidateReferenceCache()
  }

  function invalidate(): void {
    invalidateReferenceCache()
    categoriesLoaded.value = false
    locationsLoaded.value = false
    campusesLoaded.value = false
    departmentsLoaded.value = false
    initialized.value = false
  }

  return {
    categories,
    locations,
    campuses,
    departments,
    categoriesLoaded,
    locationsLoaded,
    campusesLoaded,
    departmentsLoaded,
    loading,
    initialized,
    fetchCampuses,
    fetchCategories,
    fetchLocations,
    fetchReferences,
    fetchAll: fetchReferences,
    createLocation,
    updateLocation,
    deleteLocation,
    createCampus,
    updateCampus,
    deleteCampus,
    restoreCampus,
    createCategory,
    updateCategory,
    deleteCategory,
    invalidate,
  }
})
