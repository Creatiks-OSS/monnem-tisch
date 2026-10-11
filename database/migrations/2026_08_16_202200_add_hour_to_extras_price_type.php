<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE extras MODIFY price_type ENUM('stück', 'person', 'fixed', 'hour') NOT NULL DEFAULT 'stück'");
        }

        if ($driver === 'pgsql') {
            $this->postgresPriceTypeCheck(['stück', 'person', 'fixed', 'hour']);
        }

        DB::table('extras')
            ->where(function ($query) {
                $query->whereIn('name', ['Servicekräfte', 'Personal'])
                    ->orWhere('slug', 'like', 'servicekraft%')
                    ->orWhere('slug', 'personal');
            })
            ->update(['price_type' => 'hour']);
    }

    public function down(): void
    {
        DB::table('extras')
            ->where('price_type', 'hour')
            ->update(['price_type' => 'stück']);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE extras MODIFY price_type ENUM('stück', 'person', 'fixed') NOT NULL DEFAULT 'stück'");
        }

        if ($driver === 'pgsql') {
            $this->postgresPriceTypeCheck(['stück', 'person', 'fixed']);
        }
    }

    /**
     * @param  list<string>  $values
     */
    private function postgresPriceTypeCheck(array $values): void
    {
        $constraints = DB::select(<<<'SQL'
            SELECT n.nspname AS schema, c.conname AS name
            FROM pg_constraint c
            INNER JOIN pg_class t ON t.oid = c.conrelid
            INNER JOIN pg_namespace n ON n.oid = t.relnamespace
            WHERE t.relname = 'extras'
              AND c.contype = 'c'
              AND pg_get_constraintdef(c.oid) ILIKE '%price_type%'
        SQL);

        foreach ($constraints as $constraint) {
            $schema = str_replace('"', '""', $constraint->schema);
            $name = str_replace('"', '""', $constraint->name);
            DB::statement("ALTER TABLE \"{$schema}\".extras DROP CONSTRAINT \"{$name}\"");
        }

        $quoted = implode(', ', array_map(
            fn (string $value) => "'".str_replace("'", "''", $value)."'",
            $values
        ));

        DB::statement("ALTER TABLE extras ADD CONSTRAINT extras_price_type_check CHECK (price_type IN ({$quoted}))");
    }
};
