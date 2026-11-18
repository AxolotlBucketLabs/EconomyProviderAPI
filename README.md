# EconomyProviderAPI

Economy provider interface and DevTools virion for PocketMine-MP plugins

[![CI](https://github.com/AxolotlBucketLabs/EconomyProviderAPI/actions/workflows/ci.yml/badge.svg)](https://github.com/AxolotlBucketLabs/EconomyProviderAPI/actions/workflows/ci.yml)
[![Latest release](https://img.shields.io/github/v/release/AxolotlBucketLabs/EconomyProviderAPI)](https://github.com/AxolotlBucketLabs/EconomyProviderAPI/releases/latest)
[![License](https://img.shields.io/github/license/AxolotlBucketLabs/EconomyProviderAPI)](https://github.com/AxolotlBucketLabs/EconomyProviderAPI/blob/main/LICENSE)
[![Discord](https://img.shields.io/discord/1568295845374206052?label=Discord&logo=discord&logoColor=white&color=5865F2)](https://discord.gg/Pk4FrNvHnM)

The repository contains the `EconomyProviderAPI` virion, a companion plugin for server installation, and a small example.

## Migration from previous versions

Version 3.0.0 renames the virion, entry class, and PHP namespace. Change `devtools.yml` from `EcoAPI` to `EconomyProviderAPI`, update imports from `AxolotlBucketLabs\EcoAPI\` to `AxolotlBucketLabs\EconomyProviderAPI\`, and replace `EcoAPI::` calls with `EconomyProviderAPI::`. The Composer package is `axolotlbucketlabs/economy-provider-api`. If upgrading from 1.x, replace the old `nhanaz/ecoapi` package with this package.

## Compatibility and providers

`virion.yml` and `plugin.yml` declare minimum API `5.0.0`, and Composer requires PHP `^8.1`. The [build workflow](.github/workflows/ci.yml) checks a pinned Axolotl-PM revision and the `5.0.0` API floor. The full Axolotl-PM 5.x range has not been verified.

| Provider | Registration names | Runtime requirement |
|---|---|---|
| [EconomyAPI](https://github.com/ACM-PocketMine-MP/EconomyAPI) | `economyapi`, `economy` | An enabled compatible EconomyAPI plugin |
| [BedrockEconomy](https://github.com/cooldogepm/BedrockEconomy) | `bedrockeconomy`, `bedrock` | An enabled compatible BedrockEconomy plugin |
| [SimpleEconomy](https://github.com/AxolotlBucketLabs/SimpleEconomy) | `simpleeconomy`, `simple` | An enabled SimpleEconomy plugin |
| Player XP levels | `xp`, `exp`, `experience` | No economy plugin |

EconomyProviderAPI can select an external provider only after its plugin is enabled. Declare optional providers in `softdepend` when selecting one during `onEnable()`. The example falls back to built-in XP.

- `XP` uses whole levels as currency and preserves the player's progress within the current level.
- `SimpleEconomy` accepts whole units. Its callback reports the in-memory result while database saving continues asynchronously. Reads may return the configured default before a player's session loads.
- `EconomyAPI` accepts amounts with up to two decimal places. Its callback reports the backend result.
- `BedrockEconomy` uses asynchronous updates. Version `4.0.4` requires Xbox authentication and mishandles amounts with cents from `.01` to `.09`, which EconomyProviderAPI rejects. If a matching `TransactionSubmitEvent` is canceled, the supplied callback receives `false` once.

## Installation and building

For a source based plugin, place a pinned EconomyProviderAPI source checkout under `virions/EconomyProviderAPI` and declare the virion in your plugin's `devtools.yml`:

```yaml
virions:
  - name: EconomyProviderAPI
    version: ^3.0.0
```

The [`example/`](example/) directory contains this declaration. [DevTools](https://github.com/AxolotlBucketLabs/DevTools) builds the consumer PHAR and shades the EconomyProviderAPI namespace. Pin the source revision and follow the [DevTools build guide](https://github.com/AxolotlBucketLabs/DevTools/blob/main/docs/github-actions.md) for reproducible builds. The repository workflow builds and validates the companion `EconomyProviderAPIPlugin` and shaded example PHARs, then uploads them after its checks pass. An artifact is not a stable release.

## Basic usage

Call `EconomyProviderAPI::init()` during your plugin's enable phase, then choose an available provider:

```php
use AxolotlBucketLabs\EconomyProviderAPI\EconomyProviderAPI;

EconomyProviderAPI::init();
$provider = EconomyProviderAPI::detectProvider();
if ($provider === null) {
    // No supported provider is available.
    return;
}
$provider->getMoney($player, static function (float|int $balance): void {
    // Use the balance after the provider invokes the callback.
});
```

`getProvider(string $name)` selects a registered provider by name. It throws `UnknownProviderException` for an unknown name and `MissingProviderDependencyException` when that provider is unavailable. The interface also offers `setMoney()`, `addMoney()` and `takeMoney()` with completion callbacks. Check callback results before claiming that an operation succeeded.

EconomyProviderAPI does not provide an atomic transfer between two accounts. Do not implement player payments by chaining `takeMoney()` and `addMoney()` and claiming that a later refund makes the operation safe. Use a provider with a verified transaction contract for transfers.

## Custom providers

Implement [`EconomyProvider`](src/AxolotlBucketLabs/EconomyProviderAPI/EconomyProvider.php) and register explicit factory and availability callbacks:

```php
use AxolotlBucketLabs\EconomyProviderAPI\EconomyProviderAPI;
use AxolotlBucketLabs\EconomyProviderAPI\EconomyProvider;

EconomyProviderAPI::registerProvider(
    static fn(): EconomyProvider => new MyProvider(),
    static fn(): bool => MyProvider::isAvailable(),
    "myprovider", "myalias"
);
```

This registration signature replaces the older class string argument before the first stable release. The explicit factory keeps custom construction in the consuming plugin and lets DevTools shade built in provider references safely. Aliases share one registration, so automatic detection considers that provider once.

## Verification and support

CI runs PHPStan at maximum level, PHP lint, provider fixtures, PHAR validation and an Axolotl-PM `5.0.0` startup smoke test. The full server version range, connected BedrockEconomy transactions and Bedrock game UI remain unverified.

Keep player data backups and a previously verified build during upgrades. For support, use [AxolotlBucketLabs Discord](https://discord.gg/Pk4FrNvHnM). See the [LICENSE](LICENSE) file for the full Apache License 2.0 text.
