<?php

namespace A21ns1g4ts\FilamentBrAddress\Commands;

use Illuminate\Console\Command;

class FilamentBrAddressCommand extends Command
{
    public $signature = 'filament-br-address';

    public $description = 'Filament BR Address command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
