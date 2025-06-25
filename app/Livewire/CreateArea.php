<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Area;


class CreateArea extends Component
{
    public $name = '';
    public $code = '';
    public $areas = [];
    public $editingAreaId = null;
    public $editName = '';
    public $editCode = '';

    protected $rules = [
        'name' => 'required|string|max:255|unique:areas,name',
        'code' => 'required|string|max:10|unique:areas,code',
        'editName' => 'required|string|max:255',
        'editCode' => 'required|string|max:10',
    ];

    public function mount()
    {
        $this->loadAreas();
    }

    public function loadAreas()
    {
        $this->areas = Area::withTrashed()->get();
    }

    public function createArea()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:areas,name',
            'code' => 'required|string|max:10|unique:areas,code',
        ]);

        Area::create([
            'name' => $this->name,
            'code' => strtoupper($this->code),
        ]);

        $this->name = '';
        $this->code = '';
        $this->loadAreas();
        $this->dispatch('area-created', ['message' => 'Área creada con éxito']);
    }

    public function editArea($id)
    {
        $area = Area::withTrashed()->findOrFail($id);
        $this->editingAreaId = $id;
        $this->editName = $area->name;
        $this->editCode = $area->code;
    }

    public function updateArea()
    {
        $this->validate([
            'editName' => 'required|string|max:255|unique:areas,name,' . $this->editingAreaId,
            'editCode' => 'required|string|max:10|unique:areas,code,' . $this->editingAreaId,
        ]);

        $area = Area::withTrashed()->findOrFail($this->editingAreaId);
        $area->update([
            'name' => $this->editName,
            'code' => strtoupper($this->editCode),
        ]);

        $this->cancelEdit();
        $this->loadAreas();
        $this->dispatch('area-updated', ['message' => 'Área actualizada con éxito']);
    }

    public function cancelEdit()
    {
        $this->editingAreaId = null;
        $this->editName = '';
        $this->editCode = '';
    }

    public function toggleStatus($id)
    {
        $area = Area::withTrashed()->findOrFail($id);
        if ($area->trashed()) {
            $area->restore();
            $this->dispatch('area-status-updated', ['message' => 'Área habilitada con éxito']);
        } else {
            $area->delete();
            $this->dispatch('area-status-updated', ['message' => 'Área deshabilitada con éxito']);
        }
        $this->loadAreas();
    }

    public function render()
    {
        return view('livewire.create-area');
    }
}