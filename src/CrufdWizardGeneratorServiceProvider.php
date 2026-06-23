<?php

namespace MacropaySolutions\CrufdWizardGenerator;

use Illuminate\Support\ServiceProvider;
use MacropaySolutions\CrufdWizardGenerator\Console\MakeCrufdWizard;

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
