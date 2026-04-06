# Sprint 12 Backlog

## S12-01 Deterministic workspace materialization
- **Purpose:** make Sprint 11 executable in a normal workspace.
- **Dependencies:** completed Sprint 11 package.
- **Technical tasks:** align package-manager usage, generate lockfile, update stale package metadata, wire verification scripts.
- **Acceptance criteria:** fresh workspace install path is documented and repeatable.
- **Test requirements:** clean dependency install and CI reproduction.

## S12-02 Contract and generated-client verification
- **Purpose:** prevent contract drift during closeout.
- **Dependencies:** S12-01.
- **Technical tasks:** publish OpenAPI, regenerate client, verify no handwritten API drift, fail on generated diffs.
- **Acceptance criteria:** contract and generated client are in sync.
- **Test requirements:** contract tests and generated diff check.

## S12-03 Backend runtime verification
- **Purpose:** prove Laravel runtime readiness.
- **Dependencies:** S12-02.
- **Technical tasks:** composer install, migrate fresh, seed, PHPUnit, Larastan, rollback sanity.
- **Acceptance criteria:** backend suite passes and migrations are operationally safe.
- **Test requirements:** PHPUnit, Larastan, migration logs.

## S12-04 Frontend runtime verification
- **Purpose:** prove React runtime readiness.
- **Dependencies:** S12-02.
- **Technical tasks:** workspace install, typecheck, unit tests, build, Playwright smoke.
- **Acceptance criteria:** frontend checks pass and major routes render.
- **Test requirements:** typecheck, Vitest, Playwright.

## S12-05 Phase 5 calendar/tasks closure proof
- **Purpose:** close the historical calendar caveat with executable evidence.
- **Dependencies:** S12-03, S12-04.
- **Technical tasks:** verify selected-day query, event detail, task mutation, history persistence, audit entry, and client-events drilldown.
- **Acceptance criteria:** date -> event -> task -> history -> client flow works end to end.
- **Test requirements:** feature tests plus browser smoke.

## S12-06 Queue, import, and workflow runtime proof
- **Purpose:** validate async execution and version binding.
- **Dependencies:** S12-03.
- **Technical tasks:** validate/commit import via queue, start workflow runs, verify step execution, confirm tenant/correlation propagation.
- **Acceptance criteria:** async work executes with durable evidence.
- **Test requirements:** integration tests and queue smoke logs.

## S12-07 Communications trust verification
- **Purpose:** prove callback-driven communications status in a configured environment.
- **Dependencies:** S12-03, queue runtime.
- **Technical tasks:** queue outbound SMS/email/call, verify Twilio and SendGrid callbacks, confirm tenant-safe reconciliation.
- **Acceptance criteria:** callback trust and visible timeline projection are proven.
- **Test requirements:** integration tests and configured-environment evidence.

## S12-08 Release-governance reconciliation
- **Purpose:** produce an honest release decision.
- **Dependencies:** S12-01 through S12-07.
- **Technical tasks:** blocker register, accepted-risk register, docs reconciliation, final evidence bundle.
- **Acceptance criteria:** release readiness can be reviewed from explicit evidence.
- **Test requirements:** checklist walk-through and runbook review.
