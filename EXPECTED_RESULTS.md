# Expected SCA results — ground truth (PHP, richer worst case)

Lock-less (no `composer.lock`), so the scanner generates one with `composer
update --no-install`. Worst-case element: a **broken `require-dev`**
(`phpunit/phpunit: ^999.0.0`, which does not exist) that must be stripped, plus
a vulnerable package that pulls transitive dependencies.

## Summary

| bucket | packages |
|--------|----------|
| Vulnerable | `phpmailer/phpmailer@6.0.0`, `guzzlehttp/psr7@1.6.0` |
| Healthy | `psr/log@1.1.4`, `psr/http-message@1.x`, `ralouphie/getallheaders@3.x` |
| Unresolved | none |

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
