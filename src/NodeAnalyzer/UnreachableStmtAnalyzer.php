<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class UnreachableStmtAnalyzer
{
    /**
     * in case of unreachable stmts, no other node will have available scope
     * recursively check previous expressions, until we find nothing or is_unreachable
     */
    public function resolveUnreachableStmtFromNode(?Node $node): ?Node
    {
        if (! $node instanceof Node) {
            return null;
        }

        if ($node->getAttribute(AttributeKey::IS_UNREACHABLE) === true) {
            // here the scope is never available for next stmt so we have nothing to refresh
            return $node;
        }

        $previousStmt = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (! $previousStmt instanceof Node) {
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            return $this->resolveUnreachableStmtFromNode($parentNode);
        }

        return $this->resolveUnreachableStmtFromNode($previousStmt);
    }
}
