<?php

namespace Konnco\FilamentTimeMatrix;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Konnco\FilamentTimeMatrix\Services\TimeMatrixValidator;

class FilamentTimeMatrixServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-timematrix')
            ->hasViews();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('filament-timematrix.validator', function ($app) {
            return new TimeMatrixValidator();
        });

        $this->app->alias('filament-timematrix.validator', TimeMatrixValidator::class);
    }

    public function packageBooted(): void
    {
        //
    }
}
