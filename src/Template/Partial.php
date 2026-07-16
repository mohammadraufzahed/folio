<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

use Folio\Pdf\Contracts\HasChildren;
use Folio\Pdf\Contracts\Node;
use Folio\Pdf\StyleEngine\StyleSheet;

final readonly class Partial
{
    /**
     * @param array<string, mixed> $props
     * @param array<string, Node> $slots
     */
    public function __construct(
        public string $name,
        public Node $body,
        public array $props = [],
        public array $slots = [],
        public ?StyleSheet $stylesheet = null,
    ) {
    }

    /**
     * @param array<string, mixed> $props
     * @param array<string, Node> $slots
     */
    public function resolve(array $props, array $slots): Node
    {
        $mergedProps = array_merge($this->props, $props);
        $mergedSlots = array_merge($this->slots, $slots);

        return $this->replaceSlots($this->body, $mergedSlots);
    }

    /**
     * @param array<string, Node> $slots
     */
    private function replaceSlots(Node $node, array $slots): Node
    {
        if ($node instanceof Slot) {
            return $slots[$node->name()] ?? $node;
        }

        if ($node instanceof HasChildren) {
            $newChildren = [];

            foreach ($node->children() as $child) {
                $newChildren[] = $this->replaceSlots($child, $slots);
            }

            return $node->withChildren($newChildren);
        }

        return $node;
    }
}
