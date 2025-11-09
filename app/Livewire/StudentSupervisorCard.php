<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Student;
use App\Models\SupervisorAssignment;
use Illuminate\Support\Facades\Auth;

class StudentSupervisorCard extends Component
{
    public $student;
    public $supervisorAssignment;

    public function mount()
    {
        $user = Auth::user();

        if (!$user->student) {
            abort(403, 'Access denied. Student profile required.');
        }

        $this->student = $user->student;
        $this->supervisorAssignment = $this->student->supervisorAssignment;
    }

    public function render()
    {
        return view('livewire.student-supervisor-card');
    }
}
