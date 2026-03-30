# Architecture Review Checklist

## Boundary controls
- [ ] No product code crosses Laravel module boundaries via direct table writes.
- [ ] Controllers remain thin and delegate business behavior to services.
- [ ] Shared UI imports flow through the local wrapper layer only.
- [ ] Route metadata exists for every new protected route.

## Drift controls
- [ ] New UI uses centralized tokens only.
- [ ] Public API changes update the OpenAPI artifact and generated client.
- [ ] New async or external work uses events, jobs, or listeners instead of request-thread side effects.
- [ ] Tenant context is explicit in any new background execution path.

## Verification controls
- [ ] Backend feature tests cover request validation and resources.
- [ ] Frontend tests cover route guards and shell scaffolding.
- [ ] Contract verification passes.
- [ ] Accessibility smoke checks cover any new shared surface.
