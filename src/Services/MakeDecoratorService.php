<?php

namespace MacropaySolutions\CrufdWizardGenerator\Services;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class MakeDecoratorService
{
    use InteractsWithIO;

    public function __construct(
        protected PathsAndNamespacesService $pathsAndNamespacesService,
        ConsoleOutput $consoleOutput,
    ) {
        $this->output = $consoleOutput;
    }

    public function makeCompleteDecoratorFile(string $resourceName, string $frameworkNamespace, array $columns): void
    {
        $this->createDecoratorFile(
            $this->replaceContentDecoratorStub($resourceName, $frameworkNamespace, $columns),
            $resourceName
        );
    }

    protected function replaceContentDecoratorStub(
        string $resourceName,
        string $frameworkNamespace,
        array $columns
    ): string {
        return \str_replace([
            'DummyNamespace',
            'DummyClass',
            'DummyMap',
        ], [
            \trim($frameworkNamespace, '\\'),
            $this->pathsAndNamespacesService->singularUcFirstCamelCase($resourceName) . 'Decorator',
            '            ' . \implode(",\n            ", \array_map(
                fn(string $val): string => "'" . $val . "' => '" . Str::camel($val) . "'",
                $columns
            ))
        ], \app('files')->get($this->pathsAndNamespacesService->getDecoratorStubPath()));
    }

    protected function createDecoratorFile(string $decoratorStub, string $resourceName): void
    {
        if (!\app('files')->exists($path = $this->pathsAndNamespacesService->getRealpathBaseDecorator())) {
            \app('files')->makeDirectory($path);
        }

        if (!\app('files')->exists($path = $this->pathsAndNamespacesService->getRealpathBaseCustomDecorator($resourceName))) {
            \app('files')->put($path, $decoratorStub);
            $this->line('<info>Created Decorator:</info> ' . $path);

            return;
        }

        $this->error('Decorator ' . $path . ' already exists');
    }
}
