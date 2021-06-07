<?php

declare(strict_types=1);

namespace Rector\Php80\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\MatchArm;
use Rector\Php80\ValueObject\CondAndExpr;

final class MatchArmsFactory
{
    /**
     * @param CondAndExpr[] $condAndExprs
     * @return MatchArm[]
     */
    public function createFromCondAndExprs(array $condAndExprs): array
    {
        $matchArms = [];
        foreach ($condAndExprs as $condAndExpr) {
            $expr = $condAndExpr->getExpr();

            if ($expr instanceof Assign) {
                // $this->assignExpr = $expr->var;
                $expr = $expr->expr;
            }

            $condExpr = $condAndExpr->getCondExpr();

            $condList = $condExpr instanceof Expr ? [$condExpr] : null;
            $matchArms[] = new MatchArm($condList, $expr);
        }

        return $matchArms;
    }
}
