<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Puesto;
use App\Models\Area;

class CreatePuesto extends Component
{
    public $name = '';
    public $area_id = '';
    public $puestos = [];
    public $areas = [];
    public $editingPuestoId = null;
    public $editName = '';
    public $editAreaId = '';

    public function mount()
    {
        $this->loadPuestos();
        $this->areas = Area::all();
    }

    public function loadPuestos()
    {
        $this->puestos = Puesto::with('area')->withTrashed()->get();
    }

    public function createPuesto()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'area_id' => 'required|exists:areas,id',
        ]);

        Puesto::create([
            'name' => $this->name,
            'area_id' => $this->area_id,
        ]);

        $this->reset(['name', 'area_id']);
        $this->loadPuestos();
    }

    public function editPuesto($id)
    {
        $puesto = Puesto::withTrashed()->findOrFail($id);
        $this->editingPuestoId = $id;
        $this->editName = $puesto->name;
        $this->editAreaId = $puesto->area_id;
    }

    public function updatePuesto()
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editAreaId' => 'required|exists:areas,id',
        ]);

        $puesto = Puesto::withTrashed()->findOrFail($this->editingPuestoId);
        $puesto->update([
            'name' => $this->editName,
            'area_id' => $this->editAreaId,
        ]);

        $this->cancelEdit();
        $this->loadPuestos();
    }

    public function cancelEdit()
    {
        $this->editingPuestoId = null;
        $this->editName = '';
        $this->editAreaId = '';
    }

    public function toggleStatus($id)
    {
        $puesto = Puesto::withTrashed()->findOrFail($id);
        $puesto->trashed() ? $puesto->restore() : $puesto->delete();
        $this->loadPuestos();
    }

    public function render()
    {
        return view('livewire.create-puesto');
    }
}
