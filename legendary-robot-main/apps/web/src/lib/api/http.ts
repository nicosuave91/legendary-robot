import type { ApiHttpClient, ApiRequestOptions } from '@/lib/api/generated/client'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? ''
const STATE_CHANGING_METHODS = new Set(['POST', 'PATCH', 'PUT', 'DELETE'])
let csrfBootstrapPromise: Promise<void> | null = null

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

function readCookie(name: string) {
  if (typeof document === 'undefined') {
    return null
  }

  const encodedName = `${name}=`
  const match = document.cookie
    .split(';')
    .map((part) => part.trim())
    .find((part) => part.startsWith(encodedName))

  if (!match) {
    return null
  }

  return decodeURIComponent(match.slice(encodedName.length))
}

function resolveApiOrigin() {
  if (API_BASE_URL) {
    return new URL(API_BASE_URL, typeof window !== 'undefined' ? window.location.origin : 'http://localhost').origin
  }

  if (typeof window !== 'undefined') {
    return window.location.origin
  }

  return ''
}

function resolveCsrfCookieUrl() {
  const origin = resolveApiOrigin()
  return `${origin}/sanctum/csrf-cookie`
}

function shouldEnsureCsrfCookie(options: ApiRequestOptions) {
  return STATE_CHANGING_METHODS.has(options.method)
}

async function ensureCsrfCookie(forceRefresh = false) {
  if (!forceRefresh && readCookie('XSRF-TOKEN')) {
    return
  }

  if (!forceRefresh && csrfBootstrapPromise) {
    return csrfBootstrapPromise
  }

  const bootstrap = (async () => {
    const response = await fetch(resolveCsrfCookieUrl(), {
      method: 'GET',
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      const payload = await response.text()
      throw new ApiError(response.status, 'Unable to initialize the browser CSRF session.', payload)
    }
  })()

  csrfBootstrapPromise = bootstrap

  try {
    await bootstrap
  } finally {
    if (csrfBootstrapPromise === bootstrap) {
      csrfBootstrapPromise = null
    }
  }
}

async function parseResponsePayload(response: Response) {
  const contentType = response.headers.get('content-type') ?? ''

  if (contentType.includes('application/json')) {
    return response.json()
  }

  return response.text()
}

export class BrowserApiClient implements ApiHttpClient {
  async request<T>(options: ApiRequestOptions): Promise<T> {
    const url = `${API_BASE_URL}${resolvePath(options.path, options.pathParams)}${resolveQueryString(options.queryParams)}`
    const isMultipart = options.contentType === 'multipart/form-data' || options.body instanceof FormData

    const execute = async (allowRetryOnCsrfFailure: boolean): Promise<T> => {
      if (shouldEnsureCsrfCookie(options)) {
        await ensureCsrfCookie()
      }

      const headers: HeadersInit = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      }

      if (options.body && !isMultipart) {
        headers['Content-Type'] = 'application/json'
      }

      const xsrfToken = readCookie('XSRF-TOKEN')
      if (xsrfToken && shouldEnsureCsrfCookie(options)) {
        headers['X-XSRF-TOKEN'] = xsrfToken
      }

      const response = await fetch(url, {
        method: options.method,
        headers,
        credentials: 'include',
        body: options.body
          ? isMultipart
            ? (options.body as FormData)
            : JSON.stringify(options.body)
          : undefined,
      })

      if (response.status === 419 && allowRetryOnCsrfFailure && shouldEnsureCsrfCookie(options)) {
        await ensureCsrfCookie(true)
        return execute(false)
      }

      const payload = await parseResponsePayload(response)

      if (!response.ok) {
        const message =
          typeof payload === 'object' && payload !== null && 'message' in payload
            ? String((payload as { message: string }).message)
            : `API request failed with status ${response.status}`

        throw new ApiError(response.status, message, payload)
      }

      return payload as T
    }

    return execute(true)
  }
}

export const apiHttpClient = new BrowserApiClient()
