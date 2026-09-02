import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Category, Location, Campus, OrganizationalUnit, OrganizationalUnitType } from '@/types/common.types'
import * as lookupsApi from '../api/lookups.api'
import * as adminApi from '@/features/admin/api/admin.api'
import { getCachedReferenceData, setCachedReferenceData, invalidateReferenceCache } from '../cache/reference-cache'

export const useReferencesStore = defineStore('references', () => {
  const categories = ref<Category[]>([])
  const locations = ref<Location[]>([])
  const campuses = ref<Campus[]>([])
  const organizationalUnits = ref<OrganizationalUnit[]>([])
  const organizationalUnitTypes = ref<OrganizationalUnitType[]>([])

  const categoriesLoaded = ref(false)
  const locationsLoaded = ref(false)
  const campusesLoaded = ref(false)
  const organizationalUnitsLoaded = ref(false)
  const organizationalUnitTypesLoaded = ref(false)

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
    if (initialCache.organizationalUnits && initialCache.organizationalUnits.length > 0) {
      organizationalUnits.value = initialCache.organizationalUnits
      organizationalUnitsLoaded.value = true
    }
    if (initialCache.organizationalUnitTypes && initialCache.organizationalUnitTypes.length > 0) {
      organizationalUnitTypes.value = initialCache.organizationalUnitTypes
      organizationalUnitTypesLoaded.value = true
    }
  }

  let _fetchingCampuses = false
  let _fetchingUnits = false
  let _fetchingUnitTypes = false
  let _fetchingCategories = false
  let _fetchingLocations = false
  let _referencesPromise: Promise<void> | null = null

  async function fetchCampuses(force = false): Promise<Campus[]> {
    if (!force && campuses.value.length > 0) return campuses.value
    if (_fetchingCampuses) return campuses.value
    _fetchingCampuses = true
    try {
      const res = await adminApi.getCampuses({ all: true })
      const list = Array.isArray(res) ? res : (res as any).data || []
      campuses.value = list
      campusesLoaded.value = true
      setCachedReferenceData({ campuses: list })
    } finally {
      _fetchingCampuses = false
    }
    return campuses.value
  }

  async function fetchOrganizationalUnits(force = false): Promise<OrganizationalUnit[]> {
    if (!force && organizationalUnits.value.length > 0) return organizationalUnits.value
    if (_fetchingUnits) return organizationalUnits.value
    _fetchingUnits = true
    try {
      const res = await adminApi.getOrganizationalUnits({ all: true })
      const list = Array.isArray(res) ? res : (res as any).data || []
      organizationalUnits.value = list
      organizationalUnitsLoaded.value = true
      setCachedReferenceData({ organizationalUnits: list })
    } finally {
      _fetchingUnits = false
    }
    return organizationalUnits.value
  }

  async function fetchOrganizationalUnitTypes(force = false): Promise<OrganizationalUnitType[]> {
    if (!force && organizationalUnitTypes.value.length > 0) return organizationalUnitTypes.value
    if (_fetchingUnitTypes) return organizationalUnitTypes.value
    _fetchingUnitTypes = true
    try {
      const res = await adminApi.getOrganizationalUnitTypes({ all: true })
      const list = Array.isArray(res) ? res : (res as any).data || []
      organizationalUnitTypes.value = list
      organizationalUnitTypesLoaded.value = true
      setCachedReferenceData({ organizationalUnitTypes: list })
    } finally {
      _fetchingUnitTypes = false
    }
    return organizationalUnitTypes.value
  }

  async function fetchCategories(force = false): Promise<Category[]> {
    if (!force && categories.value.length > 0) return categories.value
    if (_fetchingCategories) return categories.value
    _fetchingCategories = true
    try {
      const data = await lookupsApi.getCategories()
      categories.value = data
      categoriesLoaded.value = true
      setCachedReferenceData({ categories: data })
      return data
    } catch {
      return categories.value
    } finally {
      _fetchingCategories = false
    }
  }

  async function fetchLocations(force = false): Promise<Location[]> {
    if (!force && locations.value.length > 0) return locations.value
    if (_fetchingLocations) return locations.value
    _fetchingLocations = true
    try {
      const data = await lookupsApi.getLocations()
      locations.value = data
      locationsLoaded.value = true
      setCachedReferenceData({ locations: data })
      return data
    } catch {
      return locations.value
    } finally {
      _fetchingLocations = false
    }
  }

  /**
   * Fetch core reference data: categories + locations only.
   * Campuses and organizational units are loaded on-demand by specific pages.
   */
  async function fetchReferences(force: boolean = false): Promise<void> {
    if (!force && initialized.value) return
    if (_referencesPromise) return _referencesPromise

    const cached = getCachedReferenceData()
    if (!force && cached && cached.categories && cached.locations) {
      categories.value = cached.categories
      locations.value = cached.locations
      if (cached.campuses) campuses.value = cached.campuses
      if (cached.organizationalUnits) organizationalUnits.value = cached.organizationalUnits
      if (cached.organizationalUnitTypes) organizationalUnitTypes.value = cached.organizationalUnitTypes
      categoriesLoaded.value = true
      locationsLoaded.value = true
      initialized.value = true
      return
    }

    if (categories.value.length === 0 || locations.value.length === 0) {
      loading.value = true
    }

    _referencesPromise = (async () => {
      try {
        const [categoriesRes, locationsRes] = await Promise.all([
          fetchCategories(force),
          fetchLocations(force),
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
          organizationalUnits: organizationalUnits.value,
          organizationalUnitTypes: organizationalUnitTypes.value,
        })
      } finally {
        loading.value = false
        _referencesPromise = null
      }
    })()

    return _referencesPromise
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

  // ==========================================
  // Organizational Unit CRUD
  // ==========================================

  async function createOrganizationalUnit(data: Partial<OrganizationalUnit>): Promise<OrganizationalUnit> {
    const newUnit = await adminApi.createOrganizationalUnit(data)
    organizationalUnits.value.unshift(newUnit)
    invalidateReferenceCache()
    return newUnit
  }

  async function updateOrganizationalUnit(id: number, data: Partial<OrganizationalUnit>): Promise<OrganizationalUnit> {
    const updated = await adminApi.updateOrganizationalUnit(id, data)
    const index = organizationalUnits.value.findIndex(u => u.id === id)
    if (index !== -1) organizationalUnits.value[index] = updated
    invalidateReferenceCache()
    return updated
  }

  async function deleteOrganizationalUnit(id: number): Promise<void> {
    await adminApi.deleteOrganizationalUnit(id)
    organizationalUnits.value = organizationalUnits.value.filter(u => u.id !== id)
    invalidateReferenceCache()
  }

  function invalidate(): void {
    invalidateReferenceCache()
    categoriesLoaded.value = false
    locationsLoaded.value = false
    campusesLoaded.value = false
    organizationalUnitsLoaded.value = false
    organizationalUnitTypesLoaded.value = false
    initialized.value = false
  }

  return {
    categories,
    locations,
    campuses,
    organizationalUnits,
    organizationalUnitTypes,
    categoriesLoaded,
    locationsLoaded,
    campusesLoaded,
    organizationalUnitsLoaded,
    organizationalUnitTypesLoaded,
    loading,
    initialized,
    fetchCampuses,
    fetchOrganizationalUnits,
    fetchOrganizationalUnitTypes,
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
    createOrganizationalUnit,
    updateOrganizationalUnit,
    deleteOrganizationalUnit,
    invalidate,
  }
})
