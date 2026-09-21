# SCA test repo — PHP, lock-less, worst case

Lock-less (no `composer.lock`), so the scanner must generate one with
`composer update --no-install`. The worst-case element is a **broken
`require-dev`**: `phpunit/phpunit: ^999.0.0` does not exist.

Because `composer update` resolves `require-dev` into the lock too, an
unresolvable dev entry would fail the WHOLE resolve unless the scanner strips
`require-dev` first (the fix this repo tests). If the strip works, the two
production packages still resolve.

See `EXPECTED_RESULTS.md` for the ground truth.

## Run it
1. New GitHub repo, e.g. `harshit905/sca-test-php`.
2. `git remote add origin <url>` then `git push -u origin main`.
3. Scan in CodeAnt, compare to `EXPECTED_RESULTS.md`.

Do NOT commit `composer.lock`.
