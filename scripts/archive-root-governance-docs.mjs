#!/usr/bin/env node
import { copyFileSync, existsSync, mkdirSync, rmSync } from 'node:fs'
import { dirname, resolve } from 'node:path'

const root = process.cwd()

const moves = [
  ['current-platform-status.md', 'docs/release/current-platform-status.md'],
  ['verification-matrix.md', 'docs/testing/verification-matrix.md'],
  ['IMPLEMENTATION-NOTES.md', 'docs/archive/closure/IMPLEMENTATION-NOTES-closure-pass-1.md'],
]

for (const [from, to] of moves) {
  const source = resolve(root, from)
  const target = resolve(root, to)

  if (!existsSync(source)) {
    console.warn(`[archive-root-governance-docs] skipping missing source: ${from}`)
    continue
  }

  mkdirSync(dirname(target), { recursive: true })
  copyFileSync(source, target)
  rmSync(source)
  console.log(`[archive-root-governance-docs] moved ${from} -> ${to}`)
}
