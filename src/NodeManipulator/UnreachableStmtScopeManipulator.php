<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\Node\UnreachableStatementNode;
use Rector\Core\NodeAnalyzer\ScopeAnalyzer;
use Rector\Core\NodeAnalyzer\UnreachableStmtAnalyzer;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class UnreachableStmtScopeManipulator
{
    public function __construct(
        private readonly ScopeAnalyzer $scopeAnalyzer,
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly UnreachableStmtAnalyzer $unreachableStmtAnalyzer,
        private readonly SimpleCallableNodeTraverser $simpleCallableNodeTraverser
    ) {
    }

    public function initUnreachableStmtScope(Node $node): void
    {
        if ($this->shouldSkip($node)) {
            return;
        }

        $unreachableStmt = $this->unreachableStmtAnalyzer->resolveUnreachableStmtFromNode($node);
        if (! $unreachableStmt instanceof Stmt) {
            return;
        }

        /**
         * when :
         *     - current Stmt, previous Stmt, or parent Stmt is unreachable
         *
         * then:
         *     - fill Scope of Parent Stmt
         */
        $scope = $this->fillScopeFromParent($node);

        if (! $scope instanceof Scope) {
            return;
        }

        $previousStmt = $unreachableStmt->getAttribute(AttributeKey::PREVIOUS_NODE);

        while ($previousStmt instanceof Stmt) {
            $previousStmtScope = $previousStmt->getAttribute(AttributeKey::SCOPE);

            $scope = $previousStmtScope instanceof Scope
                    ? $previousStmtScope
                    : $scope;

            $this->simpleCallableNodeTraverser->traverseNodesWithCallable($previousStmt, function (Node $subNode) use (
                $scope
            ): ?Node {
                if (! $this->scopeAnalyzer->hasScope($subNode)) {
                    return null;
                }

                $subNode->setAttribute(AttributeKey::SCOPE, $scope);
                return $subNode;
            });

            $previousStmt = $previousStmt->getAttribute(AttributeKey::PARENT_NODE);
        }
    }

    private function shouldSkip(Node $node): bool
    {
        if ($node instanceof Stmt) {
            return true;
        }

        if (! $this->scopeAnalyzer->hasScope($node)) {
            return true;
        }

        $currentStmt = $this->betterNodeFinder->resolveCurrentStatement($node);
        return ! $currentStmt instanceof Stmt;
    }

    private function fillScopeFromParent(Node $node): ?Scope
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        while ($parentNode instanceof Stmt) {
            if ($parentNode instanceof UnreachableStatementNode) {
                $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
                continue;
            }

            $scope = $parentNode->getAttribute(AttributeKey::SCOPE);
            if (! $scope instanceof Scope) {
                $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
                continue;
            }

            $node->setAttribute(AttributeKey::SCOPE, $scope);
            return $scope;
        }

        return null;
    }
}