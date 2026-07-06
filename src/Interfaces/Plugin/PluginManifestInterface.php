<?php

/**
 * This file is part of Milpa Core — the framework-agnostic core of the Milpa PHP framework.
 *
 * (c) TeamX — https://teamx.agency <hola@teamx.agency>
 *
 * @license Apache-2.0
 * @link    https://github.com/getmilpa/core
 */

declare(strict_types=1);

namespace Milpa\Interfaces\Plugin;

use Milpa\ValueObjects\Capability\CapabilityProvision;
use Milpa\ValueObjects\Capability\CapabilityRequirement;
use Milpa\ValueObjects\Capability\CapabilitySuggestion;
use Milpa\ValueObjects\SemanticVersion;

/**
 * Read-only accessor for a plugin's `milpa.json` manifest: identity,
 * versioning, capability contracts ({@see getProvides()}, {@see getRequires()},
 * {@see getSuggests()}), and dependency/environment requirements.
 *
 * This is the primary source of plugin metadata when a manifest file exists;
 * plugins without one fall back to `#[PluginMetadata]` attributes.
 */
interface PluginManifestInterface
{
    /**
     * Create a manifest from a milpa.json file path.
     */
    public static function fromPath(string $manifestPath): self;

    /**
     * Vendor/package name (e.g., "acme/mail-plugin").
     */
    public function getName(): string;

    /**
     * Human-readable display name (e.g., "Mail Plugin").
     */
    public function getDisplayName(): string;

    /**
     * Short human-readable summary of what the plugin does.
     */
    public function getDescription(): string;

    /**
     * The plugin's parsed semantic version.
     */
    public function getVersion(): SemanticVersion;

    /**
     * Plugin type: Web, CLI, Mixed, Service.
     */
    public function getType(): string;

    /**
     * PHP namespace (e.g., "Acme\Plugins\ExamplePlugin").
     */
    public function getNamespace(): string;

    /**
     * Main plugin file relative to plugin directory (e.g., "ExamplePlugin.php").
     */
    public function getEntrypoint(): string;

    /**
     * The interfaces/services this plugin provides to the capability system.
     *
     * @return array<class-string> Interfaces/services this plugin provides
     */
    public function getProvides(): array;

    /**
     * The interfaces/services this plugin cannot boot without.
     *
     * @return array<class-string> Required interfaces/services (hard dependency)
     */
    public function getRequires(): array;

    /**
     * The interfaces/services this plugin can use if available but does not
     * strictly need.
     *
     * @return array<class-string> Optional interfaces/services (soft dependency)
     */
    public function getSuggests(): array;

    /**
     * The `provides` capabilities as typed records (id, interface, constraint),
     * parsed from the canonical `capabilities.provides` key. Prefer this over
     * {@see getProvides()} — it exposes the validated value objects rather than
     * bare class-strings.
     *
     * @return list<CapabilityProvision>
     */
    public function getProvidedCapabilities(): array;

    /**
     * The `requires` capabilities as typed records (hard dependencies).
     *
     * @return list<CapabilityRequirement>
     */
    public function getRequiredCapabilities(): array;

    /**
     * The `suggests` capabilities as typed records (soft dependencies with an
     * optional fallback).
     *
     * @return list<CapabilitySuggestion>
     */
    public function getSuggestedCapabilities(): array;

    /**
     * Composer packages this plugin depends on, beyond the framework itself.
     *
     * @return array<string, string> Package => constraint (e.g., "symfony/mailer" => "^7.0")
     */
    public function getComposerDependencies(): array;

    /**
     * Other Milpa plugins this plugin depends on.
     *
     * @return array<string, string> Plugin name => constraint (e.g., "acme/example-plugin" => "^2.0")
     */
    public function getPluginDependencies(): array;

    /**
     * The minimum Milpa/Milpa framework version this plugin requires, or
     * null if unconstrained.
     */
    public function getMinMilpaVersion(): ?string;

    /**
     * The PHP version constraint this plugin requires (e.g. ">=8.2"), or
     * null if unconstrained.
     */
    public function getPhpVersion(): ?string;

    /**
     * Environment variable names the plugin expects to be set.
     *
     * @return array<string> Required environment variables
     */
    public function getEnvVars(): array;

    /**
     * Migrations directory name relative to plugin root (e.g., "Migrations").
     */
    public function getMigrationsDirectory(): ?string;

    /**
     * The plugin's declared authors.
     *
     * @return array<string, string> Author list with name/email
     */
    public function getAuthors(): array;

    /**
     * Convert to the legacy metadata-array shape (`Plugins::$plugins`) that some
     * consumers still read instead of the typed accessors above.
     *
     * @return array{name:string, version:string, author:string, site:string, type:string, provides:array<class-string>, requires:array<class-string>, suggests:array<class-string>}
     */
    public function toMetadataArray(): array;

    /**
     * Validate the manifest. Throws on invalid data.
     *
     * @throws \InvalidArgumentException If required fields are missing or invalid
     */
    public function validate(): void;

    /**
     * Get the raw manifest data array.
     *
     * @return array<string, mixed>
     */
    public function getRawData(): array;
}
