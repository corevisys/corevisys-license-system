<?php

namespace Tests\Unit;

use App\Support\DomainNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DomainNormalizationTest extends TestCase
{
    public static function domainNormalizationDataset(): array
    {
        return [
            // Standard lowercase host
            'standard host' => ['example.com', 'example.com'],
            'uppercase host' => ['EXAMPLE.COM', 'example.com'],
            'mixed case host' => ['Sub.Example.COM', 'sub.example.com'],

            // Single leading www. stripped
            'leading www' => ['www.example.com', 'example.com'],
            'leading www uppercase' => ['WWW.EXAMPLE.COM', 'example.com'],
            'one leading www only' => ['www.www.example.com', 'www.example.com'],
            'embedded www not stripped' => ['mywww.example.com', 'mywww.example.com'],
            'hyphenated www' => ['foo-www.bar.com', 'foo-www.bar.com'],

            // Scheme stripping
            'http scheme' => ['http://example.com', 'example.com'],
            'https scheme' => ['https://example.com', 'example.com'],
            'scheme with www' => ['https://www.example.com', 'example.com'],

            // Host only: port and path stripped
            'host with port' => ['example.com:8080', 'example.com'],
            'scheme host port' => ['http://example.com:8080', 'example.com'],
            'scheme host path' => ['https://www.example.com/app/dashboard', 'example.com'],
            'path without scheme' => ['example.com/app/dashboard', 'example.com'],
            'query and fragment' => ['https://www.example.com:8080/path?foo=bar#section', 'example.com'],

            // Localhost mapping
            'localhost plain' => ['localhost', '127.0.0.1'],
            'localhost uppercase' => ['LOCALHOST', '127.0.0.1'],
            '127.0.0.1 plain' => ['127.0.0.1', '127.0.0.1'],
            'localhost with scheme' => ['http://localhost', '127.0.0.1'],
            'localhost with port' => ['http://localhost:8000', '127.0.0.1'],
            '127.0.0.1 with port' => ['http://127.0.0.1:3000/api', '127.0.0.1'],
            'www.localhost' => ['www.localhost', '127.0.0.1'],

            // Whitespace trimming
            'leading and trailing whitespace' => ['   www.example.com   ', 'example.com'],

            // Exact subdomain match (no implicit subdomain collapsing)
            'subdomain preserved' => ['sub.example.com', 'sub.example.com'],
            'subdomain with www' => ['www.sub.example.com', 'sub.example.com'],
            'api subdomain' => ['api.example.com', 'api.example.com'],
        ];
    }

    #[DataProvider('domainNormalizationDataset')]
    public function test_domain_normalization_matches_canonical_spec(string $input, string $expected): void
    {
        $this->assertSame($expected, DomainNormalizer::normalize($input));
    }

    public function test_exact_normalized_match_rejects_subdomain_implicitly(): void
    {
        // exact normalized match, no implicit subdomain acceptance
        $this->assertNotSame(
            DomainNormalizer::normalize('sub.example.com'),
            DomainNormalizer::normalize('example.com')
        );
        $this->assertNotSame(
            DomainNormalizer::normalize('app.example.com'),
            DomainNormalizer::normalize('api.example.com')
        );
        $this->assertSame(
            DomainNormalizer::normalize('www.example.com'),
            DomainNormalizer::normalize('example.com')
        );
        $this->assertSame(
            DomainNormalizer::normalize('http://localhost:8000'),
            DomainNormalizer::normalize('127.0.0.1')
        );
    }

    public function test_null_and_empty_inputs(): void
    {
        $this->assertNull(DomainNormalizer::normalize(null));
        $this->assertSame('', DomainNormalizer::normalize(''));
        $this->assertSame('', DomainNormalizer::normalize('   '));
    }
}
