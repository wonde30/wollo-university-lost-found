/**
 * Category → Lucide icon mapping utility.
 *
 * Maps category `icon_slug` values (from the backend) to Lucide icon components.
 * Unknown slugs gracefully fall back to `Package`.
 *
 * To add support for a new category, simply add an entry to ICON_MAP below.
 */

import {
  Smartphone,
  Laptop,
  BookOpen,
  KeyRound,
  Wallet,
  Backpack,
  CreditCard,
  Watch,
  Headphones,
  Glasses,
  Umbrella,
  Camera,
  PenTool,
  Shirt,
  HardDrive,
  Usb,
  Tablet,
  FlaskConical,
  Package,
  type LucideIcon,
} from 'lucide-vue-next'

/**
 * Known slug → Lucide icon mapping.
 * Keys are normalised to lowercase for matching.
 */
const ICON_MAP: Record<string, LucideIcon> = {
  // Electronics
  phone: Smartphone,
  smartphone: Smartphone,
  mobile: Smartphone,
  laptop: Laptop,
  computer: Laptop,
  tablet: Tablet,
  headphone: Headphones,
  headphones: Headphones,
  earbuds: Headphones,
  camera: Camera,
  usb: Usb,
  flash_drive: Usb,
  hard_drive: HardDrive,

  // Accessories
  key: KeyRound,
  keys: KeyRound,
  wallet: Wallet,
  purse: Wallet,
  bag: Backpack,
  backpack: Backpack,
  watch: Watch,
  glasses: Glasses,
  eyeglasses: Glasses,
  sunglasses: Glasses,
  umbrella: Umbrella,

  // Documents & stationery
  id_card: CreditCard,
  id: CreditCard,
  card: CreditCard,
  book: BookOpen,
  books: BookOpen,
  notebook: BookOpen,
  pen: PenTool,
  stationery: PenTool,

  // Clothing
  clothing: Shirt,
  clothes: Shirt,
  jacket: Shirt,

  // Academic
  lab_equipment: FlaskConical,
  lab: FlaskConical,

  // Fallback is handled separately
}

/**
 * Resolve a Lucide icon component for a given category icon slug.
 *
 * @param slug - The `icon_slug` value from the backend Category model, or null.
 * @returns A Lucide icon component.  Always returns a valid component (never undefined).
 */
export function getCategoryIcon(slug: string | null | undefined): LucideIcon {
  if (!slug) return Package

  const normalised = slug.trim().toLowerCase().replace(/[\s-]+/g, '_')
  return ICON_MAP[normalised] ?? Package
}

/** The default fallback icon component, exported for convenience. */
export const FallbackCategoryIcon = Package
