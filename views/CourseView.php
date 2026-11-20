<?php
class CourseView
{
    private string $page = 'course.php';

    public function render(array $rows, ?array $edit, array $extra, ?array $flash): void
    {
        $t = new Template('course.html');
        $isEdit = !empty($edit['id']);
        $v = fn($k) => Template::e($edit[$k] ?? '');

        $options = '<option value="">-- Belum ditentukan --</option>';
        foreach ($extra['lecturers'] as $l) {
            $sel = (($edit['lecturer_id'] ?? '') == $l['id']) ? ' selected' : '';
            $options .= '<option value="' . Template::e($l['id']) . '"' . $sel . '>'
                . Template::e($l['name'] . ' (' . $l['nidn'] . ')') . '</option>';
        }

        $table = '';
        foreach ($rows as $r) {
            $table .= Template::tr(
                [$r['id'], $r['course_code'], $r['course_name'], $r['credits'], $r['semester'], $r['lecturer_name'] ?? '-'],
                Template::actions($this->page, $r['id'])
            );
        }

        $t->set('DATA_FORM_TITLE', $isEdit ? 'Update Course' : 'Create Course');
        $t->set('DATA_HEADER_CLASS', $isEdit ? 'bg-warning' : 'bg-primary');
        $t->set('DATA_ACTION', $isEdit ? 'update' : 'add');
        $t->set('DATA_ID', $v('id'));
        $t->set('DATA_COURSE_CODE', $v('course_code'));
        $t->set('DATA_COURSE_NAME', $v('course_name'));
        $t->set('DATA_CREDITS', $v('credits'));
        $t->set('DATA_SEMESTER', $v('semester'));
        $t->set('DATA_LECTURER_OPTIONS', $options);
        $t->set('DATA_CANCEL', $isEdit ? '<a class="btn btn-info" href="' . $this->page . '">Cancel</a>' : '');
        $t->set('DATA_TABEL', $table ?: Template::emptyRow(7));
        $t->write('Courses', $flash);
    }
}
