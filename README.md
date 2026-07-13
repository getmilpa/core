<p align="center">
  <a href="https://github.com/getmilpa">
    <picture>
      <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/getmilpa/core/main/art/lockup/milpa-lockup-v-color-dark.svg">
      <img src="https://raw.githubusercontent.com/getmilpa/core/main/art/lockup/milpa-lockup-v-color-light.svg" alt="Milpa" width="300">
    </picture>
  </a>
</p>

# Milpa Core

> The framework-agnostic **contracts core** of Milpa — a modular PHP runtime for applications operable by **both humans and agents**. Not another web framework: the primitives that let modules declare capabilities, expose tools, and gate actions behind verification.

[![CI](https://github.com/getmilpa/core/actions/workflows/ci.yml/badge.svg)](https://github.com/getmilpa/core/actions/workflows/ci.yml)
[![Packagist](https://img.shields.io/packagist/v/milpa/core.svg)](https://packagist.org/packages/milpa/core)
[![PHP](https://img.shields.io/badge/php-%E2%89%A5%208.3-777bb4.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-Apache--2.0-blue.svg)](LICENSE)
[![Docs](https://img.shields.io/badge/docs-API%20reference-blue.svg)](https://getmilpa.github.io/core/)

`milpa/core` is the dependency-light heart of the framework: the attributes, events,
value objects, enums, interfaces, and the verification seam that every Milpa module
builds on. **No Doctrine, no HTTP client, no framework kernel** — just the contracts and
primitives, so you can depend on them from anywhere.

## Install

```bash
composer require milpa/core
```

## Why Milpa

Most PHP frameworks are built for one kind of caller: a human writing code, or a browser
sending requests. Milpa is built for **two** — humans *and* agents operating the same
application through the same declared surface.

Modules don't just register routes and services. They declare the **capabilities** they
provide and require, expose **tools** an agent can invoke, and gate mutating actions
behind **verification** — deterministic checks, human/agent approvals, or async results.
The contract loop that runs through the whole system:

```
plugin → capability → tool → verification → event → result
```

Three seams in this package make that loop possible:

- **Capability system** — a plugin manifest declares what it *provides*, *requires*, and
  *suggests*, so the runtime can wire modules together and reason about what's installed.
  Each entry comes in either real shape `#[PluginMetadata]` sanctions: a bare interface
  FQCN (legacy) or a structured capability record (`{id, interface, contractVersion, …}`),
  parsed by the capability value objects' `parse()` — mixing both in one list is the
  incremental migration path.
- **Agent tool-readiness** — `ToolProviderInterface` / `ToolRegistryInterface` let a module
  publish declarative tools that both humans and agents can discover and call.
- **Verification seam** — `VerifierInterface` gates a mutating action behind a
  deterministic evaluation, a human/agent approval, or a pending async result that
  resolves through an event.

**Be honest about scope:** this package ships the **contracts and primitives only**. It
does not run an HTTP server or boot an application by itself. The web tier lives in the
sibling **`milpa/http`** package, and the runtime/CLI is **`coa`**. Depend on `milpa/core`
when you want to build against those seams without pulling in a framework.

## Quick example

Value objects are immutable and self-validating. For instance, `SemanticVersion`:

```php
use Milpa\ValueObjects\SemanticVersion;

$v = SemanticVersion::parse('2.4.1');

$v->satisfies('^2.0');                              // true
$v->greaterThan(SemanticVersion::parse('2.4.0'));   // true
$v->incrementMinor();                              // 2.5.0 (a new instance)
$v->isStable();                                    // true — no pre-release tag
```

## What's inside

| Namespace | What it provides |
|-----------|------------------|
| `Milpa\Attributes` | Declarative attributes (`RegisterService`, `BusinessRule`, `Subscribe`, …) |
| `Milpa\Interfaces` | Core contracts, grouped by seam: `Di`, `Plugin`, `Event`, `Config`, `Observability`, `Tooling`, `Verification` |
| `Milpa\ValueObjects` | Immutable, validated values (`SemanticVersion`, capability & verification VOs, …) |
| `Milpa\Enums` | Shared enums (`ApprovalPolicy`, `VerificationStatus`, `DispatcherType`, …) |
| `Milpa\Events` | Framework event contracts + dispatch primitives |
| `Milpa\DTO` | Data-transfer objects passed across seam boundaries |
| `Milpa\Exceptions` | Typed exception hierarchy for the contracts |
| `Milpa\Services` | Framework-agnostic service contracts and helpers |
| `Milpa\Support` | Small framework-agnostic utilities |

Every public symbol carries a DocBlock — the API reference is generated straight from them.

## Requirements

- PHP **≥ 8.3**

## Documentation

**Full API reference: [getmilpa.github.io/core](https://getmilpa.github.io/core/)** — generated
straight from the source DocBlocks and dressed with the Milpa design system.

## Contributing

Contributions are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md). Please report security
issues via [SECURITY.md](SECURITY.md), and note that this project follows a
[Code of Conduct](CODE_OF_CONDUCT.md).

## License

[Apache-2.0](LICENSE) © Rodrigo Vicente - TeamX Agency.

---

Milpa is designed, built, and maintained by **[Rodrigo Vicente - TeamX Agency](https://teamx.agency/?utm_source=github&utm_medium=readme&utm_campaign=milpa&utm_content=core)**.
