<?php
class DepartmentView
{
    private string $page = 'department.php';

    public function render(array $rows, ?array $edit, array $extra, ?array $flash): void
    {
        $t = new Template('department.html');
        $isEdit = !empty($edit['id']);
        $v = fn($k) => Template::e($edit[$k] ?? '');

        $table = '';
        foreach ($rows as $r) {
            $table .= Template::tr(
                [$r['id'], $r['department_name'], $r['faculty'], $r['office_location']],
                Template::actions($this->page, $r['id'])
            );
        }

        $t->set('DATA_FORM_TITLE', $isEdit ? 'Update Department' : 'Create Department');
        $t->set('DATA_HEADER_CLASS', $isEdit ? 'bg-warning' : 'bg-primary');
        $t->set('DATA_ACTION', $isEdit ? 'update' : 'add');
        $t->set('DATA_ID', $v('id'));
        $t->set('DATA_DEPARTMENT_NAME', $v('department_name'));
        $t->set('DATA_FACULTY', $v('faculty'));
        $t->set('DATA_OFFICE_LOCATION', $v('office_location'));
        $t->set('DATA_CANCEL', $isEdit ? '<a class="btn btn-info" href="' . $this->page . '">Cancel</a>' : '');
        $t->set('DATA_TABEL', $table ?: Template::emptyRow(5));
        $t->write('Departments', $flash);
    }
}
