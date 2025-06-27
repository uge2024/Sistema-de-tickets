<div>
    <div class="bg-white p-6 rounded-lg shadow-md mb-6" wire:poll.10s="loadTickets">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Llamar Ficha</h2>

        <div class="space-y-8">
            @foreach ($areas as $area)
                <div class="bg-gray-50 p-6 rounded-lg shadow-md border border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4 text-center">{{ $area->name }}</h3>

                    <button
                        wire:click="callNextTicket({{ $area->id }})"
                        onclick="handleTicketCall(this)"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg shadow-md text-base transition duration-300 ease-in-out mb-4 focus:outline-none focus:ring-2 focus:ring-green-500 touch-manipulation"
                        wire:loading.attr="disabled"
                        @disabled(empty($pendingTickets[$area->id] ?? []))
                        data-button-type="call-next"
                        data-area-id="{{ $area->id }}"
                    >
                        <span wire:loading.remove wire:target="callNextTicket({{ $area->id }})">Llamar Siguiente</span>
                        <span wire:loading wire:target="callNextTicket({{ $area->id }})">Llamando...</span>
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
                                    onclick="handleRecallTicket(this)"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    wire:loading.attr="disabled"
                                    wire:target="recallTicket({{ $ticket->id }})"
                                    data-button-type="recall"
                                    data-ticket-id="{{ $ticket->id }}"
                                    data-area-id="{{ $area->id }}"
                                >
                                    <span wire:loading.remove wire:target="recallTicket({{ $ticket->id }})">Volver a llamar</span>
                                    <span wire:loading wire:target="recallTicket({{ $ticket->id }})">Llamando...</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        // Variables globales para el audio
        window.ticketAudio = null;
        window.audioInitialized = false;

        // Inicializar audio cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            initializeAudio();
        });

        function initializeAudio() {
            try {
                window.ticketAudio = new Audio('/sounds/ticket-called.mp3');
                window.ticketAudio.volume = 0.7;
                window.audioInitialized = true;
                console.log('Audio inicializado correctamente');
            } catch (e) {
                console.error('Error al inicializar audio:', e);
                window.audioInitialized = false;
            }
        }

        // Función para reproducir sonido
        function playTicketSound() {
            console.log('Reproduciendo sonido de notificación');
            // Usar directamente el beep generado para evitar problemas con archivos
            playFallbackBeep();
        }

        // Sonido de respaldo
        function playFallbackBeep() {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = 800;
                oscillator.type = 'sine';
                gainNode.gain.value = 0.3;
                
                oscillator.start();
                oscillator.stop(audioContext.currentTime + 0.2);
                
                console.log('Beep de respaldo reproducido');
            } catch (e) {
                console.error('Error en fallback beep:', e);
            }
        }

        // Efecto visual de parpadeo
        function flashButton(button) {
            if (!button) return;
            
            const originalBg = button.className;
            const isRecall = button.getAttribute('data-button-type') === 'recall';
            
            // Aplicar efecto inmediato
            button.style.transform = 'scale(1.1)';
            button.style.boxShadow = '0 0 20px rgba(59, 130, 246, 0.8)';
            
            if (isRecall) {
                button.classList.remove('bg-blue-600');
                button.classList.add('bg-blue-800');
            } else {
                button.classList.remove('bg-green-600');
                button.classList.add('bg-green-800');
            }
            
            // Restaurar después de 400ms
            setTimeout(() => {
                button.style.transform = '';
                button.style.boxShadow = '';
                button.className = originalBg;
            }, 400);
        }

        // Manejador para llamar ticket
        function handleTicketCall(button) {
            console.log('Llamando ticket nuevo');
            playTicketSound();
            flashButton(button);
        }

        // Manejador para volver a llamar
        function handleRecallTicket(button) {
            console.log('Volviendo a llamar ticket');
            playTicketSound();
            flashButton(button);
        }

        // Escuchar eventos de Livewire
        document.addEventListener('livewire:initialized', function () {
            Livewire.on('ticket-called', (data) => {
                console.log('Evento ticket-called recibido:', data);
                // El sonido y parpadeo ya se manejan en onclick
            });

            Livewire.on('no-tickets', () => {
                alert('No hay fichas pendientes');
            });
        });

        // Habilitar audio en el primer click
        document.addEventListener('click', function enableAudioContext() {
            if (window.ticketAudio && !window.audioInitialized) {
                initializeAudio();
            }
            
            // Intentar reproducir y pausar para desbloquear
            if (window.ticketAudio) {
                window.ticketAudio.play().then(() => {
                    window.ticketAudio.pause();
                    window.ticketAudio.currentTime = 0;
                    console.log('Audio desbloqueado');
                }).catch(() => {
                    console.log('Audio bloqueado por el navegador');
                });
            }
        }, { once: true });
    </script>
</div>