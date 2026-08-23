import { reactive } from 'vue'
import type { ItemType, ItemStatus } from '../types/item.types'

export interface ItemFilterState {
  search: string
  type: ItemType | ''
  status: ItemStatus | ''
  category_id: number | ''
  campus_id: number | ''
  location_id: number | ''
  page: number
}

export function useItemFilters(initialState: Partial<ItemFilterState> = {}) {
  const filters = reactive<ItemFilterState>({
    search: '',
    type: '',
    status: '',
    category_id: '',
    campus_id: '',
    location_id: '',
    page: 1,
    ...initialState,
  })

  function resetFilters(): void {
    filters.search = ''
    filters.type = ''
    filters.status = ''
    filters.category_id = ''
    filters.campus_id = ''
    filters.location_id = ''
    filters.page = 1
  }

  function setPage(page: number): void {
    filters.page = page
  }

  return {
    filters,
    resetFilters,
    setPage,
  }
}
