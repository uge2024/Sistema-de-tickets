<div>
    <div class="container mx-auto p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    {{ $editingPuestoId ? 'Editar Puesto' : 'Crear Puesto' }}
                </h2>
                <form wire:submit.prevent="{{ $editingPuestoId ? 'updatePuesto' : 'createPuesto' }}" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre del Puesto</label>
                        <input type="text" wire:model="{{ $editingPuestoId ? 'editName' : 'name' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Área</label>
                        <select wire:model="{{ $editingPuestoId ? 'editAreaId' : 'area_id' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Selecciona un Área</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded">
                            {{ $editingPuestoId ? 'Actualizar' : 'Crear' }}
                        </button>
                        @if($editingPuestoId)
                            <button type="button" wire:click="cancelEdit" class="bg-gray-500 text-white py-2 px-4 rounded">
                                Cancelar
                            </button>
                        @endif
                    </div>
                </form>
            </div>
    
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Listado de Puestos</h2>
                @if($puestos->isEmpty())
                    <p class="text-gray-500">No hay puestos registrados.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Nombre</th>
                                <th class="px-6 py-3">Área</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($puestos as $puesto)
                                <tr>
                                    <td class="px-6 py-4">{{ $puesto->name }}</td>
                                    <td class="px-6 py-4">{{ $puesto->area->name ?? 'Sin área' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="{{ $puesto->trashed() ? 'text-red-600' : 'text-green-600' }}">
                                            {{ $puesto->trashed() ? 'Inactivo' : 'Activo' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button wire:click="editPuesto({{ $puesto->id }})" class="text-blue-600 mr-2">Editar</button>
                                        <button wire:click="toggleStatus({{ $puesto->id }})" class="text-red-600">
                                            {{ $puesto->trashed() ? 'Habilitar' : 'Deshabilitar' }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
    
</div>
