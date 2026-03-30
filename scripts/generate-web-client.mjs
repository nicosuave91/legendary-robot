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

function tsTypeFromSchema(schema, nameHint = 'Anonymous') {
  if (!schema) return 'unknown'
  if (schema.$ref) {
    const ref = schema.$ref.split('/').pop()
    return ref
  }
  if (schema.enum) {
    return schema.enum.map((entry) => JSON.stringify(entry)).join(' | ')
  }
  if (schema.oneOf) {
    return schema.oneOf.map((entry) => tsTypeFromSchema(entry, nameHint)).join(' | ')
  }
  if (schema.anyOf) {
    return schema.anyOf.map((entry) => tsTypeFromSchema(entry, nameHint)).join(' | ')
  }
  if (schema.type === 'string') return 'string'
  if (schema.type === 'integer' || schema.type === 'number') return 'number'
  if (schema.type === 'boolean') return 'boolean'
  if (schema.type === 'array') return `${tsTypeFromSchema(schema.items, `${nameHint}Item`)}[]`
  if (schema.type === 'object' || schema.properties) {
    const properties = schema.properties ?? {}
    const required = new Set(schema.required ?? [])
    const lines = Object.entries(properties).map(([key, value]) => {
      const optional = required.has(key) ? '' : '?'
      return `  ${JSON.stringify(key)}${optional}: ${tsTypeFromSchema(value, pascalCase(key))};`
    })
    return `{\n${lines.join('\n')}\n}`
  }
  return 'unknown'
}

const schemaBlocks = Object.entries(schemas)
  .map(([name, schema]) => `export type ${name} = ${tsTypeFromSchema(schema, name)};`)
  .join('\n\n')

const operationBlocks = []

for (const [routePath, methods] of Object.entries(paths)) {
  for (const [method, operation] of Object.entries(methods)) {
    const operationId = operation.operationId ?? `${method}_${routePath.replace(/[\/{}-]+/g, '_')}`
    const fnName = operationId
    const requestBodySchema =
      operation.requestBody?.content?.['application/json']?.schema
    const responseSchema =
      operation.responses?.['200']?.content?.['application/json']?.schema ??
      operation.responses?.['201']?.content?.['application/json']?.schema ??
      operation.responses?.default?.content?.['application/json']?.schema

    const requestType = requestBodySchema ? tsTypeFromSchema(requestBodySchema, `${pascalCase(fnName)}Request`) : 'void'
    const responseType = responseSchema ? tsTypeFromSchema(responseSchema, `${pascalCase(fnName)}Response`) : 'void'

    const hasBody = requestType !== 'void'
    operationBlocks.push(`
export async function ${fnName}(client: ApiHttpClient${hasBody ? ', body: ' + requestType : ''}): Promise<${responseType}> {
  return client.request<${responseType}>({
    method: ${JSON.stringify(method.toUpperCase())},
    path: ${JSON.stringify(routePath)}${hasBody ? ', body' : ''}
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
