# Ongoing Development Blueprint

This blueprint governs all future development once the closure phase begins. It exists to keep product work aligned across runtime behavior, contracts, frontend consumption, testing, and documentation.

## Core rule

Every meaningful feature or behavior change must update all relevant layers together:

1. domain behavior
2. contract
3. generated client or frontend API surface
4. verification
5. documentation

## Delivery flow

### 1. Define or refine the module boundary
Before implementation, confirm:

- which module owns the behavior
- which route surface exposes it
- which permissions govern it
- whether it changes audit, notification, workflow, or release evidence

If the behavior changes platform assumptions, add or update an ADR.

### 2. Implement backend behavior
Use thin controllers and keep business rules in services, validators, policies, jobs, and projectors where appropriate.

### 3. Publish and reconcile contracts
If response or request shapes change:

- update API contract source
- publish the OpenAPI artifact
- regenerate the frontend client
- verify there is no generated diff remaining

### 4. Update frontend consumption
Consume through the generated API client or documented central wrappers only. Do not add raw fetch/axios patterns outside the approved client boundary.

### 5. Add automated verification
At minimum:

- feature/integration tests for backend behavior
- frontend tests or page-level tests where needed
- release-e2e coverage for critical journeys if the change affects signoff surfaces

### Verification lanes
The repo uses two complementary verification lanes, and they must not be conflated:

1. **Runtime server proof**
   - Seeded PHPUnit feature/integration tests are the authoritative proof for tenant scoping, audit durability, notification persistence, onboarding state, import lifecycle, and governed rule/workflow behavior.
   - If a change affects persisted business state, release confidence comes from this lane first.

2. **Mocked browser shell smoke**
   - Playwright smoke coverage proves route wiring, protected-shell composition, generated-client consumption, and page-level interaction using deterministic mocked fixtures.
   - This lane is intentionally useful, but it is **not** evidence of live API/runtime correctness.

Both lanes matter. Mocked browser smoke can complement runtime proof, but it cannot replace it.

### 6. Update docs
Update:
- module docs
- release notes or release status docs where relevant
- operations docs if config, secrets, queues, or webhooks are affected

## Required safeguards by category

### Contract safeguards
- no API behavior drift without updated contract
- no frontend API consumption outside approved client boundary
- generated diffs must be clean in CI

### Data safeguards
- all new enums and status values are contract changes
- migration impact must be documented
- seed fixture changes must be reflected in testing and release docs
- cross-tenant access must be considered for every new data path

### Design safeguards
- use local UI wrappers
- keep loading, empty, and error states consistent
- do not create one-off shell patterns without justification
- token use must remain centralized

### Documentation safeguards
- every module should have one active current doc
- historical sprint docs belong in archive
- README must describe the current system, not a previous sprint snapshot
- release docs must reflect the actual checked-in state
- mocked smoke suites must be labeled as mocked smoke, not implied live-stack proof

## Pull request checklist

Every PR should declare:

- problem being solved
- module ownership
- user-facing impact
- contract change or “no contract change” justification
- generated client update status
- migration/seed impact
- audit/notification/workflow impact
- tests added or updated
- docs updated
- release risk notes

## Code-review priorities

Reviewers should verify:

- tenant scope integrity
- server ownership of business state
- explicit version binding for governed runtime behavior
- permission alignment between route, shell, and service layers
- audit evidence for important mutations
- contract and frontend drift prevention
- verification lane correctness, especially when browser smoke is mocked

## Release rule

No release is considered valid unless:

- CI is green
- release-critical journeys pass
- runtime server proof exists for release-relevant state transitions
- mocked browser shell smoke passes and is reported accurately as mocked smoke
- docs and contracts are current
- known issues and accepted risks are explicitly updated
