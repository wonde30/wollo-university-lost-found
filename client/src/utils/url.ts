/**
 * URL and image path resolution utilities
 */

export function resolveStorageUrl(path: string | null | undefined): string {
  if (!path || typeof path !== 'string' || !path.trim()) return ''
  const trimmed = path.trim()
  if (
    trimmed.startsWith('http://') ||
    trimmed.startsWith('https://') ||
    trimmed.startsWith('data:') ||
    trimmed.startsWith('blob:')
  ) {
    return trimmed
  }

  const baseUrl = (import.meta.env.VITE_API_URL || 'http://localhost:8000').replace(/\/$/, '')
  
  // If already prefixed with storage or /storage
  if (trimmed.startsWith('/storage/') || trimmed.startsWith('storage/')) {
    const cleanPath = trimmed.startsWith('/') ? trimmed : `/${trimmed}`
    return `${baseUrl}${cleanPath}`
  }

  // If path is stored by Laravel public disk (e.g. 'avatars/xyz.jpg' or 'item-photos/xyz.jpg')
  const cleanPath = trimmed.startsWith('/') ? trimmed : `/${trimmed}`
  return `${baseUrl}/storage${cleanPath}`
}
