<?php

declare(strict_types=1);

namespace Tests\Contract;

use PHPUnit\Framework\TestCase;

final class CommunicationsOpenApiContractTest extends TestCase
{
    public function test_release_critical_communications_paths_are_present_in_contract(): void
    {
        $spec = require __DIR__ . '/../../contracts/openapi.php';

        self::assertArrayHasKey('/api/v1/communications/inbox', $spec['paths']);
        self::assertArrayHasKey('/api/v1/communications/attachments/{attachmentId}/scan-status', $spec['paths']);
        self::assertArrayHasKey('/api/v1/clients/{clientId}/communications', $spec['paths']);

        $timelineParameters = $spec['paths']['/api/v1/clients/{clientId}/communications']['get']['parameters'] ?? [];
        self::assertTrue($this->containsParameter($timelineParameters, 'cursor', 'query'));

        $inboxParameters = $spec['paths']['/api/v1/communications/inbox']['get']['parameters'] ?? [];
        self::assertTrue($this->containsParameter($inboxParameters, 'cursor', 'query'));
        self::assertTrue($this->containsParameter($inboxParameters, 'search', 'query'));

        self::assertArrayHasKey('CommunicationsInboxEnvelope', $spec['components']['schemas']);
        self::assertArrayHasKey('CommunicationAttachmentGovernanceEnvelope', $spec['components']['schemas']);
    }

    /**
     * @param array<int, array<string, mixed>> $parameters
     */
    private function containsParameter(array $parameters, string $name, string $location): bool
    {
        foreach ($parameters as $parameter) {
            if (($parameter['name'] ?? null) === $name && ($parameter['in'] ?? null) === $location) {
                return true;
            }
        }

        return false;
    }
}
