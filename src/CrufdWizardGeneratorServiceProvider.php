<?php

namespace MacropaySolutions\CrufdWizardGenerator;

use MacropaySolutions\CrufdWizardGenerator\Console\MakeCrufdWizard;
use MacropaySolutions\Kernel\Support\ServiceProvider;

class CrufdWizardGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->commands(MakeCrufdWizard::class);
    }
}
