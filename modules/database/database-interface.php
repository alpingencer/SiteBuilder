<?php

namespace SiteBuilder\Database;

interface Database {

	public function connect(): void;

	public function getRow(string $table, string $id, string $columns = '*', string $primaryKey = 'ID'): array;

	public function getRows(string $table, string $where, string $columns = '*', string $order = ''): array;

	public function getVal(string $table, string $id, string $column, string $primaryKey = 'ID'): string;

	public function insert(string $table, array $values): bool;

	public function update(string $table, array $values, string $where): bool;

	public function delete(string $table, string $where): bool;

	public function log(string $query, string $type): bool;

	public function backupTables(string $tables = '*', string $fileName): void;

}
