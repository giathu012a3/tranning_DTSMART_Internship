<?php

use yii\db\Migration;

/**
 * Class m260603_094100_add_is_deleted_to_all_tables
 */
class m260603_094100_add_is_deleted_to_all_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tables = ['products', 'categories', 'articles', 'orders'];
        foreach ($tables as $table) {
            try {
                $tableSchema = $this->db->getTableSchema($table);
                if ($tableSchema !== null && !isset($tableSchema->columns['is_deleted'])) {
                    $this->addColumn($table, 'is_deleted', $this->tinyInteger(1)->notNull()->defaultValue(0));
                    $this->createIndex("idx-{$table}-is_deleted", $table, 'is_deleted');
                    
                    if (isset($tableSchema->columns['deleted_at'])) {
                        $this->update($table, ['is_deleted' => 1], 'deleted_at IS NOT NULL');
                    }
                    echo "Successfully added 'is_deleted' column to table: {$table}\n";
                } else {
                    echo "Column 'is_deleted' already exists or table '{$table}' not found, skipping...\n";
                }
            } catch (\Throwable $e) {
                echo "Error adding 'is_deleted' to table '{$table}': " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $tables = ['products', 'categories', 'articles', 'orders'];
        foreach ($tables as $table) {
            try {
                $tableSchema = $this->db->getTableSchema($table);
                if ($tableSchema !== null && isset($tableSchema->columns['is_deleted'])) {
                    $this->dropIndex("idx-{$table}-is_deleted", $table);
                    $this->dropColumn($table, 'is_deleted');
                    echo "Successfully removed 'is_deleted' column from table: {$table}\n";
                }
            } catch (\Throwable $e) {
                echo "Error removing 'is_deleted' from table '{$table}': " . $e->getMessage() . "\n";
            }
        }
    }
}
