<?php
class LecturerController extends BaseController
{
    public function __construct()
    {
        $this->model = new Lecturer();
        $this->view = new LecturerView();
        $this->page = 'index.php';
        $this->label = 'Lecturer';
        $this->nullable = ['department_id'];
    }

    protected function extraData(): array
    {
        return ['departments' => (new Department())->all()];
    }
}
