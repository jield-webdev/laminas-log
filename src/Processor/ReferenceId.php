<?php

declare(strict_types=1);

namespace Laminas\Log\Processor;

use Override;

class ReferenceId extends RequestId implements ProcessorInterface
{
    /**
     * Adds an identifier for the request to the log.
     *
     * This enables to filter the log for messages belonging to a specific request
     *
     * @param array $event event data
     * @return array event data
     */
    #[Override]
    public function process(array $event): array
    {
        if (isset($event['extra']['referenceId'])) {
            return $event;
        }

        if (! isset($event['extra'])) {
            $event['extra'] = [];
        }

        $event['extra']['referenceId'] = $this->getIdentifier();

        return $event;
    }

    /**
     * Sets identifier.
     *
     * @param string $identifier
     * @return self
     */
    public function setReferenceId($identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Returns identifier.
     */
    public function getReferenceId(): string
    {
        return $this->getIdentifier();
    }
}
