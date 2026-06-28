<?php

namespace MacropaySolutions\CrufdWizardGenerator\Services;

use MacropaySolutions\Kernel\Console\Concerns\InteractsWithIO;
use Symfony\Component\Console\Output\ConsoleOutput;

class MakeServiceService
{
    use InteractsWithIO;

    public function __construct(
        protected PathsAndNamespacesService $pathsAndNamespacesService,
        ConsoleOutput $consoleOutput,
    ) {
        $this->output = $consoleOutput;
    }

    public function makeCompleteServiceFile(
        string $resourceName,
        string $frameworkNamespace,
        bool $composedPk,
        string $modelSubfolder,
    ): void {
        $this->createServiceFile(
            $this->replaceContentServiceStub($resourceName, $frameworkNamespace, $composedPk, $modelSubfolder),
            $resourceName
        );
    }

    protected function replaceContentServiceStub(
        string $resourceName,
        string $frameworkNamespace,
        bool $composedPk,
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
            $this->pathsAndNamespacesService->pluralUcFirstCamelCase($resourceName) . 'Service',
            $modelSubfolder,
        ], \app('files')->get($this->pathsAndNamespacesService->getServiceStubPath($composedPk)));
    }

    protected function createServiceFile(string $serviceStub, string $resourceName): void
    {
        if (!\app('files')->exists($path = $this->pathsAndNamespacesService->getRealpathBaseService())) {
            \app('files')->makeDirectory($path);
        }

        if (!\app('files')->exists($path = $this->pathsAndNamespacesService->getRealpathBaseCustomService($resourceName))) {
            \app('files')->put($path, $serviceStub);
            $this->line('<info>Created Service:</info> ' . $path);

            return;
        }

        $this->error('Service ' . $path . ' already exists');
    }
}
