<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Puesto;
use Livewire\Component;

class AssignPuestoToUser extends Component
{
    public $users;
    public $puestos;
    public $selectedUser = '';
    public $selectedPuesto = '';

    public function mount()
    {
        $this->users = User::all();
        $this->puestos = Puesto::with('area')->get();
    }

    public function assign()
    {
        $this->validate([
            'selectedUser' => 'required|exists:users,id',
            'selectedPuesto' => 'required|exists:puestos,id',
        ]);

        $user = User::findOrFail($this->selectedUser);
        $user->puesto_id = $this->selectedPuesto;
        $user->save();

        session()->flash('success', 'Puesto asignado correctamente.');
    }

    public function removeAssignment($userId)
{
    $user = User::findOrFail($userId);
    $user->puesto_id = null;
    $user->save();

    session()->flash('success', 'Puesto removido del usuario exitosamente.');
    $this->mount(); // Recargar usuarios y puestos
}

    public function render()
    {
        return view('livewire.assign-puesto-to-user');
    }
}

