<?php
class Lecturer extends DB
{
    protected string $table = 'lecturers';
    protected array $fields = ['name', 'nidn', 'email', 'phone', 'join_date', 'department_id'];

    // Override: ikut menampilkan nama department (JOIN)
    public function all(): array
    {
        return $this->select(
            "SELECT l.*, d.department_name
             FROM lecturers l
             LEFT JOIN departments d ON d.id = l.department_id
             ORDER BY l.id"
        );
    }
}
