import fs from 'node:fs'
import path from 'node:path'

const root = process.cwd()
const specPath = path.join(root, 'packages', 'contracts', 'openapi.json')
const generatedClientPath = path.join(root, 'apps', 'web', 'src', 'lib', 'api', 'generated', 'client.ts')

if (!fs.existsSync(specPath)) {
  throw new Error(`OpenAPI contract not found at ${specPath}. Run npm run contracts:publish first.`)
}

if (!fs.existsSync(generatedClientPath)) {
  throw new Error(`Generated client not found at ${generatedClientPath}. Run npm run client:generate first.`)
}

const spec = JSON.parse(fs.readFileSync(specPath, 'utf8'))
const generatedClient = fs.readFileSync(generatedClientPath, 'utf8')

const communicationsPath = spec.paths?.['/api/v1/clients/{clientId}/communications']
if (!communicationsPath?.get) {
  throw new Error('Missing GET /api/v1/clients/{clientId}/communications in published contract.')
}

const communicationsParams = communicationsPath.get.parameters ?? []
const hasCursorParam = communicationsParams.some((parameter) => parameter?.name === 'cursor' && parameter?.in === 'query')
if (!hasCursorParam) {
  throw new Error('Published communications contract is missing the cursor query parameter for client timelines.')
}

const inboxPath = spec.paths?.['/api/v1/communications/inbox']
if (!inboxPath?.get) {
  throw new Error('Published communications contract is missing GET /api/v1/communications/inbox.')
}

const scanStatusPath = spec.paths?.['/api/v1/communications/attachments/{attachmentId}/scan-status']
if (!scanStatusPath?.patch) {
  throw new Error('Published communications contract is missing PATCH /api/v1/communications/attachments/{attachmentId}/scan-status.')
}

const requiredGeneratedMarkers = [
  'export async function getCommunicationsInbox',
  'export async function patchCommunicationAttachmentScanStatus',
]

for (const marker of requiredGeneratedMarkers) {
  if (!generatedClient.includes(marker)) {
    throw new Error(`Generated web client is stale. Missing marker: ${marker}`)
  }
}

const getClientCommunicationsBlock = generatedClient.match(/export async function getClientCommunications[\s\S]*?\n}\n/)
if (!getClientCommunicationsBlock || !getClientCommunicationsBlock[0].includes('"cursor"?: string;')) {
  throw new Error('Generated web client is stale. getClientCommunications query params do not include cursor.')
}

console.log('Communications contract and generated client are in sync.')
