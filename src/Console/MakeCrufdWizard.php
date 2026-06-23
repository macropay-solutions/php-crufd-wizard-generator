<?php

namespace MacropaySolutions\CrufdWizardGenerator\Console;

use Illuminate\Console\Command;
use MacropaySolutions\CrufdWizardGenerator\Services\MakeControllerService;
use MacropaySolutions\CrufdWizardGenerator\Services\MakeDecoratorService;
use MacropaySolutions\CrufdWizardGenerator\Services\MakeMiddlewareService;
use MacropaySolutions\CrufdWizardGenerator\Services\MakeModelService;
use MacropaySolutions\CrufdWizardGenerator\Services\MakeServiceService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeCrufdWizard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:api-resource {resourceName} {--decorated} {--table=} {--connection=} {--composed}' .
        ' {--connectionAsModelFolder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a resource template for php-crufd-wizard';

    private bool $ignoreError = false;
    private ?string $resourceName = null;
    private ?string $connection = null;
    private ?string $table = null;

    public function __construct(
        protected MakeControllerService $makeControllerService,
        protected MakeModelService $makeModelService,
        protected MakeServiceService $makeServiceService,
        protected MakeDecoratorService $makeDecoratorService,
        protected MakeMiddlewareService $makeMiddlewareService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->ignoreError = false;
        $this->resourceName = $this->argument('resourceName');
        $this->connection = $this->option('connection');
        $this->table = $this->option('table');

        $this->create();
    }

    public function create(int $try = 0): void
    {
        $composedPk = $this->option('composed');
        $columns = [];
        $modelSubfolder = $this->option('connectionAsModelFolder')
            && '' !== (string)$this->connection ? '\\' . \ucfirst($this->connection) : '';

        try {
            $columns = $this->makeModelService->makeCompleteModelFile(
                $this->resourceName,
                $ns = $this->app->getNamespace(),
                $this->connection,
                $this->table,
                $this->ignoreError,
                $composedPk,
                $modelSubfolder
            );
        } catch (\Throwable $e) {
            $this->resourceName ??= $this->ask('resourceName ?');
            $this->table ??= $this->ask('tableName ?');

            if ($try === 2) {
                $this->connection ??= $this->ask('connectionName (blank for default) ?');
            }

            if ($try <= 2) {
                $this->create(++$try);

                return;
            }

            $this->error($e->getMessage());

            if (!$this->ignoreError) {
                if ('n' === $this->ask($e->getMessage() . ', do you want to continue y/n?', 'y')) {
                    return;
                }

                $this->ignoreError = true;
                $this->create(2);

                return;
            }
        }

        $this->makeServiceService->makeCompleteServiceFile($this->resourceName, $ns, $composedPk, $modelSubfolder);
        $this->makeControllerService->makeCompleteControllerFile(
            $this->resourceName,
            $ns,
            \array_diff($columns, ['created_at', 'updated_at', 'id'])
        );

        if ($decorated = $this->option('decorated')) {
            $this->makeDecoratorService->makeCompleteDecoratorFile($this->resourceName, $ns, $columns);
            $this->makeMiddlewareService->makeCompleteMiddlewareFile($this->resourceName, $ns, $modelSubfolder);
        }

        $this->info('---------------------------------------------------------------------------------------------');
        $this->info('TODO:');

        if ($this->ignoreError) {
            $this->info('- Create migration,');
        }

        $this->info('- Fill the Model\'s properties and relations,');
        $this->info('- Fill the ModelAttributes\' dock-block property types,');
        $this->info('- Fill the ModelRelations\' dock-block property-reads,');

        if ($composedPk) {
            $this->info('- Replace composed primary keys id_1, id_2, ... in Model and Service,');
        }

        $this->info('- Define validations and DbCrudMap in Controller,');

        if ($decorated) {
            $this->info('- Fill the Decorator,');
            $this->info('- Register Middleware decorator as route middleware,');
            $this->info('- Use the middleware alias as middleware in your crud route definition for each method.');
        }

        $this->info('- Expose resource in DbCrudMap::MODEL_FQN_TO_CONTROLLER_MAP.');

        $this->info('---------------------------------------------------------------------------------------------');
        $this->info('Thank you for using php-crufd-wizard-generator');
        $this->info('For more details see: https://github.com/macropay-solutions/php-crufd-wizard-generator');
        $this->info('For full cruFd suite: https://php-crufd-wizard.com');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (!\is_string($input->getArgument('resourceName'))) {
            $input->setArgument('resourceName', $this->ask('resourceName ?'));
        }
    }
}
