/**
 * Route meta type definitions.
 * Extends Vue Router's RouteMeta interface.
 */

import 'vue-router'

declare module 'vue-router' {
  interface RouteMeta {
    /**
     * Requires user to be authenticated.
     */
    requiresAuth?: boolean

    /**
     * Requires user to NOT be authenticated (guest only).
     */
    requiresGuest?: boolean

    /**
     * Requires email verification.
     * NOTE: Currently disabled as User type doesn't have email_verified_at field.
     */
    requiresVerified?: boolean

    /**
     * Required roles to access this route.
     * Empty array = any authenticated user.
     */
    roles?: string[]

    /**
     * Page title for document.title.
     */
    title?: string
  }
}
