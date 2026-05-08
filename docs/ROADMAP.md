# Beyond v0.1.0

Aspirational and infrastructure-adjacent items that are out of scope for the
v0.1.0 library release. Captured here so they aren't lost, but they don't drive
day-to-day work and are intentionally **not** loaded into every Claude session.

The active roadmap (Phase 4+) lives in `PROJECT_PLAN.md` §8.

## Future Considerations (post v1.0)

- **PSR-6/PSR-16 cache** — cache reference data lookups
- **Async/promises** — Guzzle async for parallel requests (multi-rates)
- **Webhook receiver helpers** — DHL ODD callbacks
- **Symfony bundle** — wrapper package `medzuch/dhl-express-symfony-bundle`
- **Laravel package** — wrapper `medzuch/dhl-express-laravel`
- **CLI tool** — diagnostic CLI for testing API access

## CI/CD & Deployment (later phase)

When the library matures, the user has plans to build an app on top of it. That app will use:
- **GitHub Actions** for CI
- **Docker images** pushed to GCR or Amazon ECR
- **Kubernetes** on GKE (Google) or EKS (AWS)
- **JetBrains Gateway** for remote dev (optional)

The library itself only needs:
- GitHub Actions workflow to run tests on PRs (PHP 8.3 matrix)
- Auto-publish to Packagist on tagged releases
- Optional Codecov for coverage badges
