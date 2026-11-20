<?php
class DepartmentController extends BaseController
{
    public function __construct()
    {
        $this->model = new Department();
        $this->view = new DepartmentView();
        $this->page = 'department.php';
        $this->label = 'Department';
    }
}
