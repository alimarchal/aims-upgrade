<?php

namespace App\Livewire;

use Livewire\Component;

class GovernmentDetails extends Component
{
    public $isGovernment;

    public $departments;

    public $patient;

    public $selected = '';

    public function render()
    {
        return view('livewire.government-details');
    }
}
