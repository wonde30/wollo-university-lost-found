/**
 * Application-wide configuration constants.
 */

export const APP_NAME = 'Wollo University Lost & Found System'
export const APP_SHORT_NAME = 'WU Lost & Found'
export const APP_VERSION = '1.0.0'
export const APP_INSTITUTION = 'Wollo University'

export const DEFAULT_PAGINATION = {
  PAGE: 1,
  PER_PAGE: 15,
  MAX_PER_PAGE: 50,
} as const

export const FILE_UPLOAD_LIMITS = {
  MAX_FILE_SIZE_BYTES: 2 * 1024 * 1024, // 2MB
  MAX_FILES: 5,
  ALLOWED_IMAGE_TYPES: ['image/jpeg', 'image/png', 'image/webp'],
} as const
