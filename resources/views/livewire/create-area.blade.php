<div class="container mx-auto p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Formulario para crear/editar área -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">
                {{ $editingAreaId ? 'Editar Área' : 'Crear Área' }}
            </h2>
            <form wire:submit.prevent="{{ $editingAreaId ? 'updateArea' : 'createArea' }}" class="space-y-4">
                <div>
                    <label for="{{ $editingAreaId ? 'editName' : 'name' }}" class="block text-sm font-medium text-gray-700">Nombre del Área</label>
                    <input type="text" wire:model="{{ $editingAreaId ? 'editName' : 'name' }}" id="{{ $editingAreaId ? 'editName' : 'name' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    @error($editingAreaId ? 'editName' : 'name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="{{ $editingAreaId ? 'editCode' : 'code' }}" class="block text-sm font-medium text-gray-700">Código del Área (ej. C, A)</label>
                    <input type="text" wire:model="{{ $editingAreaId ? 'editCode' : 'code' }}" id="{{ $editingAreaId ? 'editCode' : 'code' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" maxlength="10">
                    @error($editingAreaId ? 'editCode' : 'code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="flex space-x-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out">
                        {{ $editingAreaId ? 'Actualizar Área' : 'Crear Área' }}
                    </button>
                    @if($editingAreaId)
                        <button type="button" wire:click="cancelEdit" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out">
                            Cancelar
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <!-- Listado de áreas -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Listado de Áreas</h2>
            @if($areas->isEmpty())
                <p class="text-gray-500">No hay áreas registradas.</p>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($areas as $area)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $area->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $area->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="{{ $area->trashed() ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $area->trashed() ? 'Inactivo' : 'Activo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="editArea({{ $area->id }})" class="text-blue-600 hover:text-blue-800 mr-2">Editar</button>
                                    <button wire:click="toggleStatus({{ $area->id }})" class="{{ $area->trashed() ? 'text-green-600 hover:text-green-700' : 'text-red-600 hover:text-red-800' }}">
                                        {{ $area->trashed() ? 'Habilitar' : 'Deshabilitar' }}
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

<!-- Mostrar notificaciones -->
@script
    document.addEventListener('livewire:init', () => {
        Livewire.on('area-created', (event) => {
            alert(event.message);
        });
        Livewire.on('area-updated', (event) => {
            alert(event.message);
        });
        Livewire.on('area-status-updated', (event) => {
            alert(event.message);
        });
    });
@endscript