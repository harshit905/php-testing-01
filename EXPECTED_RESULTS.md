# Expected SCA results — ground truth (PHP)

## Summary

| bucket | count | packages |
|--------|-------|----------|
| Vulnerable | 1 | `phpmailer/phpmailer@6.0.0` |
| Healthy | 1 | `psr/log@1.1.4` |
| Unresolved | 0 | — |

Both production packages have no other package dependencies, so the resolved set
is exactly these two.

## Vulnerabilities
- **`phpmailer/phpmailer@6.0.0`** — old, multiple advisories. Expect several,
  likely including `CVE-2020-13625` (Content-Type escaping), `CVE-2021-3603`
  (object injection), `CVE-2021-34551`. Fixed in 6.5.0. Pass condition: phpmailer
  shows one or more advisories, all for version 6.0.0.

## Healthy
- **`psr/log@1.1.4`** — the range `^1.1` resolves to `1.1.4` (latest 1.x), no
  advisories. This checks range generation.

## The worst-case element
`require-dev` names `phpunit/phpunit: ^999.0.0`, which does not exist.
- **PASS (require-dev stripped):** production resolves → 1 vuln + 1 healthy above.
- **FAIL (require-dev NOT stripped):** `composer update` cannot resolve
  `^999.0.0` → whole generation fails → **0 healthy, both production packages
  unresolved, 0 vulnerabilities.** That "0 vulnerabilities" is a false all-clear.

## Pass / fail
- PASS: `phpmailer/phpmailer@6.0.0` vulnerable, `psr/log@1.1.4` healthy.
- FAIL: 0 healthy, packages unresolved, 0 vulns (a false all-clear), or any
  invented version.
