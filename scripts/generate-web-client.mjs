import fs from 'node:fs'
import path from 'node:path'

const root = process.cwd()
const specPath = path.join(root, 'packages', 'contracts', 'openapi.json')
const outPath = path.join(root, 'apps', 'web', 'src', 'lib', 'api', 'generated', 'client.ts')

const spec = JSON.parse(fs.readFileSync(specPath, 'utf8'))
const schemas = spec.components?.schemas ?? {}
const paths = spec.paths ?? {}

function pascalCase(value) {
  return value
    .replace(/[^a-zA-Z0-9]+(.)/g, (_, c) => c.toUpperCase())
    .replace(/^[a-z]/, (c) => c.toUpperCase())
}

function uniqueUnion(parts) {
  const normalized = []
  for (const part of parts.flatMap((entry) => String(entry).split('|').map((value) => value.trim()))) {
    if (!part || normalized.includes(part)) continue
    normalized.push(part)
  }
  return normalized.join(' | ') || 'unknown'
}

function withNullable(type, schema) {
  return schema?.nullable ? uniqueUnion([type, 'null']) : type
}

function normalizeNullableEnum(enumValues = []) {
  const cleaned = enumValues.filter((entry) => entry !== null)
  const base = cleaned.map((entry) => JSON.stringify(entry)).join(' | ') || 'never'
  return enumValues.includes(null) ? uniqueUnion([base, 'null']) : base
}

function tsTypeFromSchema(schema, nameHint = 'Anonymous') {
  if (!schema) return 'unknown'
  if (schema.$ref) {
    const ref = schema.$ref.split('/').pop()
    return withNullable(ref, schema)
  }
  if (schema.type === 'null') return 'null'
  if (Array.isArray(schema.type)) {
    const nonNullTypes = schema.type.filter((entry) => entry !== 'null')
    if (nonNullTypes.length === 1) {
      return uniqueUnion([tsTypeFromSchema({ ...schema, type: nonNullTypes[0] }, nameHint), 'null'])
    }
    return uniqueUnion(schema.type.map((entry) => entry === 'null' ? 'null' : tsTypeFromSchema({ ...schema, type: entry }, nameHint)))
  }
  if (schema.enum) {
    return withNullable(normalizeNullableEnum(schema.enum), schema)
  }
  if (schema.oneOf) {
    return withNullable(uniqueUnion(schema.oneOf.map((entry) => tsTypeFromSchema(entry, nameHint))), schema)
  }
  if (schema.anyOf) {
    return withNullable(uniqueUnion(schema.anyOf.map((entry) => tsTypeFromSchema(entry, nameHint))), schema)
  }
  if (schema.format === 'binary') return 'File'
  if (schema.type === 'string') return withNullable('string', schema)
  if (schema.type === 'integer' || schema.type === 'number') return withNullable('number', schema)
  if (schema.type === 'boolean') return withNullable('boolean', schema)
  if (schema.type === 'array') return withNullable(`(${tsTypeFromSchema(schema.items, `${nameHint}Item`)})[]`, schema)
  if (schema.type === 'object' || schema.properties) {
    const properties = schema.properties ?? {}
    if (Object.keys(properties).length === 0) {
      return withNullable('Record<string, unknown>', schema)
    }
    const required = new Set(schema.required ?? [])
    const lines = Object.entries(properties).map(([key, value]) => {
      const optional = required.has(key) ? '' : '?'
      return `  ${JSON.stringify(key)}${optional}: ${tsTypeFromSchema(value, pascalCase(key))};`
    })
    return withNullable(`{\n${lines.join('\n')}\n}`, schema)
  }
  return 'unknown'
}

function parameterType(parameters) {
  const lines = parameters.map((parameter) => {
    const optional = parameter.required ? '' : '?'
    return `  ${JSON.stringify(parameter.name)}${optional}: ${tsTypeFromSchema(parameter.schema ?? {}, pascalCase(parameter.name))};`
  })
  return `{\n${lines.join('\n')}\n}`
}

