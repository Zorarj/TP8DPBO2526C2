<?php
/**
 * Base model: mengelola koneksi database + operasi CRUD generik.
 * Model turunan cukup mengisi $table dan $fields.
 */
class DB
{
    protected string $table = '';
    protected array $fields = [];   // kolom yang diisi dari form (tanpa id)
    private static ?mysqli $conn = null;

    public function __construct()
    {
        if (self::$conn === null) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            self::$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            self::$conn->set_charset('utf8mb4');
        }
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    // Menjalankan prepared statement
    protected function run(string $sql, array $params = []): mysqli_stmt
    {
        $stmt = self::$conn->prepare($sql);
        if ($params) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    // SELECT -> array of rows
    protected function select(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function all(): array
    {
        return $this->select("SELECT * FROM `{$this->table}` ORDER BY id");
    }

    public function find($id): ?array
    {
        return $this->select("SELECT * FROM `{$this->table}` WHERE id = ?", [$id])[0] ?? null;
    }

    private function values(array $data): array
    {
        return array_map(fn($f) => $data[$f] ?? null, $this->fields);
    }

    public function create(array $data): void
    {
        $cols = implode(',', array_map(fn($f) => "`$f`", $this->fields));
        $mark = implode(',', array_fill(0, count($this->fields), '?'));
        $this->run("INSERT INTO `{$this->table}` ($cols) VALUES ($mark)", $this->values($data));
    }

    public function update($id, array $data): void
    {
        $set = implode(',', array_map(fn($f) => "`$f` = ?", $this->fields));
        $this->run("UPDATE `{$this->table}` SET $set WHERE id = ?", [...$this->values($data), $id]);
    }

    public function delete($id): void
    {
        $this->run("DELETE FROM `{$this->table}` WHERE id = ?", [$id]);
    }
}
