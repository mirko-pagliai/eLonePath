<?php
declare(strict_types=1);

namespace Elone\Core\Console;

use Gettext\Scanner\PhpFunctionsScanner;

/**
 * Finds every domain name a `__d()` call in a set of files uses literally. `PhpScanner` needs every domain it will
 * encounter declared upfront (as its own `Translations` instance), or entries for an undeclared one are silently
 * dropped — this finds them first, with the same low-level scanner `PhpScanner` itself uses internally, but
 * without needing any domain declared in advance: it just reads whatever `__d()`'s first argument literally says,
 * and ignores a call whose first argument isn't a literal string (a variable, a concatenation) — there's no domain
 * name to read from one of those.
 */
final readonly class DomainDiscoverer
{
    /**
     * @param iterable<string> $files Paths to PHP files to scan.
     * @return list<string> Every domain found, plus `'default'` — always included, since it's what a bare `__()`
     * call (no explicit domain) is assigned to.
     */
    public function discover(iterable $files): array
    {
        $scanner = new PhpFunctionsScanner(['__d']);
        $domains = ['default' => true];

        foreach ($files as $file) {
            $code = file_get_contents($file);
            if ($code === false) {
                continue;
            }

            foreach ($scanner->scan($code, $file) as $function) {
                $domain = $function->getArguments()[0] ?? null;
                if (is_string($domain)) {
                    $domains[$domain] = true;
                }
            }
        }

        return array_keys($domains);
    }
}
