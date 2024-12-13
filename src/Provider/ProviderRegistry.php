<?php

namespace Ommax\ResponsiveImageBundle\Provider;

use Ommax\ResponsiveImageBundle\Exception\ProviderNotFoundException;

class ProviderRegistry
{
    /**
     * @var array<string, ProviderInterface>
     */
    private array $providers = [];

    private string $defaultProvider;

    public function __construct(string $defaultProvider = 'liip_imagine')
    {
        $this->defaultProvider = $defaultProvider;
    }

    public function addProvider(ProviderInterface $provider): void
    {
        $this->providers[$provider->getName()] = $provider;
    }

    public function getProvider(?string $name = null): ProviderInterface
    {
        $providerName = $name ?? $this->defaultProvider;

        if (!isset($this->providers[$providerName])) {
            throw new ProviderNotFoundException(sprintf(
                'Provider "%s" not found. Available providers: %s',
                $providerName,
                implode(', ', array_keys($this->providers))
            ));
        }

        return $this->providers[$providerName];
    }

    /**
     * @return array<string, ProviderInterface>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getDefaultProvider(): string
    {
        return $this->defaultProvider;
    }

    public function setDefaultProvider(string $name): void
    {
        if (!isset($this->providers[$name])) {
            throw new ProviderNotFoundException(sprintf('Cannot set default provider "%s" as it does not exist', $name));
        }

        $this->defaultProvider = $name;
    }
}
