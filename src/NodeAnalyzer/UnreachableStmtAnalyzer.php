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
    public function isStmtPHPStanUnreachable(?Node $node): bool
    {
        if (! $node instanceof Node) {
            return false;
        }

        if ($node->getAttribute(AttributeKey::IS_UNREACHABLE) === true) {
            // here the scope is never available for next stmt so we have nothing to refresh
            return true;
        }

        $previousStmt = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (! $previousStmt instanceof Node) {
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            return $this->isStmtPHPStanUnreachable($parentNode);
        }

        return $this->isStmtPHPStanUnreachable($previousStmt);
    }
}
