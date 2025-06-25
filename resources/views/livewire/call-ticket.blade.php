<div>
    <div class="bg-white p-6 rounded-lg shadow-md mb-6" wire:poll.10s="loadTickets" wire:ignore.self>
        <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Llamar Ficha</h2>

        <div class="space-y-8">
            @foreach ($areas as $area)
                <div class="bg-gray-50 p-6 rounded-lg shadow-md border border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4 text-center">{{ $area->name }}</h3>

                    <button
                        wire:click="callNextTicket({{ $area->id }})"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg shadow-md text-base transition duration-300 ease-in-out mb-4 focus:outline-none focus:ring-2 focus:ring-green-500 touch-manipulation"
                        wire:loading.attr="disabled"
                        @disabled(empty($pendingTickets[$area->id] ?? []))
                    >
                        Llamar Siguiente
                    </button>

                    @if ($pendingTickets[$area->id] ?? [])
                        <ul class="space-y-2">
                            @foreach ($pendingTickets[$area->id] as $ticket)
                                <li class="flex justify-between items-center p-3 rounded-md shadow-sm border border-gray-100 {{ $ticket->type == 'senior' ? 'bg-purple-50 text-purple-800' : 'bg-green-50 text-green-800' }} hover:bg-opacity-80 transition-colors duration-200 touch-action-manipulation">
                                    <span class="text-gray-800 font-medium">{{ $ticket->ticket_number }}</span>
                                    <span class="text-sm text-gray-600">{{ $ticket->type == 'senior' ? 'Tercera Edad' : 'Normal' }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-3 text-sm text-gray-600 text-center">Pendientes: {{ $pendingTickets[$area->id]->count() }}</p>
                    @else
                        <p class="text-sm text-gray-500 text-center">No hay fichas pendientes</p>
                    @endif

                    <h4 class="text-md font-semibold mt-6 text-center text-gray-700">Últimas Fichas Atendidas</h4>
                    <ul class="mt-2 space-y-2">
                        @foreach ($calledTickets[$area->id] ?? [] as $ticket)
                            <li class="flex justify-between items-center p-3 rounded-md bg-blue-50 text-blue-800 border">
                                <span class="font-semibold">{{ $ticket->ticket_number }}</span>
                                <button
                                    wire:click="recallTicket({{ $ticket->id }})"
                                    class="text-sm text-blue-600 hover:underline"
                                >
                                    Volver a llamar
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>

    @script
    <script>
        document.addEventListener('livewire:load', function () {
            Livewire.on('refresh-view', () => {
                Livewire.emit('loadTickets'); // Forzar recarga manual
            });

            $wire.on('no-tickets', (params) => {
                alert(`No hay fichas pendientes en el área ${params.areaId}.`);
            });

            $wire.on('ticket-called', () => {
                const audio = new Audio('/sounds/ticket-called.mp3');
                audio.play().catch(error => console.error('Error al reproducir el audio:', error));
            });
        });
    </script>
    @endscript
</div>