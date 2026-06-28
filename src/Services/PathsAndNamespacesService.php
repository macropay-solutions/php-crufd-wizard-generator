<?php

namespace MacropaySolutions\CrufdWizardGenerator\Services;

use MacropaySolutions\Kernel\Support\Str;

class PathsAndNamespacesService
{
    public function getStubPath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'stubs';
    }

    public function getRealpathBase(string $directory): string
    {
        return \realpath(\base_path($directory));
    }

    public function getModelStubPath(bool $composedPk): string
    {
        return $this->getStubPath() . DIRECTORY_SEPARATOR . ($composedPk ? 'ModelComposed.stub' : 'Model.stub');
    }

    public function getModelAttributesStubPath(): string
    {
        return $this->getStubPath() . DIRECTORY_SEPARATOR . 'ModelAttributes.stub';
    }

    public function getModelRelationsStubPath(): string
    {
        return $this->getStubPath() . DIRECTORY_SEPARATOR . 'ModelRelations.stub';
    }

    public function getServiceStubPath(bool $composedPk): string
    {
        return $this->getStubPath() . DIRECTORY_SEPARATOR . ($composedPk ? 'ServiceComposed.stub' : 'Service.stub');
    }

    public function getControllerStubPath(): string
    {
        return $this->getStubPath() . DIRECTORY_SEPARATOR . 'Controller.stub';
    }

    public function getDecoratorStubPath(): string
    {
        return $this->getStubPath() . DIRECTORY_SEPARATOR . 'Decorator.stub';
    }

    public function getMiddlewareStubPath(): string
    {
        return $this->getStubPath() . DIRECTORY_SEPARATOR . 'Middleware.stub';
    }

    public function getRealpathBaseModel(string $modelSubfolder): string
    {
        $str = $this->getRealpathBase('app') . DIRECTORY_SEPARATOR . 'Models';

        if ('' !== $folder = \trim($modelSubfolder, '\\')) {
            return $str . DIRECTORY_SEPARATOR . $folder;
        }

        return $str;
    }

    public function getRealpathBaseModelAttributes(string $modelSubfolder): string
    {
        return $this->getRealpathBaseModel($modelSubfolder) . DIRECTORY_SEPARATOR . 'Attributes';
    }

    public function getRealpathBaseModelRelations(string $modelSubfolder): string
    {
        return $this->getRealpathBaseModelAttributes($modelSubfolder);
    }

    public function getRealpathBaseCustomModel(string $resourceName, string $modelSubfolder): string
    {
        return $this->getRealpathBaseModel($modelSubfolder) . DIRECTORY_SEPARATOR .
            $this->singularUcFirstCamelCase($resourceName) . '.php';
    }

    public function getRealpathBaseCustomModelAttributes(string $resourceName, string $modelSubfolder): string
    {
        return $this->getRealpathBaseModelAttributes($modelSubfolder) . DIRECTORY_SEPARATOR .
            $this->singularUcFirstCamelCase($resourceName) . 'Attributes.php';
    }

    public function getRealpathBaseCustomModelRelations(string $resourceName, string $modelSubfolder): string
    {
        return $this->getRealpathBaseModelRelations($modelSubfolder) . DIRECTORY_SEPARATOR .
            $this->singularUcFirstCamelCase($resourceName) . 'Relations.php';
    }

    public function getRealpathBaseService(): string
    {
        return $this->getRealpathBase('app') . DIRECTORY_SEPARATOR . 'Services';
    }

    public function getRealpathBaseCustomService(string $resourceName): string
    {
        return $this->getRealpathBaseService() . DIRECTORY_SEPARATOR .
            $this->pluralUcFirstCamelCase($resourceName) . 'Service.php';
    }

    public function getRealpathBaseController(): string
    {
        return $this->getRealpathBase('app') . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers';
    }

    public function getRealpathBaseCustomController(string $resourceName): string
    {
        return $this->getRealpathBaseController() . DIRECTORY_SEPARATOR .
            $this->pluralUcFirstCamelCase($resourceName) . 'Controller.php';
    }

    public function getRealpathBaseDecorator(): string
    {
        return $this->getRealpathBase('app') . DIRECTORY_SEPARATOR . 'Decorators';
    }

    public function getRealpathBaseCustomDecorator(string $resourceName): string
    {
        return $this->getRealpathBaseDecorator() . DIRECTORY_SEPARATOR .
            $this->singularUcFirstCamelCase($resourceName) . 'Decorator.php';
    }

    public function getRealpathBaseMiddleware(): string
    {
        return $this->getRealpathBase('app') . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Middleware' .
            DIRECTORY_SEPARATOR . 'Decorators';
    }

    public function getRealpathBaseCustomMiddleware(string $resourceName): string
    {
        return $this->getRealpathBaseMiddleware() . DIRECTORY_SEPARATOR .
            $this->pluralUcFirstCamelCase($resourceName) . 'Middleware.php';
    }

    public function singularUcFirstCamelCase(string $resourceName): string
    {
        static $result;

        return $result[$resourceName] ??= \ucfirst(Str::camel(\implode(
            '-',
            \array_map(fn(string $value): string => Str::singular($value), \explode('-', $resourceName))
        )));
    }

    public function pluralUcFirstCamelCase(string $resourceName): string
    {
        static $result;

        return $result[$resourceName] ??= \ucfirst(Str::camel($resourceName));
    }
}
