<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $conn = DB::connection('mysql2');
        $db = $conn->getDatabaseName();

        $fk = $conn->selectOne("
            SELECT CONSTRAINT_NAME AS name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'pontos_interesse'
              AND COLUMN_NAME = 'place_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ", [$db]);

        if ($fk && isset($fk->name)) {
            $conn->statement("ALTER TABLE `pontos_interesse` DROP FOREIGN KEY `{$fk->name}`");
        }

        $conn->statement("ALTER TABLE `pontos_interesse` MODIFY `place_id` VARCHAR(255) NOT NULL");

        $idx = $conn->selectOne("
            SELECT 1 AS ok
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'pontos_interesse'
              AND INDEX_NAME = 'pi_place_id_idx'
            LIMIT 1
        ", [$db]);

        if (!$idx) {
            $conn->statement("ALTER TABLE `pontos_interesse` ADD INDEX `pi_place_id_idx` (`place_id`)");
        }

        $fk2 = $conn->selectOne("
            SELECT 1 AS ok
            FROM information_schema.REFERENTIAL_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = ?
              AND TABLE_NAME = 'pontos_interesse'
              AND CONSTRAINT_NAME = 'pi_place_id_fk'
            LIMIT 1
        ", [$db]);

        if (!$fk2) {
            $conn->statement("
                ALTER TABLE `pontos_interesse`
                ADD CONSTRAINT `pi_place_id_fk`
                FOREIGN KEY (`place_id`) REFERENCES `places`(`place_id`)
                ON DELETE CASCADE
            ");
        }
    }

    public function down(): void
    {
        $conn = DB::connection('mysql2');
        $db = $conn->getDatabaseName();

        $fk = $conn->selectOne("
            SELECT CONSTRAINT_NAME AS name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'pontos_interesse'
              AND COLUMN_NAME = 'place_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ", [$db]);

        if ($fk && isset($fk->name)) {
            $conn->statement("ALTER TABLE `pontos_interesse` DROP FOREIGN KEY `{$fk->name}`");
        }

        $idx = $conn->selectOne("
            SELECT 1 AS ok
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'pontos_interesse'
              AND INDEX_NAME = 'pi_place_id_idx'
            LIMIT 1
        ", [$db]);

        if ($idx) {
            $conn->statement("ALTER TABLE `pontos_interesse` DROP INDEX `pi_place_id_idx`");
        }

        $conn->statement("ALTER TABLE `pontos_interesse` MODIFY `place_id` BIGINT UNSIGNED NOT NULL");

        $conn->statement("
            ALTER TABLE `pontos_interesse`
            ADD CONSTRAINT `pontos_interesse_place_id_foreign`
            FOREIGN KEY (`place_id`) REFERENCES `places`(`id`)
            ON DELETE CASCADE
        ");
    }
};
