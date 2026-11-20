<?php
class Department extends DB
{
    protected string $table = 'departments';
    protected array $fields = ['department_name', 'faculty', 'office_location'];
}
