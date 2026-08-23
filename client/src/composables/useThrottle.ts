/**
 * Throttle function utility and composable.
 */

export function throttle<T extends (...args: any[]) => any>(
  fn: T,
  limit = 200
): (...args: Parameters<T>) => void {
  let inThrottle = false

  return function (this: any, ...args: Parameters<T>) {
    if (!inThrottle) {
      fn.apply(this, args)
      inThrottle = true
      setTimeout(() => {
        inThrottle = false
      }, limit)
    }
  }
}

export function useThrottle() {
  return {
    throttle,
  }
}
