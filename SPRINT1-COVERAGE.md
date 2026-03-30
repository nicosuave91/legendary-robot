# Sprint 1 Coverage Map

## Completed in this artifact

### Repository and module topology
- Root monorepo workspace files
- `apps/web`, `apps/api`, `packages/contracts`, `docs`, `scripts`
- Placeholder feature and module directories for later epics

### Frontend shell bootstrap
- App shell layout
- Collapsible-ready sidebar scaffold
- Sticky header scaffold
- Search placeholder
- Notification tray scaffold
- Toast host
- Protected route boundary
- Auth layout and sign-in scaffold
- Placeholder module routes

### Local design-system foundation
- Local `src/components/ui` wrappers
- Button, input, card, badge, table, dialog, drawer, tabs, page header
- Loading skeleton
- Empty state

### Centralized theme token system
- CSS variable token registry
- Tailwind variable mapping
- Theme provider
- Typography roles using Archivo Black and Roboto token slots

### Laravel modular monolith skeleton
- Shared module
- IdentityAccess module
- Audit module
- Placeholder modules for later epics
- Thin controller / request / resource / service convention
- Tenant context scaffold
- Audit logger scaffold
- Job tenant metadata contract

### Versioned API namespace and contract pipeline
- `/api/v1/auth/*` route scaffold
- OpenAPI contract source in PHP
- Published OpenAPI artifact in `packages/contracts/openapi.json`
- Generated TypeScript client in `apps/web/src/lib/api/generated/client.ts`
- Guard script preventing handwritten API calls

### CI/CD quality gates
- GitHub workflows for web, api, and contracts
- Typecheck/build placeholders for the web app
- PHP lint placeholder for the API
- Contract publication and generated diff checks

### Governance
- ADR template
- Accepted ADRs for modular monolith, design system, and generated client
- Architecture review checklist
- Dependency direction rules

## Intentionally deferred by scope
- Full sign-in business flow
- Real session persistence
- Onboarding flow
- User management screens
- Tenant industry configuration UI
- Homepage business widgets
- Clients, calendar, communications, applications, workflows, imports, and persistent notifications business logic
