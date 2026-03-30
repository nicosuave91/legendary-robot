import type { ApiHttpClient, ApiRequestOptions } from '@/lib/api/generated/client'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? ''

export class BrowserApiClient implements ApiHttpClient {
  async request<T>(options: ApiRequestOptions): Promise<T> {
    const response = await fetch(`${API_BASE_URL}${options.path}`, {
      method: options.method,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json'
      },
      credentials: 'include',
      body: options.body ? JSON.stringify(options.body) : undefined
    })

    if (!response.ok) {
      throw new Error(`API request failed with status ${response.status}`)
    }

    return response.json() as Promise<T>
  }
}

export const apiHttpClient = new BrowserApiClient()
