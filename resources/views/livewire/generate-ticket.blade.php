<div>
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Generar Ficha</h2>
        <!-- Mensajes de Confirmación -->
        @if (session()->has('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-center">
                {{ session('success') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-center">
                {{ session('error') }}
            </div>
        @endif 
        <!-- Selección de Área con Botones -->
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4 text-center">Seleccione un Área</h3>
            <div class="flex flex-col items-center gap-4 max-w-lg mx-auto">
                @foreach ($areas as $area)
                    <button
                        wire:click="openModalForArea({{ $area->id }})"
                        type="button"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-6 px-8 rounded-lg shadow-md text-xl transition duration-300 ease-in-out w-full max-w-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        {{ $area->name }}
                    </button>
                @endforeach
            </div>
            @error('selectedArea') <span class="text-red-500 text-sm block text-center mt-2">{{ $message }}</span> @enderror
        </div>
    </div>

    <!-- Modal para Selección de Tipo -->
    @if ($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-8 rounded-lg shadow-lg w-11/12 max-w-md">
                <h3 class="text-xl font-semibold text-gray-800 mb-6 text-center">Seleccione el Tipo de Ficha</h3>
                <div class="grid grid-cols-1 gap-4">
                    <button
                        wire:click="generateTicketWithType('normal')"
                        type="button"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-6 px-8 rounded-lg shadow-md text-xl transition duration-300 ease-in-out w-full focus:outline-none focus:ring-2 focus:ring-green-500"
                        wire:loading.attr="disabled"
                    >
                        Normal
                    </button>
                    <!--
                    <button
                        wire:click="generateTicketWithType('senior')"
                        type="button"
                        class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-6 px-8 rounded-lg shadow-md text-xl transition duration-300 ease-in-out w-full focus:outline-none focus:ring-2 focus:ring-purple-500"
                        wire:loading.attr="disabled"
                    >
                        Tercera Edad
                    </button>
                -->
                </div>
                <div class="mt-6 text-center">
                    <button
                        wire:click="closeModal"
                        type="button"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-lg shadow-md text-base transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Cargar script externo -->
    <script src="{{ asset('js/generate-ticket.js') }}"></script>
</div>