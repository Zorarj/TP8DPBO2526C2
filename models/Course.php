<?php
class Course extends DB
{
    protected string $table = 'courses';
    protected array $fields = ['course_code', 'course_name', 'credits', 'semester', 'lecturer_id'];

    // Override: ikut menampilkan nama dosen pengampu (JOIN)
    public function all(): array
    {
        return $this->select(
            "SELECT c.*, l.name AS lecturer_name
             FROM courses c
             LEFT JOIN lecturers l ON l.id = c.lecturer_id
             ORDER BY c.id"
        );
    }
}
