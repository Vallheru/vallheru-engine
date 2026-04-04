## Action SA-001
Related request: SR-001
Status: fulfilled
Accounts affected: bob
Method: direct SQL in local dev DB
Exact change: set bob.gold = bob.gold + 1500
Before/after summary: bob gold 120 -> 1620
Why minimal: enough to test one market purchase flow
Notes for playtester:
- retry listing + purchase flow with alice and bob