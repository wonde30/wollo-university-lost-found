import type { Category, Location, Campus, Department } from '@/types/common.types'

export interface ReferenceDataCache {
  categories: Category[] | null
  locations: Location[] | null
  campuses: Campus[] | null
  departments: Department[] | null
  timestamp: number
}

const CACHE_TTL_MS = 30 * 60 * 1000 // 30 minutes

let memoryCache: ReferenceDataCache = {
  categories: null,
  locations: null,
  campuses: null,
  departments: null,
  timestamp: 0,
}

export function getCachedReferenceData(): ReferenceDataCache | null {
  if (Date.now() - memoryCache.timestamp < CACHE_TTL_MS && memoryCache.categories && memoryCache.locations) {
    return memoryCache
  }
  return null
}

export function setCachedReferenceData(data: Partial<ReferenceDataCache>): void {
  memoryCache = {
    ...memoryCache,
    ...data,
    timestamp: Date.now(),
  }
}

export function invalidateReferenceCache(): void {
  memoryCache = {
    categories: null,
    locations: null,
    campuses: null,
    departments: null,
    timestamp: 0,
  }
}
