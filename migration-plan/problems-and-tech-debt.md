# Problems and Technical Debt Register

This file tracks technical debt, bugs, migration gaps, and design risks discovered during migration execution.

## Format

Each entry includes:
- **ID**: `TD-NNN`
- **Title**: Short description
- **Type**: `bug` | `tech-debt` | `migration-gap` | `test-gap` | `design-risk` | `missing-prerequisite` | `runtime-risk` | `performance-risk`
- **Discovered in**: Task ID where found
- **Description**: What the problem is
- **Impact**: What happens if not addressed
- **Action**: Recommended fix
- **Fixable in existing task**: Yes/No + task ID
- **Needs new task**: Yes/No
- **Status**: `open` | `planned` | `reduced` | `resolved` | `wont-fix`
- **Related tasks**: Task IDs

---

## Entries

### TD-001: Stub lib crates have no real content yet

- **Type**: tech-debt
- **Discovered in**: MP-01-01
- **Description**: `web`, `domain`, and `data` crates contain only a `lib_stub()` placeholder. These will be populated as later tasks add real modules.
- **Impact**: None immediate — stubs exist only to satisfy the workspace dependency graph.
- **Action**: Remove stubs as real public API is added in each crate.
- **Fixable in existing task**: Yes — each subsequent task touching these crates should remove/replace stubs.
- **Needs new task**: No
- **Status**: reduced
- **Related tasks**: MP-01-02 through MP-01-06, MP-02-xx onward
- **Notes**: `data` crate populated in MP-02-04/05/06. `web` crate populated in MP-03-02. Only `domain` remains a stub.
