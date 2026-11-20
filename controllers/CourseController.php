<?php
class CourseController extends BaseController
{
    public function __construct()
    {
        $this->model = new Course();
        $this->view = new CourseView();
        $this->page = 'course.php';
        $this->label = 'Course';
        $this->nullable = ['lecturer_id'];
    }

    protected function extraData(): array
    {
        return ['lecturers' => (new Lecturer())->all()];
    }
}
