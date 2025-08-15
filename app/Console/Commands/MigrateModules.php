<?php

namespace App\Console\Commands;

use Database\Seeders\InitialUserSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class MigrateModules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:orderly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute migrations in a specific order: Principal first, then others.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //Borrar todas las tablas excepto 'users'
        // $this->info("Truncating all tables except 'users'...");
        // $this->truncateAllExceptUsers();
        // $this->info("All tables truncated successfully, except 'users'.");


        //Correr todas las migraciones
        $this->info("Executing Principal Module Migration");
        $this->call('migrate', ['--path' => 'database/migrations/Principal']);

        $this->info("Executing remaining Module Migrations");

        // Get all files inside database/migrations
        $folders = File::directories(database_path('migrations'));

        foreach ($folders as $folder) {
            if (str_contains($folder, 'Principal')) {
                continue;
            }
            $relativePath = "database/migrations/" . basename($folder);

            $this->info("Executing migrations in folder: $relativePath");
            
            $this->call('migrate', [
                '--path' => $relativePath,
            ]);
        }

        $this->info("All migrations executed successfully.");

        $this->call(InitialUserSeeder::class);
        $this->info("Initial user seeding completed successfully.");
    }
    protected function truncateAllExceptUsers()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Obtener todas las tablas
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();

        foreach ($tables as $table) {
            if ($table === 'users') {
                continue; // Saltar la tabla 'users'
            }

            try {
                DB::table($table)->truncate();
                $this->info("Table '$table' truncated.");
            } catch (\Exception $e) {
                $this->warn("Could not truncate table '$table': " . $e->getMessage());
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
