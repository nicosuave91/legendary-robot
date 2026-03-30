# Dependency Direction Rules

1. Feature routes may depend on:
   - route metadata
   - local UI wrappers
   - generated API client
   - feature-specific hooks and schemas

2. Feature modules may not depend on:
   - other feature modules' internal components
   - raw HTTP libraries
   - page-local token definitions

3. Laravel modules may depend on:
   - Shared module contracts, DTOs, support utilities
   - another module's published service interface or DTO

4. Laravel modules may not:
   - write directly into another module's tables
   - call another module's controller
   - bypass policies or tenancy checks

5. Background jobs must carry:
   - tenant id
   - actor id or system actor marker
   - correlation id
