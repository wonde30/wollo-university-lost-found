// src/utils/cache.ts
export interface CacheEntry<T> {
    data: T;
    timestamp: number;
    ttl: number; // in milliseconds
}

export class AppCache {
    private static cache = new Map<string, CacheEntry<any>>();

    static set<T>(key: string, data: T, ttlSeconds: number = 300): void {
        this.cache.set(key, {
            data,
            timestamp: Date.now(),
            ttl: ttlSeconds * 1000,
        });
    }

    static get<T>(key: string): { data: T; isStale: boolean } | null {
        const entry = this.cache.get(key);
        if (!entry) return null;

        const isStale = Date.now() - entry.timestamp > entry.ttl;
        return { data: entry.data as T, isStale };
    }

    static invalidate(prefix: string): void {
        for (const key of this.cache.keys()) {
            if (key.startsWith(prefix)) {
                this.cache.delete(key);
            }
        }
    }

    static clear(): void {
        this.cache.clear();
    }
}
