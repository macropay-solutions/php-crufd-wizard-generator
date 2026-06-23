<?php

namespace MacropaySolutions\CrufdWizardGenerator\Services;

use Illuminate\Console\Concerns\InteractsWithIO;
use Symfony\Component\Console\Output\ConsoleOutput;

class MakeControllerService
{
    use InteractsWithIO;

    public function __construct(
        protected PathsAndNamespacesService $pathsAndNamespacesService,
        ConsoleOutput $consoleOutput,
    ) {
        $this->output = $consoleOutput;
    }

    public function makeCompleteControllerFile(string $resourceName, string $frameworkNamespace, array $columns): void
    {
        $this->createControllerFile(
            $this->replaceContentControllerStub($resourceName, $frameworkNamespace, $columns),
            $resourceName
        );
    }

    protected function replaceContentControllerStub(
        string $resourceName,
        string $frameworkNamespace,
        array $columns
    ): string {
        return \str_replace([
            'DummyNamespace',
            'DummyService',
            'DummyClass',
            'DummyMapCreate',
            'DummyMapUpdate',
        ], [
            \trim($frameworkNamespace, '\\'),
            ($plural = $this->pathsAndNamespacesService->pluralUcFirstCamelCase($resourceName)) . 'Service',
            $plural . 'Controller',
            '            ' . \implode(",\n            ", \array_map(
                fn(string $val): string => "'" . $val . "' => 'required'",
                $columns
            )),
            '            ' . \implode(",\n            ", \array_map(
                fn(string $val): string => "'" . $val . "' => 'sometimes|required'",
                $columns
            ))
        ], \app('files')->get($this->pathsAndNamespacesService->getControllerStubPath()));
    }

    protected function createControllerFile(string $controllerStub, string $resourceName): void
    {
        if (!\app('files')->exists($path = $this->pathsAndNamespacesService->getRealpathBaseController())) {
            \app('files')->makeDirectory($path);
        }

        if (!\app('files')->exists($path = $this->pathsAndNamespacesService->getRealpathBaseCustomController($resourceName))) {
            \app('files')->put($path, $controllerStub);
            $this->line('<info>Created Controller:</info> ' . $path);

            return;
        }

        $this->error('Controller ' . $path . ' already exists');
    }
}
