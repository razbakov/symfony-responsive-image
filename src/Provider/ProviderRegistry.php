<?php

namespace Ommax\ResponsiveImageBundle\Provider;

use Ommax\ResponsiveImageBundle\Exception\ProviderNotFoundException;

class ProviderRegistry
{
    /**
     * @var array<string, ProviderInterface>
     */
    private array $providers = [];

    private ?string $defaultProvider = null;

    public function __construct(?string $defaultProvider = null)
    {
        if ($defaultProvider) {
            $this->setDefaultProvider($defaultProvider);
        }
    }

    public function setDefaultProvider(string $providerName): void
    {
        $this->defaultProvider = $providerName;
    }

    public function addProvider(ProviderInterface $provider): void
    {
        $this->providers[$provider->getName()] = $provider;
    }

    public function getProvider(?string $name = null): ProviderInterface
    {
        $provider = $name ?? $this->defaultProvider;

        if (null === $provider) {
            throw new ProviderNotFoundException('No provider specified and no default provider configured.');
        }

        if (empty($this->providers)) {
            throw new ProviderNotFoundException('No providers configured.');
        }

        if (!isset($this->providers[$provider])) {
            throw new ProviderNotFoundException(\sprintf('Provider "%s" not found. Available providers: %s', $provider, implode(', ', array_keys($this->providers))));
        }

        return $this->providers[$provider];
    }

    /**
     * @return array<string, ProviderInterface>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }
}
