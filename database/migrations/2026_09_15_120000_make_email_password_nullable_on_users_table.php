<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Allow POS created customers to be stored with only a mobile number.
     *
     * Raw statements are used instead of the Blueprint change() helper so the
     * existing column definitions are not rewritten by doctrine/dbal guesses.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY `email` VARCHAR(255) NULL");
        DB::statement("ALTER TABLE `users` MODIFY `password` VARCHAR(255) NULL");
        DB::statement("ALTER TABLE `users` MODIFY `name` VARCHAR(255) NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE `users` SET `email` = CONCAT('user-', `id`, '@placeholder.local') WHERE `email` IS NULL");
        DB::statement("UPDATE `users` SET `password` = '' WHERE `password` IS NULL");
        DB::statement("UPDATE `users` SET `name` = CONCAT('Customer ', `id`) WHERE `name` IS NULL");

        DB::statement("ALTER TABLE `users` MODIFY `email` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE `users` MODIFY `password` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE `users` MODIFY `name` VARCHAR(255) NOT NULL");
    }
};
