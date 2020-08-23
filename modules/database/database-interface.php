<?php

namespace SiteBuilder\Database;

interface Database {

	public function connect(): void;

	public function query(string $query);

	public function getRow(string $query);

	public function getVal(string $query);

	public function insert(string $table, array $values);

	public function update(string $table, array $values, string $where);

	public function delete(string $table, string $where);

	public function log(string $query, string $type);

	public function backupTables(string $tables = '*', string $fileName);

}
