<?php
class LecturerView
{
    private string $page = 'index.php';

    public function render(array $rows, ?array $edit, array $extra, ?array $flash): void
    {
        $t = new Template('index.html');
        $isEdit = !empty($edit['id']);
        $v = fn($k) => Template::e($edit[$k] ?? '');

        $options = '<option value="">-- Tanpa department --</option>';
        foreach ($extra['departments'] as $d) {
            $sel = (($edit['department_id'] ?? '') == $d['id']) ? ' selected' : '';
            $options .= '<option value="' . Template::e($d['id']) . '"' . $sel . '>'
                . Template::e($d['department_name']) . '</option>';
        }

        $table = '';
        foreach ($rows as $r) {
            $table .= Template::tr(
                [$r['id'], $r['name'], $r['nidn'], $r['email'], $r['phone'], $r['join_date'], $r['department_name'] ?? '-'],
                Template::actions($this->page, $r['id'])
            );
        }

        $t->set('DATA_FORM_TITLE', $isEdit ? 'Update Lecturer' : 'Create Lecturer');
        $t->set('DATA_HEADER_CLASS', $isEdit ? 'bg-warning' : 'bg-primary');
        $t->set('DATA_ACTION', $isEdit ? 'update' : 'add');
        $t->set('DATA_ID', $v('id'));
        $t->set('DATA_NAME', $v('name'));
        $t->set('DATA_NIDN', $v('nidn'));
        $t->set('DATA_EMAIL', $v('email'));
        $t->set('DATA_PHONE', $v('phone'));
        $t->set('DATA_JOIN_DATE', $v('join_date'));
        $t->set('DATA_DEPARTMENT_OPTIONS', $options);
        $t->set('DATA_CANCEL', $isEdit ? '<a class="btn btn-info" href="' . $this->page . '">Cancel</a>' : '');
        $t->set('DATA_TABEL', $table ?: Template::emptyRow(8));
        $t->write('Lecturers', $flash);
    }
}
