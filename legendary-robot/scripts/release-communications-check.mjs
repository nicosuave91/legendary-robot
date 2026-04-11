import { spawnSync } from 'node:child_process'
import process from 'node:process'

const useShell = process.platform === 'win32'

function run(command, args, options = {}) {
  const pretty = [command, ...args].join(' ')
  console.log(`\n> ${pretty}`)
  const result = spawnSync(command, args, {
    stdio: 'inherit',
    shell: useShell,
    ...options,
  })

  if (result.status !== 0) {
    throw new Error(`Command failed: ${pretty}`)
  }
}

run('npm', ['run', 'contracts:sync'])
run('node', ['scripts/verify-communications-contract-sync.mjs'])
run('npm', ['run', 'guard:no-handwritten-api'])
run('npm', ['--workspace', 'apps/web', 'run', 'typecheck'])
run('npm', ['--workspace', 'apps/web', 'run', 'test'])
run('npm', ['--workspace', 'apps/web', 'run', 'build'])
run('php', ['vendor/bin/phpstan', 'analyse', '--memory-limit=1G'], { cwd: 'apps/api' })
run('php', ['vendor/bin/phpunit'], { cwd: 'apps/api' })

console.log('\nCommunications prerelease check completed successfully.')
