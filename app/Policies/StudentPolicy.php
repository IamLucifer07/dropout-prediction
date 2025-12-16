<?php

namespace App\Policies;

use App\Models\CollegeAdmin;
use App\Models\Student;
use Illuminate\Auth\Access\Response;

class StudentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(CollegeAdmin $collegeAdmin): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(CollegeAdmin $collegeAdmin, Student $student): bool
    {
        return $collegeAdmin->id === $student->college_admin_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(CollegeAdmin $collegeAdmin): bool
    {
        return true; // Allow college admins to create students
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(CollegeAdmin $collegeAdmin, Student $student): bool
    {
        return $collegeAdmin->id === $student->college_admin_id; // Only allow if admin owns the student
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(CollegeAdmin $collegeAdmin, Student $student): bool
    {
        return $collegeAdmin->id === $student->college_admin_id; // Only allow if admin owns the student
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(CollegeAdmin $collegeAdmin, Student $student): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(CollegeAdmin $collegeAdmin, Student $student): bool
    {
        return false;
    }
}
