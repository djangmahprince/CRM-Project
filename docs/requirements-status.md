# SRS Requirements Status

Primary acceptance source: CRM Software Requirements Specification (Sales & Service Cloud) as summarized in `docs/DEVELOPMENT_PLAN.md` and the completion brief. Status values: **COMPLETE**, **PARTIAL**, **BLOCKED**, **NOT IMPLEMENTED**.

COMPLETE means end-to-end behavior works with server-side enforcement and automated coverage where noted — not merely that a route/page exists.

| ID | Description | Priority | Location | Tests | Status | Notes |
| --- | --- | --- | --- | --- | --- | --- |
| FR-AUTH-001 | Login / logout | P0 | `AuthenticatedSessionController`, Auth Vue pages | `AuthenticationTest` | COMPLETE | |
| FR-AUTH-002 | Password hashing & reset | P0 | Fortify-style reset controllers, mail | `PasswordResetTest` | COMPLETE | Token expiry via config |
| FR-AUTH-003 | RBAC + record ownership/sharing | P0 | Spatie roles, policies, `HasCrmVisibility` | `LeadVisibilityTest`, `RecordShareTest` | COMPLETE | Roles: Admin, Sales Manager, Sales Rep, Service Rep, Read-Only |
| FR-AUTH-004 | Lockout / session idle / remember-me | P0 | `LoginRequest`, `EnsureSessionIsActive` | `AuthenticationLockoutTest` | COMPLETE | |
| FR-LEAD-001 | Lead CRUD, list, sort, filter, search, columns, bulk | P0 | `LeadController`, `Leads/*` | `LeadCrudTest`, `BulkActionTest` | COMPLETE | Column prefs localStorage |
| FR-LEAD-002 | Lead detail, activities, notes/attachments, log call | P0/P1 | `Leads/Show`, notes/attachments, task create deep-link | `NoteAttachmentTest` | COMPLETE | Log Call → completed Task |
| FR-LEAD-003 | Lead conversion (match/create account, contact, optional opp, transfer activities, read-only) | P1 | `ConvertLeadAction`, `LeadConversionController` | `LeadConversionTest` | COMPLETE | Transactional |
| FR-ACCT-001 | Account CRUD + relationships | P0 | `AccountController` | `AccountCrudTest` | COMPLETE | |
| FR-ACCT-002 | Account hierarchy / parent | P2 | `AccountHierarchyController`, `Accounts/Hierarchy` | `AccountHierarchyTest` | COMPLETE | |
| FR-CONT-001 | Contact CRUD + account / reports-to | P0 | `ContactController` | `ContactCrudTest` | COMPLETE | |
| FR-OPP-001 | Opportunity CRUD, stages, probability, expected revenue, history | P0 | `Opportunity` model boot, Show stage path | `OpportunityCrudTest`, `OpportunityExpectedRevenueTest`, `OpportunityCloneTest` | COMPLETE | Expected Revenue = Amount × Probability |
| FR-OPP-002 | Opportunity clone + stage progression UI | P1 | `opportunities.clone`, `opportunities.stage` | `OpportunityCloneTest` | COMPLETE | |
| FR-CASE-001 | Case CRUD, case number, close/reopen, closed read-only | P0 | `CaseController` | `CaseCrudTest` | COMPLETE | |
| FR-TASK-001 | Task CRUD, assignment, due, status, related polymorphic | P1 | `TaskController` | `TaskCrudTest` | COMPLETE | |
| FR-EVENT-001 | Calendar day/week/month/table, create/edit, drag reschedule | P1 | `CalendarController`, `Calendar/Index`, `EventController` | `EventCalendarTest` | COMPLETE | Grid + drop reschedule |
| FR-SRCH-001 | Global search suggestions (2+ chars) + results | P0 | `SearchController`, AppLayout | `SearchTest` | COMPLETE | |
| FR-SRCH-002 | Advanced search AND/OR + saved searches | P2 | `AdvancedSearchController`, `SavedSearchController` | `AdvancedSearchTest` | COMPLETE | |
| FR-HOME-001 | Home dashboard real metrics, funnel, source, today tasks/events | P0 | `DashboardController`, Chart.js | `DashboardTest` | COMPLETE | Charts + key deals + recent + date filters |
| FR-HOME-002 | Rule-based assistant recommendations | P1 | `HomeAssistant` | `HomeAssistantTest` | COMPLETE | |
| FR-RPT-001 | Prebuilt reports + CSV/Excel/PDF export | P0/P1 | `ReportController`, `ReportExporter` | `ReportTest`, `ReportExportFormatsTest` | COMPLETE | PDF is lightweight text PDF |
| FR-RPT-002 | Report builder | P1 | `ReportBuilderController` | `ReportBuilderTest` | COMPLETE | |
| FR-RPT-003 | Report subscriptions via schedule | P2 | `SendReportSubscriptions` job | `ReportSubscriptionTest` | COMPLETE | Hourly schedule |
| FR-DASH-001 | Configurable CRM dashboards | P2 | `CrmDashboardController` | `CrmDashboardTest` | COMPLETE | |
| FR-IMP-001 | CSV import | P1 | `ImportController` | `ImportCsvTest` | COMPLETE | |
| FR-FILE-001 | Attachments type/size + download/preview | P1/P2 | `AttachmentController` | `NoteAttachmentTest`, `AttachmentPreviewTest` | COMPLETE | Parent-record ACL; upload requires update; private disk; no virus scanner |
| FR-NOTIF-001 | Assignment / due / ownership mail + in-app inbox | P1 | Notifications + `NotificationController` | `NotificationInboxTest` | COMPLETE | Alerts bell wired |
| FR-MFA-001 | Optional TOTP MFA | P2 | `MfaController` | `MfaTest` | COMPLETE | |
| FR-API-001 | REST CRUD for core objects + Sanctum | P2 | `routes/api.php`, `docs/openapi.yaml` | `Api*Test` | COMPLETE | Lead API auth aligned; OpenAPI outline |
| FR-GDPR-001 | Admin export/delete | P2 | `GdprController` | `GdprTest` | COMPLETE | |
| FR-WF-001 | Workflow rules create_task | Out of v1 MVP | `WorkflowAutomationService` | `WorkflowAutomationTest` | COMPLETE | Narrow slice |
| NFR-SEC-001 | CSRF, mass assignment, upload allowlist, policies | — | Form Requests, policies | Feature suite | COMPLETE | |
| NFR-PERF-001 | Indexes, pagination, eager load | — | migrations, list controllers | — | PARTIAL | Measured locally; no load test harness |
| NFR-TEST-001 | Automated coverage toward 80% | — | `tests/` | Full suite | PARTIAL | Suite green; PCOV/Xdebug not installed for coverage % |
| NFR-DOCS-001 | README, deploy, backup, requirements | — | `docs/*`, README | — | COMPLETE | |
| NFR-A11Y-001 | Keyboard/focus/ARIA basics | — | Layout, dialogs, labels | Manual UI | PARTIAL | Improved; full WCAG audit not run |
| NFR-RESP-001 | Responsive 320–1440 | — | AppLayout mobile nav, tables | Manual UI | PARTIAL | Mobile nav + overflow fixes; not all viewports automated |

## Deviations

- Product UI is **Inertia**, not a separate SPA consuming REST exclusively. REST exists for integrations.
- Opportunity Products, Quotes, Campaigns, native mobile apps, and ML insights remain out of v1.
- Attachment antivirus is **not** claimed; architecture uses allowlisted MIME/size + private storage.
- Report PDF export is a simple generated PDF suitable for tabular text, not a pixel-perfect print engine.

## Last verification

- `php artisan migrate:fresh --seed` — clean schema + demo data
- `php artisan test --compact` — see latest run in session notes
- Browser login + Home dashboard inspected against live DB metrics
