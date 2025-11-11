<?php

declare(strict_types = 1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executa o seeder de produtos';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Executando seeder de produtos...');

        $this->call('db:seed', [
            '--class' => 'ProductSeeder',
            '--force' => true,
        ]);

        $this->info('Seeder de produtos executado com sucesso!');
    }
}
