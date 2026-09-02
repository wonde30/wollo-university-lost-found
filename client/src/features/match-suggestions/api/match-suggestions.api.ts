import { apiClient } from '@/lib/http/client'
import { MATCH_SUGGESTIONS } from '@/lib/api/endpoints'
import type { PaginatedResponse } from '@/types/common.types'
import type { ApiResponse } from '@/lib/api/response'
import type {
  MatchSuggestion,
  MatchSuggestionListParams,
} from '../types/match-suggestion.types'

export async function getMatchSuggestions(
  params?: MatchSuggestionListParams
): Promise<PaginatedResponse<MatchSuggestion>> {
  const { data } = await apiClient.get<PaginatedResponse<MatchSuggestion>>(
    MATCH_SUGGESTIONS.INDEX,
    { params }
  )
  return data
}

export async function updateMatchSuggestion(
  id: number,
  status: 'confirmed' | 'dismissed'
): Promise<MatchSuggestion> {
  const { data } = await apiClient.patch<ApiResponse<MatchSuggestion>>(
    MATCH_SUGGESTIONS.UPDATE(id),
    { status }
  )
  return data.data!
}
