<?php

namespace MacropaySolutions\CrufdWizardGenerator\Services;

use Illuminate\Console\Concerns\InteractsWithIO;
use Symfony\Component\Console\Output\ConsoleOutput;

class MakeMiddlewareService
{
    use InteractsWithIO;

    public function __construct(
        protected PathsAndNamespacesService $pathsAndNamespacesService,
        ConsoleOutput $consoleOutput,
    ) {
        $this->output = $consoleOutput;
    }

    public function makeCompleteMiddlewareFile(
        string $resourceName,
        string $frameworkNamespace,
        string $modelSubfolder,
    ): void {
        $this->createMiddlewareFile(
            $this->replaceContentMiddlewareStub($resourceName, $frameworkNamespace, $modelSubfolder),
            $resourceName
        );
    }

    protected function replaceContentMiddlewareStub(
        string $resourceName,
        string $frameworkNamespace,
        string $modelSubfolder,
    ): string {
        return \str_replace([
            'DummyNamespace',
            'DummyModel',
            'DummyClass',
            'DummyFolderConnection',
        ], [
            \trim($frameworkNamespace, '\\'),
            $this->pathsAndNamespacesService->singularUcFirstCamelCase($resourceName),
            $this->pathsAndNamespacesService->pluralUcFirstCamelCase($resourceName) . 'Middleware',
            $modelSubfolder,
        ], \app('files')->get($this->pathsAndNamespacesService->getMiddlewareStubPath()));
    }

    protected function createMiddlewareFile(string $middlewareStub, string $resourceName): void
    {
        if (!\app('files')->exists($path = $this->pathsAndNamespacesService->getRealpathBaseMiddleware())) {
            \app('files')->makeDirectory($path);
        }

        if (!\app('files')->exists($path = $this->pathsAndNamespacesService->getRealpathBaseCustomMiddleware($resourceName))) {
            \app('files')->put($path, $middlewareStub);
            $this->line('<info>Created Middleware:</info> ' . $path);

            return;
        }

        $this->error('Middleware ' . $path . ' already exists');
    }
}
