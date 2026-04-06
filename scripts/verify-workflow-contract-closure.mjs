import { readFileSync } from 'node:fs'

const openapi = readFileSync('packages/contracts/openapi.json', 'utf8')
const generatedClient = readFileSync('apps/web/src/lib/api/generated/client.ts', 'utf8')
const webClient = readFileSync('apps/web/src/lib/api/client.ts', 'utf8')
const workflowDetailPage = readFileSync('apps/web/src/features/workflow-builder/pages/workflow-detail-page.tsx', 'utf8')

const failures = []

if (!openapi.includes('"WorkflowDraftValidationSummary"')) {
  failures.push('packages/contracts/openapi.json is missing WorkflowDraftValidationSummary.')
}

if (!openapi.includes('"WorkflowDraftValidationIssue"')) {
  failures.push('packages/contracts/openapi.json is missing WorkflowDraftValidationIssue.')
}

if (!openapi.includes('"draftValidation"')) {
  failures.push('packages/contracts/openapi.json is missing the draftValidation workflow detail field.')
}

if (!webClient.includes('WorkflowDetailEnvelopeWithDraftValidation')) {
  failures.push('apps/web/src/lib/api/client.ts is missing WorkflowDetailEnvelopeWithDraftValidation.')
}

if (!workflowDetailPage.includes('draftValidation')) {
  failures.push('apps/web/src/features/workflow-builder/pages/workflow-detail-page.tsx is not reading draftValidation.')
}

if (failures.length > 0) {
  console.error('Workflow contract closure verification failed:')
  for (const failure of failures) {
    console.error(`- ${failure}`)
  }
  process.exit(1)
}

console.log('Workflow contract closure verification passed.')
