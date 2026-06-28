<?php

namespace MacropaySolutions\CrufdWizardGenerator\Services;

use MacropaySolutions\Kernel\Console\Concerns\InteractsWithIO;
use MacropaySolutions\Kernel\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class MakeModelService
{
    use InteractsWithIO;

    private ?array $columns = null;

    public function __construct(
        protected PathsAndNamespacesService $pathsAndNamespacesService,
        ConsoleOutput $consoleOutput,
    ) {
        $this->output = $consoleOutput;
    }

    /**
     * @throws \Throwable
     */
    public function makeCompleteModelFile(
        string $resourceName,
        string $frameworkNamespace,
        ?string $connection,
        ?string $table,
        bool $ignoreError,
        bool $composedPk,
        string $modelSubfolder,
    ): array {
        $this->createModelFile(
            $resourceName,
            $this->replaceContentModelStub(
                $frameworkNamespace,
                $resourceName,
                $connection,
                $table,
                $ignoreError,
                $composedPk,
                $modelSubfolder,
            ),
            $modelSubfolder,
        );

        $this->createModelAttributesFile(
            $resourceName,
            $this->replaceContentModelAttributesStub(
                $frameworkNamespace,
                $resourceName,
                $composedPk,
                $modelSubfolder
            ),
            $modelSubfolder,
        );

        $this->createModelRelationsFile(
            $resourceName,
            $this->replaceContentModelRelationsStub(
                $frameworkNamespace,
                $resourceName,
                $modelSubfolder
            ),
            $modelSubfolder,
        );

        return $this->columns ?? [];
    }

    /**
     * @throws \Throwable
     */
    protected function replaceContentModelStub(
        string $frameworkNamespace,
        string $resourceName,
        ?string $connection,
        ?string $table,
        bool $ignoreError,
        bool $composedPk,
        string $modelSubfolder,
    ): string {
        $table ??= Str::snake(Str::camel($resourceName));
        $this->columns = null;

        try {
            $fillable = '        ' . \implode(",\n        ", \array_map(
                fn(string $val): string => "'" . $val . "'",
                $this->columns =
                    \app('db')->connection($connection ?? \config('database.default'))->getSchemaBuilder()
                        ->getColumnListing($table)
            ));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $fillable = "\n        'created_at',\n        'updated_at',";
        }

        if (!$ignoreError && $fillable === '        ') {
            throw new \Exception('Could not auto-populate fillable');
        }

        return \str_replace([
            'DummyNamespace',
            'DummyResourceName',
            'DummyTableName',
            'DummyClass',
            'DummyFillable',
            'DummyConnection',
            'DummyFolderConnection',
        ], [
            \trim($frameworkNamespace, '\\'),
            $resourceName,
            $table,
            $this->pathsAndNamespacesService->singularUcFirstCamelCase($resourceName),
            $fillable,
            (string)$connection !== '' ? '    protected $connection = \'' . $connection . '\';' : '',
            $modelSubfolder,
        ], \app('files')->get($this->pathsAndNamespacesService->getModelStubPath($composedPk)));
    }

    protected function createModelFile(string $resourceName, string $modelStub, string $modelSubfolder) : void
    {
        if(!\app('files')->exists($path = $this->pathsAndNamespacesService->getRealpathBaseModel($modelSubfolder))) {
            \app('files')->makeDirectory($path);
        }

        $path = $this->pathsAndNamespacesService->getRealpathBaseCustomModel($resourceName, $modelSubfolder);

        if (!\app('files')->exists($path)) {
            \app('files')->put($path, $modelStub);
            $this->line('<info>Created Model:</info> ' . $path);

            return;
        }

        $this->error('Model ' . $path . ' already exists');
    }

    protected function replaceContentModelAttributesStub(
        string $frameworkNamespace,
        string $resourceName,
        bool $composedPk,
        string $modelSubfolder,
    ): string {
        return \str_replace([
            'DummyNamespace',
            'DummyClass',
            'DummyDockBlock',
            'DummyFolderConnection',
        ], [
            \trim($frameworkNamespace, '\\'),
            $this->pathsAndNamespacesService->singularUcFirstCamelCase($resourceName),
            "/**\n * @property mixed $" . \implode("\n * @property mixed $", $this->columns ?? ($composedPk ? [
                'id',
                'created_at',
                'updated_at',
            ] : [
                'created_at',
                'updated_at',
            ])) . "\n */",
            $modelSubfolder,
        ], \app('files')->get($this->pathsAndNamespacesService->getModelAttributesStubPath()));
    }

    protected function replaceContentModelRelationsStub(
        string $frameworkNamespace,
        string $resourceName,
        string $modelSubfolder,
    ): string {
        return \str_replace([
            'DummyNamespace',
            'DummyClass',
            'DummyFolderConnection',
        ], [
            \trim($frameworkNamespace, '\\'),
            $this->pathsAndNamespacesService->singularUcFirstCamelCase($resourceName),
            $modelSubfolder,
        ], \app('files')->get($this->pathsAndNamespacesService->getModelRelationsStubPath()));
    }

    protected function createModelAttributesFile(
        string $resourceName,
        string $modelAttributesStub,
        string $modelSubfolder
    ) : void {
        if(!\app('files')->exists($path = $this->pathsAndNamespacesService->getRealpathBaseModelAttributes($modelSubfolder))) {
            \app('files')->makeDirectory($path);
        }

        $path = $this->pathsAndNamespacesService->getRealpathBaseCustomModelAttributes($resourceName, $modelSubfolder);

        if (!\app('files')->exists($path)) {
            \app('files')->put($path, $modelAttributesStub);
            $this->line('<info>Created ModelAttributes:</info> ' . $path);

            return;
        }

        $this->error('ModelAttributes ' . $path . ' already exists');
    }

    protected function createModelRelationsFile(
        string $resourceName,
        string $modelRelationsStub,
        string $modelSubfolder
    ) : void {
        if(!\app('files')->exists($path = $this->pathsAndNamespacesService->getRealpathBaseModelRelations($modelSubfolder))) {
            \app('files')->makeDirectory($path);
        }

        $path = $this->pathsAndNamespacesService->getRealpathBaseCustomModelRelations($resourceName, $modelSubfolder);

        if (!\app('files')->exists($path)) {
            \app('files')->put($path, $modelRelationsStub);
            $this->line('<info>Created ModelRelations:</info> ' . $path);

            return;
        }

        $this->error('ModelRelations ' . $path . ' already exists');
    }
}
