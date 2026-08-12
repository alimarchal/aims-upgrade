<?php

namespace App\Livewire;

use Livewire\Component;

class IpdOpd extends Component
{
    public $ipd_opd = 'NONE';

    public $government_non_gov = '';

    public function render()
    {
        return view('livewire.ipd-opd');
    }
}
