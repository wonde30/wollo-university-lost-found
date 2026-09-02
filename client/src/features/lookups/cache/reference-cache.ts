import type { Category, Location, Campus, OrganizationalUnit, OrganizationalUnitType } from '@/types/common.types'

export interface ReferenceDataCache {
  categories: Category[] | null
  locations: Location[] | null
  campuses: Campus[] | null
  organizationalUnits: OrganizationalUnit[] | null
  organizationalUnitTypes: OrganizationalUnitType[] | null
  timestamp: number
}

const CACHE_TTL_MS = 30 * 60 * 1000 // 30 minutes

let memoryCache: ReferenceDataCache = {
  categories: null,
  locations: null,
  campuses: null,
  organizationalUnits: null,
  organizationalUnitTypes: null,
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
    organizationalUnits: null,
    organizationalUnitTypes: null,
    timestamp: 0,
  }
}
