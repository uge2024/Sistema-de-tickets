<div class="container mx-auto px-6 py-8">
    <h1 class="text-2xl font-bold text-center mb-8">Sistema de Fichas</h1>

    <div class="grid grid-cols-1 gap-6">
        <!-- Formulario -->
<div class="bg-white p-6 rounded-lg shadow max-w-md mx-auto">
    <h2 class="text-xl font-semibold text-gray-800 mb-4 text-center">Subir nuevo video</h2>

    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-800 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="store" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Título</label>
            <input type="text" wire:model="title" class="w-full border border-gray-300 rounded px-3 py-2">
            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Video (MP4)</label>
            <input type="file" wire:model="video" class="w-full">
            @if ($video)
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Vista previa:</label>
                <video class="w-full rounded shadow" controls>
                    <source src="{{ $video->temporaryUrl() }}" type="video/mp4">
                    Tu navegador no soporta la reproducción de video.
                </video>
            </div>
            @endif
            @error('video') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
            Subir Video
        </button>
    </form>
</div>


        <!-- Lista de videos -->
        <div class="bg-white p-6 rounded-lg shadow overflow-x-auto">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Videos Subidos</h2>

            @if($videos->isEmpty())
                <p class="text-gray-500">No hay videos aún.</p>
            @else
                <table class="min-w-full table-auto border border-gray-300 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">#</th>
                            <th class="px-3 py-2 text-left">Título</th>
                            <th class="px-3 py-2 text-left">Ruta</th>
                            <th class="px-3 py-2 text-left">Tamaño</th>
                            <th class="px-3 py-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($videos as $index => $video)
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ $index + 1 }}</td>
                                <td class="px-3 py-2">{{ $video->title }}</td>
                                <td class="px-3 py-2 text-blue-600 break-all">{{ $video->file_path }}</td>
                                <td class="px-3 py-2">{{ number_format($video->size / 1024 / 1024, 2) }} MB</td>
                                <td class="px-3 py-2 space-y-1">
                                    <div>
                                        <span class="{{ $video->is_active ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $video->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </div>
                                
                                    <div>
                                        @if ($video->is_active)
                                            <button wire:click="toggleStatus({{ $video->id }})" class="text-red-600 hover:underline">Desactivar</button>
                                        @else
                                            <button wire:click="toggleStatus({{ $video->id }})" class="text-green-600 hover:underline">Activar</button>
                                        @endif
                                    </div>
                                
                                    <div>
                                        <button wire:click="delete({{ $video->id }})" class="text-red-500 hover:underline">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
