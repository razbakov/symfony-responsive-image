<?php

namespace Ommax\ResponsiveImageBundle\Tests\Unit\Provider;

use Ommax\ResponsiveImageBundle\Exception\ProviderNotFoundException;
use Ommax\ResponsiveImageBundle\Provider\ProviderInterface;
use Ommax\ResponsiveImageBundle\Provider\ProviderRegistry;
use PHPUnit\Framework\TestCase;

class ProviderRegistryTest extends TestCase
{
    private ProviderRegistry $registry;
    private ProviderInterface $provider;
    private string $providerName = 'test_provider';

    protected function setUp(): void
    {
        $this->registry = new ProviderRegistry('default_provider');

        $this->provider = $this->createMock(ProviderInterface::class);
        $this->provider->method('getName')
            ->willReturn($this->providerName);
    }

    public function testAddProvider(): void
    {
        $this->registry->addProvider($this->provider);

        $providers = $this->registry->getProviders();
        $this->assertArrayHasKey($this->providerName, $providers);
        $this->assertSame($this->provider, $providers[$this->providerName]);
    }

    public function testGetProvider(): void
    {
        $this->registry->addProvider($this->provider);

        $provider = $this->registry->getProvider($this->providerName);
        $this->assertSame($this->provider, $provider);
    }

    public function testGetProviderWithDefaultProvider(): void
    {
        $defaultProvider = $this->createMock(ProviderInterface::class);
        $defaultProvider->method('getName')
            ->willReturn('default_provider');

        $this->registry->addProvider($defaultProvider);

        $provider = $this->registry->getProvider();
        $this->assertSame($defaultProvider, $provider);
    }

    public function testGetProviderThrowsExceptionForNonExistentProvider(): void
    {
        $this->expectException(ProviderNotFoundException::class);
        $this->registry->getProvider('non_existent');
    }

    public function testSetDefaultProvider(): void
    {
        $this->registry->addProvider($this->provider);
        $this->registry->setDefaultProvider($this->providerName);

        $this->assertEquals($this->providerName, $this->registry->getDefaultProvider());
        $this->assertSame($this->provider, $this->registry->getProvider());
    }

    public function testSetDefaultProviderThrowsExceptionForNonExistentProvider(): void
    {
        $this->expectException(ProviderNotFoundException::class);
        $this->registry->setDefaultProvider('non_existent');
    }

    public function testGetProviders(): void
    {
        $provider1 = $this->createMock(ProviderInterface::class);
        $provider1->method('getName')->willReturn('provider1');

        $provider2 = $this->createMock(ProviderInterface::class);
        $provider2->method('getName')->willReturn('provider2');

        $this->registry->addProvider($provider1);
        $this->registry->addProvider($provider2);

        $providers = $this->registry->getProviders();

        $this->assertCount(2, $providers);
        $this->assertArrayHasKey('provider1', $providers);
        $this->assertArrayHasKey('provider2', $providers);
        $this->assertSame($provider1, $providers['provider1']);
        $this->assertSame($provider2, $providers['provider2']);
    }
}
