# Expected SCA results — ground truth (PHP, richer worst case)

Lock-less (no `composer.lock`), so the scanner generates one with `composer
update --no-install`. Worst-case element: a **broken `require-dev`**
(`phpunit/phpunit: ^999.0.0`, which does not exist) that must be stripped, plus
a vulnerable package that pulls transitive dependencies.

## Summary

| bucket | packages |
|--------|----------|
| Vulnerable | `phpmailer/phpmailer@6.0.0`, `guzzlehttp/psr7@1.6.0` |
| Healthy | `psr/log@1.1.4`, `psr/http-message@1.x`, `ralouphie/getallheaders@3.x`, `monolog/monolog@2.9.x` (NOT 3.x), `harshit905/local-lib@1.0.0` |
| Unresolved | none |

`php` and `ext-json` are platform packages and must NOT appear as packages.

## Vulnerabilities
- **`phpmailer/phpmailer@6.0.0`** — multiple advisories (`CVE-2020-13625`,
  `CVE-2021-3603`, `CVE-2021-34551`). Zero package dependencies.
- **`guzzlehttp/psr7@1.6.0`** — improper header parsing, `CVE-2022-24775`
  (`GHSA-q559-8m2m-g86r`), fixed in 1.8.4. This one has dependencies (below).

## Healthy — includes transitives (the new thing to analyze)
- **`psr/log@1.1.4`** — range `^1.1` resolves to 1.1.4, no advisories.
- **`psr/http-message`** and **`ralouphie/getallheaders`** — transitive
  dependencies of `guzzlehttp/psr7`, pulled in only by generation, both healthy.
  Their presence proves the scanner discovered transitives, not just direct deps.

## Worst-case feature — broken `require-dev`
`require-dev` names `phpunit/phpunit: ^999.0.0`, which does not exist.
- **PASS (require-dev stripped):** production tree resolves → the two vulns and
  the healthy set above.
- **FAIL (require-dev NOT stripped):** `composer update` cannot resolve
  `^999.0.0` → whole generation fails → **0 healthy, everything unresolved, 0
  vulnerabilities** (a false all-clear).

## Pass / fail
- PASS: phpmailer and psr7 vulnerable; psr/log, psr/http-message,
  ralouphie/getallheaders healthy; 0 unresolved.
- FINDINGS to flag: missing transitives (only direct deps resolved), 0 vulns
  (false all-clear), or any invented version.

## New edge case (regression re-test) — local `path` repository
`repositories` declares a `type: path` repo `./local-lib`, and `require` adds
`harshit905/local-lib: *`. The resolver must copy the whole manifest directory
so `./local-lib/composer.json` is present.
- **PASS:** generation succeeds and `harshit905/local-lib@1.0.0` appears (healthy,
  no advisories) alongside the existing results.
- **FAIL:** composer can't find `./local-lib` → whole generation fails → 0 healthy,
  everything unresolved (this is what the copytree fix prevents).

## Round 2 edge cases

### A. `config.platform.php = 7.4.0` decides a range (`monolog/monolog: ^2.0 || ^3.0`)
monolog 3.x requires PHP >= 8.1; 2.x requires >= 7.2. With the manifest's
`config.platform` honored, composer resolves for PHP 7.4.0 and picks the
latest **2.9.x**. If the scanner runs with `--ignore-platform-reqs` (or
otherwise ignores `config.platform`), it picks **3.x** instead.
- **PASS:** `monolog/monolog@2.9.x`, healthy. (Its only dep `psr/log` stays at
  1.1.4, already present.)
- **FINDING:** `monolog/monolog@3.x` — the scanner ignored `config.platform`,
  so it scanned a version the project can never install.

### B. Platform packages in `require` (`"php": ">=7.4"`, `"ext-json": "*"`)
Analog of Go's `go`/`toolchain` pseudo-modules.
- **PASS:** no `php` or `ext-json` entries anywhere (healthy, vulnerable, or
  unresolved).
- **FAIL:** `php@7.4.0` / `ext-json` listed as packages, or generation fails on
  them.

### Round 2 pass / fail (combined)
- PASS: phpmailer and psr7 vulnerable; psr/log, psr/http-message,
  ralouphie/getallheaders, monolog 2.9.x, local-lib healthy; no platform
  packages; 0 unresolved.
