<?php

namespace SiteBuilder\Database;

interface DatabaseInterface {

	public function connect(string $server, string $name, string $user, string $password): void;

	public function getRow(string $table, string $id, string $columns = '*', string $primaryKey = 'ID'): array;

	public function getRows(string $table, string $where, string $columns = '*', string $order = ''): array;

	public function getVal(string $table, string $id, string $column, string $primaryKey = 'ID'): string;

	public function insert(string $table, array $values, $primaryKey = 'ID'): int;

	public function update(string $table, array $values, string $where): int;

	public function delete(string $table, string $where): int;

	public function enableLogging(string $logTableName): DatabaseComponent;

	public function disableLogging(): DatabaseComponent;

	public function log(string $type, string $query): bool;

}
