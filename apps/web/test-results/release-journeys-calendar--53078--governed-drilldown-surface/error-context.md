# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: release-journeys.spec.ts >> calendar page loads governed drilldown surface
- Location: e2e\release-journeys.spec.ts:12:1

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: getByText('Client review')
Expected: visible
Error: strict mode violation: getByText('Client review') resolved to 2 elements:
    1) <button type="button" class="block w-full truncate rounded-md bg-muted px-2 py-1 text-left text-xs font-medium text-text">Client review</button> aka getByRole('button', { name: 'Client review', exact: true })
    2) <div class="font-semibold text-text">Client review</div> aka getByText('Client review').nth(1)

Call log:
  - Expect "toBeVisible" with timeout 5000ms
  - waiting for getByText('Client review')

```

# Page snapshot

```yaml
- generic [ref=e3]:
  - complementary [ref=e4]:
    - generic [ref=e5]:
      - generic [ref=e6]:
        - generic [ref=e7]: Snowball
        - generic [ref=e8]: CRM Platform
      - generic [ref=e9]:
        - button "Toggle sidebar" [ref=e10] [cursor=pointer]:
          - img [ref=e11]
        - generic [ref=e14]: Release candidate
    - navigation "Primary" [ref=e15]:
      - link "Homepage" [ref=e16] [cursor=pointer]:
        - /url: /app/dashboard
        - img [ref=e17]
        - text: Homepage
      - link "My Profile" [ref=e22] [cursor=pointer]:
        - /url: /app/settings/profile
        - img [ref=e23]
        - text: My Profile
      - link "Accounts" [ref=e26] [cursor=pointer]:
        - /url: /app/settings/accounts
        - img [ref=e27]
        - text: Accounts
      - link "Branding" [ref=e30] [cursor=pointer]:
        - /url: /app/settings/theme
        - img [ref=e31]
        - text: Branding
      - link "Industry Config" [ref=e37] [cursor=pointer]:
        - /url: /app/settings/industry-configurations
        - img [ref=e38]
        - text: Industry Config
      - link "Clients" [ref=e43] [cursor=pointer]:
        - /url: /app/clients
        - img [ref=e44]
        - text: Clients
      - link "Imports" [ref=e49] [cursor=pointer]:
        - /url: /app/imports
        - img [ref=e50]
        - text: Imports
      - link "Calendar" [ref=e53] [cursor=pointer]:
        - /url: /app/calendar
        - img [ref=e54]
        - text: Calendar
      - link "Communications" [ref=e56] [cursor=pointer]:
        - /url: /app/communications
        - img [ref=e57]
        - text: Communications
      - link "Rules Library" [ref=e59] [cursor=pointer]:
        - /url: /app/rules
        - img [ref=e60]
        - text: Rules Library
      - link "Workflow Builder" [ref=e62] [cursor=pointer]:
        - /url: /app/workflows
        - img [ref=e63]
        - text: Workflow Builder
      - link "Audit" [ref=e67] [cursor=pointer]:
        - /url: /app/audit
        - img [ref=e68]
        - text: Audit
    - generic [ref=e70]:
      - generic [ref=e71]: Tenant
      - generic [ref=e72]: Default Workspace
      - generic [ref=e73]: Mortgage resolves through mortgage-v1.
  - generic [ref=e74]:
    - banner [ref=e75]:
      - generic [ref=e76]:
        - generic [ref=e77]:
          - button "Collapse navigation" [ref=e78] [cursor=pointer]:
            - img [ref=e79]
          - generic [ref=e81]:
            - generic [ref=e82]: Workspace
            - generic [ref=e83]: Default Workspace
            - generic [ref=e84]: Mortgage · mortgage-v1
        - generic [ref=e85]:
          - button "Notifications" [ref=e87] [cursor=pointer]:
            - img [ref=e88]
            - text: Notifications
          - button "Sign out" [ref=e91] [cursor=pointer]:
            - img [ref=e92]
            - text: Sign out
    - generic [ref=e95]:
      - main [ref=e96]:
        - generic [ref=e97]:
          - generic [ref=e98]:
            - generic [ref=e99]:
              - heading "Calendar & Tasks" [level=1] [ref=e100]
              - paragraph [ref=e101]: Operational calendar drilldown for day selection, event detail, linked files, and durable task history.
            - button "Create event" [ref=e103] [cursor=pointer]
          - generic [ref=e104]:
            - generic [ref=e105]:
              - generic [ref=e107]:
                - generic [ref=e108]:
                  - generic [ref=e109]: Operational calendar
                  - generic [ref=e110]: Selected-day drilldown and event chips render from canonical calendar APIs.
                - generic [ref=e111]:
                  - button [ref=e112] [cursor=pointer]:
                    - img [ref=e113]
                  - generic [ref=e115]: March 2026
                  - button [ref=e116] [cursor=pointer]:
                    - img [ref=e117]
              - generic [ref=e119]:
                - generic [ref=e120]:
                  - generic [ref=e121]: Sun
                  - generic [ref=e122]: Mon
                  - generic [ref=e123]: Tue
                  - generic [ref=e124]: Wed
                  - generic [ref=e125]: Thu
                  - generic [ref=e126]: Fri
                  - generic [ref=e127]: Sat
                - generic [ref=e128]:
                  - button "1" [ref=e129] [cursor=pointer]:
                    - generic [ref=e131]: "1"
                  - button "2" [ref=e132] [cursor=pointer]:
                    - generic [ref=e134]: "2"
                  - button "3" [ref=e135] [cursor=pointer]:
                    - generic [ref=e137]: "3"
                  - button "4" [ref=e138] [cursor=pointer]:
                    - generic [ref=e140]: "4"
                  - button "5" [ref=e141] [cursor=pointer]:
                    - generic [ref=e143]: "5"
                  - button "6" [ref=e144] [cursor=pointer]:
                    - generic [ref=e146]: "6"
                  - button "7" [ref=e147] [cursor=pointer]:
                    - generic [ref=e149]: "7"
                  - button "8" [ref=e150] [cursor=pointer]:
                    - generic [ref=e152]: "8"
                  - button "9" [ref=e153] [cursor=pointer]:
                    - generic [ref=e155]: "9"
                  - button "10" [ref=e156] [cursor=pointer]:
                    - generic [ref=e158]: "10"
                  - button "11" [ref=e159] [cursor=pointer]:
                    - generic [ref=e161]: "11"
                  - button "12" [ref=e162] [cursor=pointer]:
                    - generic [ref=e164]: "12"
                  - button "13" [ref=e165] [cursor=pointer]:
                    - generic [ref=e167]: "13"
                  - button "14" [ref=e168] [cursor=pointer]:
                    - generic [ref=e170]: "14"
                  - button "15" [ref=e171] [cursor=pointer]:
                    - generic [ref=e173]: "15"
                  - button "16" [ref=e174] [cursor=pointer]:
                    - generic [ref=e176]: "16"
                  - button "17" [ref=e177] [cursor=pointer]:
                    - generic [ref=e179]: "17"
                  - button "18" [ref=e180] [cursor=pointer]:
                    - generic [ref=e182]: "18"
                  - button "19" [ref=e183] [cursor=pointer]:
                    - generic [ref=e185]: "19"
                  - button "20" [ref=e186] [cursor=pointer]:
                    - generic [ref=e188]: "20"
                  - button "21" [ref=e189] [cursor=pointer]:
                    - generic [ref=e191]: "21"
                  - button "22" [ref=e192] [cursor=pointer]:
                    - generic [ref=e194]: "22"
                  - button "23" [ref=e195] [cursor=pointer]:
                    - generic [ref=e197]: "23"
                  - button "24" [ref=e198] [cursor=pointer]:
                    - generic [ref=e200]: "24"
                  - button "25" [ref=e201] [cursor=pointer]:
                    - generic [ref=e203]: "25"
                  - button "26" [ref=e204] [cursor=pointer]:
                    - generic [ref=e206]: "26"
                  - button "27" [ref=e207] [cursor=pointer]:
                    - generic [ref=e209]: "27"
                  - button "28" [ref=e210] [cursor=pointer]:
                    - generic [ref=e212]: "28"
                  - button "29" [ref=e213] [cursor=pointer]:
                    - generic [ref=e215]: "29"
                  - button "30" [ref=e216] [cursor=pointer]:
                    - generic [ref=e218]: "30"
                  - button "31 1 Client review" [ref=e219] [cursor=pointer]:
                    - generic [ref=e220]:
                      - generic [ref=e221]: "31"
                      - generic [ref=e222]: "1"
                    - button "Client review" [ref=e224]
                  - button "1" [ref=e225] [cursor=pointer]:
                    - generic [ref=e227]: "1"
                  - button "2" [ref=e228] [cursor=pointer]:
                    - generic [ref=e230]: "2"
                  - button "3" [ref=e231] [cursor=pointer]:
                    - generic [ref=e233]: "3"
                  - button "4" [ref=e234] [cursor=pointer]:
                    - generic [ref=e236]: "4"
                  - button "5" [ref=e237] [cursor=pointer]:
                    - generic [ref=e239]: "5"
                  - button "6" [ref=e240] [cursor=pointer]:
                    - generic [ref=e242]: "6"
                  - button "7" [ref=e243] [cursor=pointer]:
                    - generic [ref=e245]: "7"
                  - button "8" [ref=e246] [cursor=pointer]:
                    - generic [ref=e248]: "8"
                  - button "9" [ref=e249] [cursor=pointer]:
                    - generic [ref=e251]: "9"
                  - button "10" [ref=e252] [cursor=pointer]:
                    - generic [ref=e254]: "10"
                  - button "11" [ref=e255] [cursor=pointer]:
                    - generic [ref=e257]: "11"
            - generic [ref=e258]:
              - generic [ref=e260]:
                - generic [ref=e261]:
                  - generic [ref=e262]: Tuesday, March 31
                  - generic [ref=e263]: Selected-day workflow with canonical event and task summaries.
                - generic [ref=e264]:
                  - generic [ref=e265]: 1 events
                  - generic [ref=e266]: 1 open tasks
                  - generic [ref=e267]: 0 completed
              - generic [ref=e271]:
                - generic [ref=e272]:
                  - generic [ref=e273]:
                    - img [ref=e274]
                    - generic [ref=e278]: Client review
                    - generic [ref=e279]: appointment
                  - generic [ref=e280]: 10:00 AM – 10:30 AM
                  - generic [ref=e281]: Review intake completeness
                  - generic [ref=e282]:
                    - generic [ref=e283]:
                      - img [ref=e284]
                      - text: Acme Mortgage
                    - generic [ref=e287]: 1 open
                    - generic [ref=e288]: 0 done
                - generic [ref=e289]:
                  - button "Open event" [ref=e290] [cursor=pointer]
                  - link "Open file" [ref=e291] [cursor=pointer]:
                    - /url: /app/clients/client-1/events
                    - img [ref=e292]
                    - text: Open file
      - complementary [ref=e294]:
        - generic [ref=e295]:
          - generic [ref=e296]:
            - generic [ref=e297]: Notification tray
            - generic [ref=e298]: Persistent operational feedback surfaces here without deleting source-event truth.
          - generic [ref=e300]:
            - generic [ref=e301]: ∅
            - heading "No notifications yet" [level=2] [ref=e302]
            - paragraph [ref=e303]: Persistent notifications will appear here when workflow, import, or operational events need attention.
```

# Test source

```ts
  1  | import { expect, test } from '@playwright/test'
  2  | import { installAuthenticatedAppMocks } from './support/mock-api'
  3  | 
  4  | test('homepage renders governed summary content', async ({ page }) => {
  5  |   await installAuthenticatedAppMocks(page)
  6  |   await page.goto('/app/dashboard')
  7  | 
  8  |   await expect(page.getByRole('heading', { name: 'Homepage' })).toBeVisible()
  9  |   await expect(page.getByRole('link', { name: 'View clients' })).toBeVisible()
  10 | })
  11 | 
  12 | test('calendar page loads governed drilldown surface', async ({ page }) => {
  13 |   await installAuthenticatedAppMocks(page)
  14 |   await page.goto('/app/calendar?date=2026-03-31')
  15 | 
  16 |   await expect(page.getByRole('heading', { name: 'Calendar & Tasks' })).toBeVisible()
> 17 |   await expect(page.getByText('Client review')).toBeVisible()
     |                                                 ^ Error: expect(locator).toBeVisible() failed
  18 | })
```