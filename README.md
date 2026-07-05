# Milpa Core

> The framework-agnostic core of **Milpa** — a modular, AI-native PHP framework.
> *Siembra módulos, cosecha aplicaciones.*

[![CI](https://github.com/getmilpa/core/actions/workflows/ci.yml/badge.svg)](https://github.com/getmilpa/core/actions/workflows/ci.yml)
[![Packagist](https://img.shields.io/packagist/v/milpa/core.svg)](https://packagist.org/packages/milpa/core)
[![PHP](https://img.shields.io/badge/php-%E2%89%A5%208.3-777bb4.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-Apache--2.0-blue.svg)](LICENSE)

`milpa/core` is the dependency-light heart of the Milpa framework: the attributes,
events, value objects, enums, interfaces, and the verification seam that every Milpa
module builds on. **No Doctrine, no HTTP client, no product coupling** — just the
contracts and primitives, so you can depend on them from anywhere.

## Install

```bash
composer require milpa/core
```

## Requirements

- PHP **≥ 8.3**

## Quick example

Value objects are immutable and self-validating. For instance, `SemanticVersion`:

```php
use Milpa\app\ValueObjects\SemanticVersion;

$v = SemanticVersion::parse('2.4.1');

$v->satisfies('^2.0');                              // true
$v->greaterThan(SemanticVersion::parse('2.4.0'));   // true
$v->incrementMinor();                              // 2.5.0 (a new instance)
$v->isStable();                                    // true — no pre-release tag
```

## What's inside

| Namespace | What it provides |
|-----------|------------------|
| `Milpa\app\Attributes` | Declarative attributes (`RegisterService`, `BusinessRule`, `Subscribe`, …) |
| `Milpa\app\Events` | Framework event contracts + dispatch primitives |
| `Milpa\app\ValueObjects` | Immutable, validated values (`SemanticVersion`, capability & verification VOs, …) |
| `Milpa\app\Enums` | Shared enums (`Roles`, `Events`, …) |
| `Milpa\app\Interfaces` | Core contracts (`MilpaEventDispatcherInterface`, `PluginManifestInterface`, …) |
| `Milpa\app\Support` | Small framework-agnostic helpers |

Every public symbol carries a DocBlock — the API reference is generated straight from them.

## Documentation

**Full API reference: [getmilpa.github.io/core](https://getmilpa.github.io/core/)** — generated
straight from the source DocBlocks and dressed with the Milpa design system.

## Contributing

Contributions are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md). Please report security
issues via [SECURITY.md](SECURITY.md), and note that this project follows a
[Code of Conduct](CODE_OF_CONDUCT.md).

## License

[Apache-2.0](LICENSE) © the Milpa authors.
