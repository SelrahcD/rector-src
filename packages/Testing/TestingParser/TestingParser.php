<?php

declare(strict_types=1);

namespace Rector\Testing\TestingParser;

use Nette\Utils\FileSystem;
use PhpParser\Node;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\ParameterProvider;
use Rector\Core\PhpParser\Parser\RectorParser;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;

/**
 * @api
 */
final class TestingParser
{
    public function __construct(
        private readonly ParameterProvider $parameterProvider,
        private readonly RectorParser $rectorParser,
        private readonly NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
    ) {
    }

    public function parseFilePathToFile(string $filePath): File
    {
        $file = new File($filePath, FileSystem::read($filePath));

        $stmts = $this->rectorParser->parseFile($filePath);
        $file->hydrateStmtsAndTokens($stmts, $stmts, []);

        return $file;
    }

    /**
     * @return Node[]
     */
    public function parseFileToDecoratedNodes(string $filePath): array
    {
        $this->parameterProvider->changeParameter(Option::SOURCE, [$filePath]);

        $nodes = $this->rectorParser->parseFile($filePath);

        $file = new File($filePath, FileSystem::read($filePath));
        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $nodes);
    }
}
