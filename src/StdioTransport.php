<?php

declare(strict_types=1);

namespace McpPhpStarter;

use Mcp\Schema\JsonRpc\Error;
use Mcp\Server\Transport\StdioTransport as SdkStdioTransport;
use Symfony\Component\Uid\Uuid;

/**
 * Stdio transport that keeps request IDs on errors sent before initialization.
 *
 * The SDK rejects session-less requests on stdio without echoing their ID, so
 * clients probing with stateless methods such as `server/discover` wait forever
 * instead of falling back to `initialize`.
 */
class StdioTransport extends SdkStdioTransport
{
    protected function handleMessage(string $payload, ?Uuid $sessionId): void
    {
        if (null === $sessionId && null !== ($error = $this->preInitializeError($payload))) {
            $this->send(json_encode($error, \JSON_THROW_ON_ERROR), []);

            return;
        }

        parent::handleMessage($payload, $sessionId);
    }

    private function preInitializeError(string $payload): ?Error
    {
        try {
            $message = json_decode($payload, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (
            !\is_array($message)
            || array_is_list($message)
            || !isset($message['method'])
            || !\array_key_exists('id', $message)
            || 'initialize' === $message['method']
            || (!\is_string($message['id']) && !\is_int($message['id']))
        ) {
            return null;
        }

        if ('server/discover' === $message['method']) {
            return Error::forMethodNotFound('Method "server/discover" is not supported over stdio; use "initialize".', $message['id']);
        }

        return Error::forInvalidRequest('A valid session id is REQUIRED for non-initialize requests.', $message['id']);
    }
}
