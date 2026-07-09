# Changelog

## [0.5.1](https://github.com/getmilpa/core/compare/v0.5.0...v0.5.1) (2026-07-09)


### Features

* promote the lifecycle event VOs into Milpa\Events ([f202fe4](https://github.com/getmilpa/core/commit/f202fe43c006f485e72f0586faea0f4714ed06c7))


### Miscellaneous Chores

* release 0.5.1 ([0826f05](https://github.com/getmilpa/core/commit/0826f053d1c268ec4d39ec2c99ac18604db46554))

## [0.5.0](https://github.com/getmilpa/core/compare/v0.4.0...v0.5.0) (2026-07-08)


### ⚠ BREAKING CHANGES

* event interception seam — InterceptionSlot + stoppable dispatch contract

### Features

* event interception seam — InterceptionSlot + stoppable dispatch contract ([194e46b](https://github.com/getmilpa/core/commit/194e46b05917cd6a52607df9f108c16e500798df))

## [0.4.0](https://github.com/getmilpa/core/compare/v0.3.0...v0.4.0) (2026-07-08)


### ⚠ BREAKING CHANGES

* verification resolution seam + parametrized docs generator

### Features

* verification resolution seam + parametrized docs generator ([a8fbc9c](https://github.com/getmilpa/core/commit/a8fbc9cd8bcdb94ec032b029f921d0314acde164))

## [0.3.0](https://github.com/getmilpa/core/compare/v0.2.1...v0.3.0) (2026-07-07)


### ⚠ BREAKING CHANGES

* capability graph checker + honest contracts (autowiring MAY, async semantics, validating VOs)

### Features

* capability graph checker + honest contracts (autowiring MAY, async semantics, validating VOs) ([03ab027](https://github.com/getmilpa/core/commit/03ab027a0aed711465adf75edfd431d78156281d))

## [0.2.1](https://github.com/getmilpa/core/compare/v0.2.0...v0.2.1) (2026-07-07)


### Bug Fixes

* **docs:** footer credit color via design tokens + version detection on the docs site ([1dce204](https://github.com/getmilpa/core/commit/1dce2049ecc73e1645eda5a037316ed5ae24aa4a))

## 0.2.0 (2026-07-06)


### ⚠ BREAKING CHANGES

* update imports `Milpa\app\X` -> `Milpa\X`, e.g. `use Milpa\app\ValueObjects\SemanticVersion;` becomes `use Milpa\ValueObjects\SemanticVersion;`. No other API changes.

### Features

* flatten namespace Milpa\app\ -&gt; Milpa\, overhaul docs & README ([51860db](https://github.com/getmilpa/core/commit/51860db0e0671d4652c465d24b7a472c0759c643))
* milpa/core initial public release ([739eb51](https://github.com/getmilpa/core/commit/739eb5164799cd138416b56385377285c094cec1))


### Bug Fixes

* drop dead PHPStan ignore for UuidGenerator trait (export CI green) ([f8a2a27](https://github.com/getmilpa/core/commit/f8a2a278929c9630cc7f44b33b14fa2ae28fe01d))


### Miscellaneous Chores

* release 0.1.0 ([6b4bbef](https://github.com/getmilpa/core/commit/6b4bbef97e9b78aa924a71f35b7a99a8d897ffed))

## 0.1.0 (2026-07-05)


### Features

* milpa/core initial public release ([739eb51](https://github.com/getmilpa/core/commit/739eb5164799cd138416b56385377285c094cec1))


### Bug Fixes

* drop dead PHPStan ignore for UuidGenerator trait (export CI green) ([f8a2a27](https://github.com/getmilpa/core/commit/f8a2a278929c9630cc7f44b33b14fa2ae28fe01d))


### Miscellaneous Chores

* release 0.1.0 ([6b4bbef](https://github.com/getmilpa/core/commit/6b4bbef97e9b78aa924a71f35b7a99a8d897ffed))