const schemaBlocks = Object.entries(schemas)
  .map(([name, schema]) => `export type ${name} = ${tsTypeFromSchema(schema, name)};`)
  .join('\n\n')

const operationBlocks = []

for (const [routePath, methods] of Object.entries(paths)) {
  const pathParamsFromRoute = [...routePath.matchAll(/\{([^}]+)\}/g)].map((match) => match[1])

  for (const [method, operation] of Object.entries(methods)) {
    const operationId = operation.operationId ?? `${method}_${routePath.replace(/[\/{}-]+/g, '_')}`
    const fnName = operationId
    const parameters = operation.parameters ?? []
    const pathParameters = parameters.filter((parameter) => parameter.in === 'path')
    const queryParameters = parameters.filter((parameter) => parameter.in === 'query')
    const derivedPathParameters = pathParameters.length > 0
      ? pathParameters
      : pathParamsFromRoute.map((name) => ({ name, required: true, schema: { type: 'string' } }))

    const requestContent = operation.requestBody?.content ?? {}
    const jsonSchema = requestContent['application/json']?.schema
    const multipartSchema = requestContent['multipart/form-data']?.schema
    const requestType = multipartSchema ? 'FormData' : (jsonSchema ? tsTypeFromSchema(jsonSchema, `${pascalCase(fnName)}Request`) : 'void')
    const contentType = multipartSchema ? 'multipart/form-data' : (jsonSchema ? 'application/json' : null)
    const responseSchema =
      operation.responses?.['200']?.content?.['application/json']?.schema ??
      operation.responses?.['201']?.content?.['application/json']?.schema ??
      operation.responses?.default?.content?.['application/json']?.schema

    const responseType = responseSchema ? tsTypeFromSchema(responseSchema, `${pascalCase(fnName)}Response`) : 'void'
    const signatureParts = ['client: ApiHttpClient']
    const optionsParts = [
      `method: ${JSON.stringify(method.toUpperCase())}`,
      `path: ${JSON.stringify(routePath)}`
    ]

    if (derivedPathParameters.length > 0) {
      signatureParts.push(`pathParams: ${parameterType(derivedPathParameters)}`)
      optionsParts.push('pathParams')
    }

    if (queryParameters.length > 0) {
      const allOptional = queryParameters.every((parameter) => !parameter.required)
      signatureParts.push(`queryParams${allOptional ? '?' : ''}: ${parameterType(queryParameters)}`)
      optionsParts.push('queryParams')
    }

    if (requestType !== 'void') {
      signatureParts.push(`body: ${requestType}`)
      optionsParts.push('body')
      if (contentType) {
        optionsParts.push(`contentType: ${JSON.stringify(contentType)}`)
      }
    }

    operationBlocks.push(`
export async function ${fnName}(${signatureParts.join(', ')}): Promise<${responseType}> {
  return client.request<${responseType}>({
    ${optionsParts.join(',\n    ')}
  });
}
`)
  }
}

const output = `/* eslint-disable */
/**
 * AUTO-GENERATED FILE.
 * Source: packages/contracts/openapi.json
 * Regenerate with: npm run client:generate
 */

export type HttpMethod = 'GET' | 'POST' | 'PATCH' | 'PUT' | 'DELETE';

export interface ApiRequestOptions {
  method: HttpMethod;
  path: string;
  body?: unknown;
  pathParams?: Record<string, string | number>;
  queryParams?: Record<string, string | number | boolean | null | undefined>;
  contentType?: 'application/json' | 'multipart/form-data';
}

export interface ApiHttpClient {
  request<T>(options: ApiRequestOptions): Promise<T>;
}

${schemaBlocks}

${operationBlocks.join('\n')}
`

fs.mkdirSync(path.dirname(outPath), { recursive: true })
fs.writeFileSync(outPath, output)
console.log(`Generated client at ${outPath}`)
