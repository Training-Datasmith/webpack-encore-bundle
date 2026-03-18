<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\CacheWarmer;

use Symfony\Bundle\FrameworkBundle\CacheWarmer\AbstractPhpFileCacheWarmer;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Exception\EntrypointNotFoundException;

class EntrypointCacheWarmer extends AbstractPhpFileCacheWarmer
{
    public function __construct(private readonly array $cacheKeys, private readonly ?HttpClientInterface $httpClient, string $phpArrayFile)
    {
        parent::__construct($phpArrayFile);
    }

    protected function doWarmUp(string $cacheDir, ArrayAdapter $arrayAdapter, ?string $buildDir = null): bool
    {
        foreach ($this->cacheKeys as $cacheKey => $path) {
            // If the file does not exist then just skip past this entry point.
            if (!str_starts_with((string) $path, 'http') && !file_exists($path)) {
                continue;
            }

            $entryPointLookup = new EntrypointLookup($path, $arrayAdapter, $cacheKey, httpClient: $this->httpClient);

            try {
                $entryPointLookup->getJavaScriptFiles('dummy');
            } catch (EntrypointNotFoundException) {
                // ignore exception
            }
        }

        return true;
    }
}
