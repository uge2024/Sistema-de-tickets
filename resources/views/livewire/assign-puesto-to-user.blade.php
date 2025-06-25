<div class="max-w-5xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Asignar Puesto a Usuario</h2>

    @if (session()->has('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">{{ session('success') }}</div>
    @endif

    <!-- FORMULARIO -->
    <div class="mb-8 bg-gray-50 p-4 rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                <select wire:model="selectedUser" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Selecciona un usuario</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                @error('selectedUser') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Puesto</label>
                <select wire:model="selectedPuesto" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Selecciona un puesto</option>
                    @foreach($puestos as $puesto)
                        <option value="{{ $puesto->id }}">{{ $puesto->name }} (Área: {{ $puesto->area->name }})</option>
                    @endforeach
                </select>
                @error('selectedPuesto') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mt-4">
            <button wire:click="assign" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Asignar
            </button>
        </div>
    </div>

    <!-- TABLA DE ASIGNACIONES -->
    <h3 class="text-xl font-semibold mb-3">Usuarios con Puesto Asignado</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded shadow-md">
            <thead class="bg-gray-100 text-gray-700 text-sm">
                <tr>
                    <th class="py-2 px-4 text-left">Nombre</th>
                    <th class="py-2 px-4 text-left">Email</th>
                    <th class="py-2 px-4 text-left">Puesto</th>
                    <th class="py-2 px-4 text-left">Área</th>
                    <th class="py-2 px-4 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr class="border-b">
                        <td class="py-2 px-4">{{ $user->name }}</td>
                        <td class="py-2 px-4">{{ $user->email }}</td>
                        <td class="py-2 px-4">{{ $user->puesto?->name ?? '—' }}</td>
                        <td class="py-2 px-4">{{ $user->puesto?->area?->name ?? '—' }}</td>
                        <td class="py-2 px-4">
                            @if($user->puesto)
                                <button wire:click="removeAssignment({{ $user->id }})"
                                    class="text-red-600 hover:text-red-800 text-sm">
                                    Quitar Puesto
                                </button>
                            @else
                                <span class="text-gray-400 text-sm">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
