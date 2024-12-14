<?php

namespace Ommax\ResponsiveImageBundle\Provider;

use Ommax\ResponsiveImageBundle\Exception\ProviderNotFoundException;

class ProviderRegistry
{
    /**
     * @var array<string, ProviderInterface>
     */
    private array $providers = [];

    public function addProvider(ProviderInterface $provider): void
    {
        $this->providers[$provider->getName()] = $provider;
    }

    public function getProvider(?string $name = null): ProviderInterface
    {
        if (!isset($this->providers[$name])) {
            throw new ProviderNotFoundException(
                \sprintf(
                    'Provider "%s" not found. Available providers: %s',
                    $name,
                    implode(', ', array_keys($this->providers))
                )
            );
        }

        return $this->providers[$name];
    }

    /**
     * @return array<string, ProviderInterface>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }
}
