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

function resolveQueryString(queryParams?: Record<string, string | number | boolean | null | undefined>) {
  if (!queryParams) return ''

  const searchParams = new URLSearchParams()
  Object.entries(queryParams).forEach(([key, value]) => {
    if (value === null || value === undefined || value === '') {
      return
    }
    searchParams.set(key, String(value))
  })

  const queryString = searchParams.toString()
  return queryString ? `?${queryString}` : ''
}

export class BrowserApiClient implements ApiHttpClient {
  async request<T>(options: ApiRequestOptions): Promise<T> {
    const url = `${API_BASE_URL}${resolvePath(options.path, options.pathParams)}${resolveQueryString(options.queryParams)}`
    const isMultipart = options.contentType === 'multipart/form-data' || options.body instanceof FormData

    const headers: HeadersInit = {
      Accept: 'application/json'
    }

    if (options.body && !isMultipart) {
      headers['Content-Type'] = 'application/json'
    }

    const response = await fetch(url, {
      method: options.method,
      headers,
      credentials: 'include',
      body: options.body
        ? isMultipart
          ? (options.body as FormData)
          : JSON.stringify(options.body)
        : undefined
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
