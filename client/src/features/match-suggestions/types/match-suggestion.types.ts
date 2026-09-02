import type { Item } from '@/features/items/types/item.types'
import type { PaginatedResponse, PaginationParams } from '@/types/common.types'

export type MatchSuggestionStatus = 'pending' | 'confirmed' | 'dismissed'

export interface MatchSuggestion {
  id: number
  lost_item_id: number
  found_item_id: number
  score: number
  category_score?: number
  text_score?: number
  location_score?: number
  algorithm_version?: string
  status: MatchSuggestionStatus
  reviewed_by: number | null
  reviewed_at: string | null
  lost_item?: Item
  found_item?: Item
  created_at: string
  updated_at: string
}

export interface MatchSuggestionListParams extends PaginationParams {
  status?: MatchSuggestionStatus | ''
  min_score?: number
}

export type MatchSuggestionListResponse = PaginatedResponse<MatchSuggestion>
