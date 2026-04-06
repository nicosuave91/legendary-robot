import fs from 'node:fs/promises'
import path from 'node:path'

const repoRoot = process.cwd()

const directMoves = [
  ['current-platform-status.md', 'docs/release/current-platform-status.md'],
  ['verification-matrix.md', 'docs/testing/verification-matrix.md'],
  ['IMPLEMENTATION-NOTES.md', 'docs/archive/closure/IMPLEMENTATION-NOTES-closure-pass-1.md'],
]

const sprintArchiveDir = path.join(repoRoot, 'docs', 'archive', 'sprints')

async function exists(target) {
  try {
    await fs.access(target)
    return true
  } catch {
    return false
  }
}

async function ensureDir(target) {
  await fs.mkdir(path.dirname(target), { recursive: true })
}

async function moveIfPresent(sourceRelative, targetRelative) {
  const source = path.join(repoRoot, sourceRelative)
  const target = path.join(repoRoot, targetRelative)

  if (!(await exists(source))) {
    return { sourceRelative, targetRelative, moved: false, reason: 'missing' }
  }

  await ensureDir(target)

  if (await exists(target)) {
    return { sourceRelative, targetRelative, moved: false, reason: 'target_exists' }
  }

  await fs.rename(source, target)
  return { sourceRelative, targetRelative, moved: true, reason: 'moved' }
}

async function moveRootSprintArtifacts() {
  const rootEntries = await fs.readdir(repoRoot, { withFileTypes: true })
  const results = []

  for (const entry of rootEntries) {
    if (!entry.isFile()) continue
    const name = entry.name
    if (!/^SPRINT/i.test(name)) continue
    if (!/\.(md|txt|zip)$/i.test(name)) continue

    const source = path.join(repoRoot, name)
    const target = path.join(sprintArchiveDir, name)
    await ensureDir(target)

    if (await exists(target)) {
      results.push({ name, moved: false, reason: 'target_exists' })
      continue
    }

    await fs.rename(source, target)
    results.push({ name, moved: true, reason: 'moved' })
  }

  return results
}

async function main() {
  const movedDocs = []
  for (const [source, target] of directMoves) {
    movedDocs.push(await moveIfPresent(source, target))
  }

  const sprintMoves = await moveRootSprintArtifacts()

  const report = {
    movedDocs,
    sprintMoves,
    notes: [
      'Review docs/archive/root-artifacts-manifest.md for the remaining delete actions.',
      'This script does not delete root snapshot directories like merge-hotfix-2/.',
    ],
  }

  console.log(JSON.stringify(report, null, 2))
}

main().catch((error) => {
  console.error(error)
  process.exitCode = 1
})
