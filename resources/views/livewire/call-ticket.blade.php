<div>
    <div class="bg-white p-6 rounded-lg shadow-md mb-6" wire:poll.10s="loadTickets">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Llamar Ficha</h2>
        
        <!-- Información del puesto actual -->
        <div class="bg-blue-50 p-4 rounded-lg mb-6 text-center">
            <h3 class="text-lg font-semibold text-blue-800">
                Puesto: {{ $puesto->name ?? 'No asignado' }}
            </h3>
            <p class="text-blue-600">
                Área: {{ $area->name ?? 'No asignada' }}
            </p>
        </div>

        <!-- ← CAMBIO: Trabajar con área única, no colección -->
        <div class="bg-gray-50 p-6 rounded-lg shadow-md border border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 text-center">{{ $area->name }}</h3>

            <!-- Botón para llamar siguiente ticket -->
            <button
                wire:click="callNextTicket({{ $area->id }})"
                onclick="handleTicketCall(this)"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg shadow-md text-base transition duration-300 ease-in-out mb-4 focus:outline-none focus:ring-2 focus:ring-green-500 touch-manipulation"
                wire:loading.attr="disabled"
                @disabled(empty($pendingTickets))
                data-button-type="call-next"
                data-area-id="{{ $area->id }}"
            >
                <span wire:loading.remove wire:target="callNextTicket({{ $area->id }})">
                    <i class="fas fa-microphone mr-2"></i>Llamar Siguiente
                </span>
                <span wire:loading wire:target="callNextTicket({{ $area->id }})">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Llamando...
                </span>
            </button>

            <!-- Lista de tickets pendientes -->
            <div class="mb-6">
                <h4 class="text-md font-semibold mb-3 text-gray-700">Tickets Pendientes</h4>
                @if ($pendingTickets->count() > 0)
                    <ul class="space-y-2 max-h-60 overflow-y-auto">
                        @foreach ($pendingTickets as $index => $ticket)
                            <li class="flex justify-between items-center p-3 rounded-md shadow-sm border border-gray-100 
                                {{ $ticket->type == 'senior' ? 'bg-purple-50 border-purple-200' : 'bg-green-50 border-green-200' }} 
                                hover:bg-opacity-80 transition-colors duration-200
                                {{ $index === 0 ? 'ring-2 ring-yellow-400 bg-yellow-50' : '' }}">
                                
                                <div class="flex items-center space-x-3">
                                    @if ($index === 0)
                                        <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full font-bold">
                                            SIGUIENTE
                                        </span>
                                    @endif
                                    
                                    <span class="text-gray-800 font-bold text-lg">{{ $ticket->ticket_number }}</span>
                                    
                                    @if ($ticket->type == 'senior')
                                        <span class="bg-purple-500 text-white text-xs px-2 py-1 rounded-full">
                                            <i class="fas fa-user-check mr-1"></i>Tercera Edad
                                        </span>
                                    @else
                                        <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                                            Normal
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">
                                        {{ $ticket->created_at->format('H:i') }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        Pos. {{ $index + 1 }}
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-3 p-2 bg-gray-100 rounded text-center">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-clock mr-1"></i>
                            Total pendientes: <span class="font-bold">{{ $pendingTickets->count() }}</span>
                        </p>
                    </div>
                @else
                    <div class="text-center p-6 bg-gray-100 rounded-lg">
                        <i class="fas fa-inbox text-4xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-500">No hay fichas pendientes</p>
                    </div>
                @endif
            </div>

            <!-- Mis tickets llamados -->
            <div>
                <h4 class="text-md font-semibold mb-3 text-gray-700 flex items-center">
                    <i class="fas fa-user mr-2 text-blue-600"></i>
                    Mis Últimas Fichas Llamadas
                </h4>
                
                @forelse ($calledTickets as $ticket)
                    <div class="mb-2 p-3 rounded-md bg-blue-50 border border-blue-200 hover:bg-blue-100 transition-colors duration-200">
                        <div class="flex justify-between items-center">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <span class="text-blue-800 font-bold text-lg">{{ $ticket->ticket_number }}</span>
                                    
                                    @if ($ticket->type == 'senior')
                                        <span class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded-full">
                                            Tercera Edad
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="mt-1 space-y-1">
                                    <p class="text-xs text-blue-600">
                                        <i class="fas fa-clock mr-1"></i>
                                        Llamada: {{ $ticket->updated_at->format('H:i:s') }}
                                    </p>
                                    <p class="text-xs text-blue-500">
                                        <i class="fas fa-calendar mr-1"></i>
                                        {{ $ticket->updated_at->format('d/m/Y') }}
                                    </p>
                                </div>
                            </div>
                            
                            <button
                                wire:click="recallTicket({{ $ticket->id }})"
                                onclick="handleRecallTicket(this)"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md"
                                wire:loading.attr="disabled"
                                wire:target="recallTicket({{ $ticket->id }})"
                                data-button-type="recall"
                                data-ticket-id="{{ $ticket->id }}"
                                data-area-id="{{ $area->id }}"
                            >
                                <span wire:loading.remove wire:target="recallTicket({{ $ticket->id }})">
                                    <i class="fas fa-redo mr-1"></i>Volver a llamar
                                </span>
                                <span wire:loading wire:target="recallTicket({{ $ticket->id }})">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>Llamando...
                                </span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center p-4 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                        <i class="fas fa-info-circle text-2xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-500">No has llamado ninguna ficha aún</p>
                        <p class="text-xs text-gray-400 mt-1">
                            Los tickets que llames aparecerán aquí
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Script mejorado -->
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
            playFallbackBeep();
        }

        // Sonido de respaldo mejorado
        function playFallbackBeep() {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                
                const frequencies = [800, 1000, 800];
                let startTime = audioContext.currentTime;
                
                frequencies.forEach((freq, index) => {
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    oscillator.frequency.value = freq;
                    oscillator.type = 'sine';
                    gainNode.gain.value = 0.3;
                    
                    const noteStart = startTime + (index * 0.15);
                    const noteEnd = noteStart + 0.1;
                    
                    oscillator.start(noteStart);
                    oscillator.stop(noteEnd);
                });
                
                console.log('Beep mejorado reproducido');
            } catch (e) {
                console.error('Error en fallback beep:', e);
            }
        }

        // Efecto visual mejorado de parpadeo
        function flashButton(button) {
            if (!button) return;
            
            const originalClasses = button.className;
            const isRecall = button.getAttribute('data-button-type') === 'recall';
            
            button.style.transform = 'scale(1.05)';
            button.style.transition = 'all 0.2s ease';
            
            if (isRecall) {
                button.style.boxShadow = '0 0 20px rgba(37, 99, 235, 0.8)';
                button.classList.remove('bg-blue-600');
                button.classList.add('bg-blue-800');
            } else {
                button.style.boxShadow = '0 0 20px rgba(34, 197, 94, 0.8)';
                button.classList.remove('bg-green-600');
                button.classList.add('bg-green-800');
            }
            
            setTimeout(() => {
                button.style.transform = '';
                button.style.boxShadow = '';
                button.className = originalClasses;
            }, 300);
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

    <!-- Estilos adicionales -->
    <style>
        @keyframes pulse-yellow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.7);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(251, 191, 36, 0);
            }
        }

        .ring-2.ring-yellow-400 {
            animation: pulse-yellow 2s infinite;
        }

        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }

        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        button:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        * {
            transition: background-color 0.2s ease, color 0.2s ease;
        }
    </style>
</div>