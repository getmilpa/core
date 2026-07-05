# Contributing to Milpa Core

Thanks for your interest in contributing! Milpa Core is the framework-agnostic
heart of the Milpa framework — attributes, events, value objects, enums,
interfaces, and the verification seam. No Doctrine, no product coupling.

## Getting started

```bash
composer install
vendor/bin/phpunit --testsuite Core
vendor/bin/phpstan analyse src
php tools/validate-docblocks.php
```

These run in CI on PHP 8.3 and 8.4 (alongside `composer validate --strict` and a
`php -l` syntax pass); run them locally before opening a PR.

## Guidelines

- **PHP >= 8.3**, with `declare(strict_types=1);` in every file.
- **Document every public symbol.** A public class/interface/enum/trait or public
  method without a DocBlock summary fails CI (`tools/validate-docblocks.php`).
  Trivial accessors and magic methods are exempt.
- **Framework-agnostic core.** Do not introduce a dependency on Doctrine, a
  concrete container, or any product/plugin code.
- **[Conventional Commits](https://www.conventionalcommits.org/)** — releases and
  the CHANGELOG are generated automatically from commit messages. Use
  `feat:` / `fix:` / `docs:` / `chore:` etc.; a breaking change to a public
  interface or capability schema is a `feat!:` / `BREAKING CHANGE:` (MAJOR).

## Pull requests

Keep PRs focused, add tests for behavior changes, and make sure the four commands
above are green. A maintainer will review and, once merged to `main`,
release-please will handle versioning.

## License

By contributing, you agree that your contributions are licensed under the
[Apache License 2.0](LICENSE).
