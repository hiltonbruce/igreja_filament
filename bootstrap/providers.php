<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\SecretaryPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    SecretaryPanelProvider::class,
];
