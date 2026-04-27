import fs from 'node:fs'
import path from 'node:path'

const root = process.cwd()
const webSrc = path.join(root, 'apps', 'web', 'src')
const allowed = new Set([
  path.join(webSrc, 'lib', 'api', 'http.ts'),
  path.join(webSrc, 'lib', 'api', 'generated', 'client.ts')
])

const bannedPatterns = [
  /\bfetch\s*\(/,
  /\baxios\s*\./,
  /\baxios\s*\(/,
  /\bapiHttpClient\.request\s*</,
  /\bapiHttpClient\.request\s*\(/
]

function walk(dir, bucket = []) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name)
    if (entry.isDirectory()) walk(full, bucket)
    else if (/\.(ts|tsx)$/.test(entry.name)) bucket.push(full)
  }
  return bucket
}

const offenders = []
for (const file of walk(webSrc)) {
  if (allowed.has(file)) continue
  const content = fs.readFileSync(file, 'utf8')
  if (bannedPatterns.some((pattern) => pattern.test(content))) {
    offenders.push(path.relative(root, file))
  }
}

if (offenders.length) {
  console.error('Forbidden handwritten API usage found in:')
  offenders.forEach((entry) => console.error(` - ${entry}`))
  process.exit(1)
}

console.log('No forbidden handwritten API usage found.')
