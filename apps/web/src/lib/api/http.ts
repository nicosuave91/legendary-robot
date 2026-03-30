import type { ApiHttpClient, ApiRequestOptions } from '@/lib/api/generated/client'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? ''

export class ApiError extends Error {
  status: number
  payload: unknown

  constructor(status: number, message: string, payload: unknown) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.payload = payload
  }
}

function resolvePath(path: string, pathParams?: Record<string, string | number>) {
  if (!pathParams) return path

  return Object.entries(pathParams).reduce((resolved, [key, value]) => {
    return resolved.replaceAll(`{${key}}`, encodeURIComponent(String(value)))
  }, path)
}

export class BrowserApiClient implements ApiHttpClient {
  async request<T>(options: ApiRequestOptions): Promise<T> {
    const response = await fetch(`${API_BASE_URL}${resolvePath(options.path, options.pathParams)}`, {
      method: options.method,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json'
      },
      credentials: 'include',
      body: options.body ? JSON.stringify(options.body) : undefined
    })

    const contentType = response.headers.get('content-type') ?? ''
    const payload = contentType.includes('application/json')
      ? await response.json()
      : await response.text()

    if (!response.ok) {
      const message =
        typeof payload === 'object' && payload !== null && 'message' in payload
          ? String(payload.message)
          : `API request failed with status ${response.status}`

      throw new ApiError(response.status, message, payload)
    }

    return payload as T
  }
}

export const apiHttpClient = new BrowserApiClient()
