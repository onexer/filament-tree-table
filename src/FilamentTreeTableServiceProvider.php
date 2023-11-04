<?php

namespace Onexer\FilamentTreeTable;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Onexer\FilamentTreeTable\Testing\TestsActions;
use Onexer\FilamentTreeTable\Testing\TestsBulkActions;
use Onexer\FilamentTreeTable\Testing\TestsColumns;
use Onexer\FilamentTreeTable\Testing\TestsFilters;
use Onexer\FilamentTreeTable\Testing\TestsRecords;
use Onexer\FilamentTreeTable\Testing\TestsSummaries;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTreeTableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-tree-table')
            ->hasTranslations()
            ->hasViews();
    }
}
