#!/usr/bin/env node
import { existsSync, readdirSync } from 'node:fs'
import { resolve } from 'node:path'

const root = process.cwd()

const prohibitedExact = [
  'current-platform-status.md',
  'verification-matrix.md',
  'IMPLEMENTATION-NOTES.md',
]

const prohibitedPatterns = [
  /^SPRINT/i,
]

const failures = []

for (const file of prohibitedExact) {
  if (existsSync(resolve(root, file))) {
    failures.push(`Prohibited root documentation file present: ${file}`)
  }
}

for (const name of readdirSync(root)) {
  if (prohibitedPatterns.some((pattern) => pattern.test(name))) {
    failures.push(`Prohibited sprint artifact present at repo root: ${name}`)
  }
}

if (failures.length > 0) {
  console.error('Repository root governance check failed:')
  for (const failure of failures) {
    console.error(`- ${failure}`)
  }
  process.exit(1)
}

console.log('Repository root governance check passed.')
