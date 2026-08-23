/**
 * Shared UI Design System Component Types & Interfaces
 */

export interface TableColumn {
  key: string
  label: string
  align?: 'left' | 'center' | 'right'
  sortable?: boolean
  width?: string
}

export interface TabItem {
  id: string | number
  label: string
  icon?: string
  badge?: number | string
  disabled?: boolean
}

export interface SelectOption {
  label: string
  value: string | number
  disabled?: boolean
}

export interface RadioOption {
  label: string
  value: string | number
  description?: string
  disabled?: boolean
}
