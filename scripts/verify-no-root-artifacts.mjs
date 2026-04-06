import fs from 'node:fs/promises'
import path from 'node:path'

const repoRoot = process.cwd()

const forbiddenRootFiles = new Set([
  'current-platform-status.md',
  'verification-matrix.md',
  'IMPLEMENTATION-NOTES.md',
])

const forbiddenRootFilePatterns = [
  /^SPRINT.*\.(md|txt|zip)$/i,
]

const forbiddenRootDirectoryPatterns = [
  /^merge-hotfix-/i,
]

async function main() {
  const entries = await fs.readdir(repoRoot, { withFileTypes: true })
  const violations = []

  for (const entry of entries) {
    if (entry.isFile()) {
      if (forbiddenRootFiles.has(entry.name) || forbiddenRootFilePatterns.some((pattern) => pattern.test(entry.name))) {
        violations.push(`root file should be archived or relocated: ${entry.name}`)
      }
    }

    if (entry.isDirectory()) {
      if (forbiddenRootDirectoryPatterns.some((pattern) => pattern.test(entry.name))) {
        violations.push(`root snapshot directory should be removed: ${entry.name}/`)
      }
    }
  }

  if (violations.length > 0) {
    console.error('Repository hygiene violations detected:')
    for (const violation of violations) {
      console.error(`- ${violation}`)
    }
    process.exit(1)
  }

  console.log('No forbidden root sprint/status artifacts detected.')
}

main().catch((error) => {
  console.error(error)
  process.exit(1)
})
