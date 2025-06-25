<div class="min-h-screen bg-gray-100 p-6 flex items-center justify-center">
    <div class="container mx-auto max-w-7xl">
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">Pantalla de Espera</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md" wire:poll.5s="loadAreas">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 text-center">Últimas Fichas Llamadas</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse ($areas as $area)
                        @php
                            $isBlinking = $blinkingAreas[$area->id] ?? false;
                        @endphp
                        <div 
                            class="p-4 rounded-lg shadow-sm text-center transition-all duration-500 transform"
                            x-data="ticketCard({{ $area->id }}, {{ $isBlinking ? 'true' : 'false' }})"
                            x-init="init()"
                            :class="{
                                'bg-green-100 border-2 border-green-500 shadow-lg animate-pulse-green': isBlinking,
                                'bg-blue-50': !isBlinking
                            }"
                            id="area-{{ $area->id }}"
                        >
                            <h3 class="text-lg font-medium" 
                                :class="isBlinking ? 'text-green-800' : 'text-blue-800'">
                                {{ $area->name }}
                            </h3>
                            @if ($area->display?->ticket)
                                <p
                                    :class="{
                                        'text-green-700 animate-bounce text-4xl': isBlinking,
                                        'text-blue-600 text-3xl': !isBlinking
                                    }"
                                    class="font-bold ticket-number transition-all duration-500"
                                >
                                    {{ $area->display->ticket->ticket_number }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ $area->display->puesto?->name ? 'Puesto: ' . $area->display->puesto->name : '' }}
                                    - Llamada a las {{ \Carbon\Carbon::parse($area->display->called_at)->format('H:i') }}
                                </p>
                            @else
                                <p class="text-sm text-gray-500">No hay fichas llamadas</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 text-center col-span-full">No hay áreas configuradas.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 text-center">Video de Espera</h2>
                <div x-data="{
                    activeIndex: 0,
                    videos: @js($videoUrls),
                    nextVideo() {
                        this.activeIndex = (this.activeIndex + 1) % this.videos.length;
                    }
                }" class="aspect-w-16 aspect-h-9">
                    <div class="text-gray-500 text-center flex items-center justify-center h-full" x-show="videos.length === 0">
                        No hay videos disponibles.
                    </div>
                    <template x-if="videos.length > 0">
                        <video
                            :src="videos[activeIndex].url"
                            controls
                            autoplay
                            @ended="nextVideo"
                            class="w-full h-full object-cover rounded-lg"
                        ></video>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Audio element para el sonido de notificación -->
    <audio id="notification-sound-player" preload="auto">
        <source src="{{ asset('sounds/ticket-called.mp3') }}" type="audio/mpeg">
        <!-- Fallback para otros navegadores -->
        <source src="{{ asset('sounds/ticket-called.ogg') }}" type="audio/ogg">
        <source src="{{ asset('sounds/ticket-called.wav') }}" type="audio/wav">
    </audio>

    <script>
        // Componente Alpine.js para manejar las tarjetas de tickets
        function ticketCard(areaId, initialBlinking) {
            return {
                areaId: areaId,
                isBlinking: initialBlinking,
                
                init() {
                    // Reproducir sonido si ya está parpadeando al inicializar
                    if (this.isBlinking) {
                        setTimeout(() => {
                            playNotificationSound();
                        }, 100);
                    }

                    // Escuchar eventos de Livewire
                    Livewire.on('auto-stop-blink', (data) => {
                        if (data.areaId === this.areaId) {
                            setTimeout(() => {
                                this.isBlinking = false;
                                // Llamar a Livewire para actualizar el estado
                                @this.call('stopBlink', this.areaId);
                            }, 5000);
                        }
                    });

                    // Escuchar cambios en el estado de Livewire
                    Livewire.on('ticket-updated', (data) => {
                        if (data.areaId === this.areaId) {
                            this.isBlinking = true;
                            // Reproducir sonido cuando comience a parpadear
                            setTimeout(() => {
                                playNotificationSound();
                            }, 100);
                        }
                    });
                }
            }
        }

        // Precargar y configurar el audio
        let notificationAudio = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            notificationAudio = document.getElementById('notification-sound-player');
            
            // Configurar el volumen
            notificationAudio.volume = 0.7;
            
            // Precargar el audio
            notificationAudio.load();
        });

        // Escuchar evento de sonido
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('play-notification-sound', () => {
                playNotificationSound();
            });
        });

        function playNotificationSound() {
            try {
                if (notificationAudio) {
                    // Resetear el audio al inicio
                    notificationAudio.currentTime = 0;
                    
                    // Reproducir el sonido
                    const playPromise = notificationAudio.play();
                    
                    if (playPromise !== undefined) {
                        playPromise.then(() => {
                            console.log('Sonido de notificación reproducido');
                        }).catch(error => {
                            console.log('Error al reproducir sonido:', error);
                            // Fallback al sonido generado si falla el archivo
                            playFallbackSound();
                        });
                    }
                } else {
                    console.log('Audio element no encontrado, usando fallback');
                    playFallbackSound();
                }
            } catch (e) {
                console.log('Error con el audio:', e);
                playFallbackSound();
            }
        }

        // Función de respaldo con sonido generado (la original)
        function playFallbackSound() {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = 800;
                oscillator.type = 'sine';
                
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
            } catch (e) {
                console.log('Audio no disponible:', e);
            }
        }

        // Función para habilitar audio en caso de que esté bloqueado
        function enableAudio() {
            if (notificationAudio) {
                notificationAudio.play().then(() => {
                    notificationAudio.pause();
                    notificationAudio.currentTime = 0;
                }).catch(() => {
                    console.log('Audio aún bloqueado');
                });
            }
        }

        // Habilitar audio después de la primera interacción del usuario
        document.addEventListener('click', enableAudio, { once: true });
        document.addEventListener('touchstart', enableAudio, { once: true });
    </script>

    <style>
        @keyframes pulse-green {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
            }
        }

        .animate-pulse-green {
            animation: pulse-green 2s ease-in-out infinite;
        }

        .animate-bounce {
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% {
                animation-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000);
                transform: translate3d(0,0,0);
            }
            40%, 43% {
                animation-timing-function: cubic-bezier(0.755, 0.050, 0.855, 0.060);
                transform: translate3d(0, -10px, 0);
            }
            70% {
                animation-timing-function: cubic-bezier(0.755, 0.050, 0.855, 0.060);
                transform: translate3d(0, -5px, 0);
            }
            90% {
                transform: translate3d(0,-2px,0);
            }
        }

        /* Ocultar el elemento de audio */
        #notification-sound-player {
            display: none;
        }
    </style>
</div>