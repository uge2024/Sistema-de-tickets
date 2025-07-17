<div class="min-h-screen bg-gray-100" id="kiosk-container">
    
    <!-- Botón discreto para salir (solo visible con hover) -->
    <button onclick="exitKioskMode()" 
            class="fixed top-2 right-2 z-50 bg-red-600 hover:bg-red-700 text-white p-2 rounded opacity-0 hover:opacity-100 transition-opacity duration-300 text-sm">
        Salir
    </button>

    <div class="w-full h-screen flex flex-col px-0">
        
        <!-- Header simplificado -->
        <div class="text-center mb-6">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-2">
                <div id="current-time">
                    --:--:--
                </div>
            </h1>
        </div>

        <!-- Contenido principal -->
        <div class="flex-1 grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Columna de fichas llamadas -->
            <div class="flex flex-col">
                <!-- 🔥 CAMBIO: Polling más inteligente -->
                <div class="bg-white p-6 rounded-lg shadow-md flex-1" wire:poll.15s="checkForUpdates">
                    <h2 class="text-2xl md:text-3xl font-semibold text-gray-800 mb-6 text-center">
                        <i class="fas fa-bullhorn mr-3 text-blue-600"></i>
                        Últimas Fichas Llamadas
                    </h2>
                    
                    <div class="space-y-6">
                        @forelse ($areas as $area)
                            @php
                                $isBlinking = $blinkingAreas[$area->id] ?? false;
                            @endphp
                            <div 
                                class="ticket-card p-8 rounded-xl shadow-lg text-center transition-all duration-500 transform"
                                data-area-id="{{ $area->id }}"
                                x-data="{ 
                                    areaId: {{ $area->id }},
                                    isBlinking: {{ $isBlinking ? 'true' : 'false' }},
                                    initialized: false,
                                    
                                    init() {
                                        if (this.initialized) return; // 🔥 PREVENIR DOBLE INICIALIZACIÓN
                                        this.initialized = true;
                                        
                                        // 🔥 REDUCIR LOGS: Solo en debug
                                        if (window.kioskDebug) {
                                            console.log('INIT Área ' + this.areaId + ' - Parpadeo: ' + this.isBlinking);
                                        }
                                        
                                        if (!window.kioskCards) window.kioskCards = {};
                                        window.kioskCards[this.areaId] = this;
                                        
                                        if (this.isBlinking) {
                                            this.activateBlink();
                                        }
                                    },
                                    
                                    activateBlink() {
                                        if (this.isBlinking) return; // 🔥 PREVENIR DOBLE ACTIVACIÓN
                                        
                                        if (window.kioskDebug) {
                                            console.log('🔥 ACTIVANDO parpadeo área ' + this.areaId);
                                        }
                                        this.isBlinking = true;
                                        window.triggerAreaBlink(this.areaId);
                                    },
                                    
                                    stopBlink() {
                                        if (!this.isBlinking) return; // 🔥 PREVENIR DOBLE DESACTIVACIÓN
                                        
                                        if (window.kioskDebug) {
                                            console.log('🛑 DETENIENDO parpadeo área ' + this.areaId);
                                        }
                                        this.isBlinking = false;
                                    }
                                }"
                                :class="{
                                    'bg-gradient-to-r from-green-400 to-green-600 border-4 border-yellow-400 shadow-2xl animate-pulse-green scale-105': isBlinking,
                                    'bg-blue-50 border border-blue-200': !isBlinking
                                }"
                                id="area-{{ $area->id }}"
                            >
                                <!-- Nombre del área -->
                                <h3 class="text-3xl md:text-4xl font-bold mb-4 transition-all duration-300" 
                                    :class="isBlinking ? 'text-white' : 'text-blue-800'">
                                    {{ $area->name }}
                                </h3>
                                
                                @if ($area->display && $area->display->ticket)
                                    <!-- Número de ticket - EXTRA GRANDE -->
                                    <div class="mb-6">
                                        <p class="text-sm md:text-base opacity-80 mb-2"
                                           :class="isBlinking ? 'text-yellow-200' : 'text-blue-600'">
                                            TICKET
                                        </p>
                                        <p class="font-black transition-all duration-500 leading-tight"
                                           :class="{
                                               'text-white text-7xl md:text-8xl lg:text-9xl animate-bounce': isBlinking,
                                               'text-blue-600 text-6xl md:text-7xl lg:text-8xl': !isBlinking
                                           }">
                                            {{ $area->display->ticket->ticket_number }}
                                        </p>
                                    </div>
                                    
                                    @if ($area->display->puesto && $area->display->puesto->name)
                                        <!-- Información del puesto - EXTRA GRANDE -->
                                        <div class="mb-4 transition-all duration-300"
                                             :class="{
                                                 'animate-pulse-text scale-110': isBlinking,
                                                 '': !isBlinking
                                             }">
                                            <p class="text-sm md:text-base opacity-80 mb-2"
                                               :class="isBlinking ? 'text-yellow-200' : 'text-blue-600'">
                                                DIRIGIRSE A
                                            </p>
                                            <p class="font-bold text-2xl md:text-3xl lg:text-4xl"
                                               :class="{
                                                   'text-black text-7xl md:text-8xl lg:text-9xl animate-bounce': isBlinking,
                                                   'text-black-600 text-6xl md:text-7xl lg:text-8xl': !isBlinking
                                               }">
                                                {{ $area->display->puesto->name }}
                                            </p>
                                        </div>
                                    @endif
                                    
                                    <!-- Hora de llamada -->
                                    <div class="text-lg md:text-xl opacity-75"
                                         :class="{
                                             'text-yellow-200 font-semibold': isBlinking,
                                             'text-gray-600': !isBlinking
                                         }">
                                        <i class="fas fa-clock mr-2"></i>
                                        Llamada: {{ \Carbon\Carbon::parse($area->display->called_at)->format('H:i:s') }}
                                    </div>
                                @else
                                    <div class="text-center py-12">
                                        <i class="fas fa-hourglass-half text-6xl text-blue-300 mb-4 opacity-50"></i>
                                        <p class="text-blue-400 text-xl">En espera...</p>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-16">
                                <i class="fas fa-info-circle text-8xl text-gray-400 mb-6"></i>
                                <p class="text-gray-500 text-2xl">No hay áreas configuradas</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Columna del video -->
            <div class="flex flex-col">
                <div class="bg-black rounded-lg shadow-2xl flex-1 relative overflow-hidden" style="min-height: 70vh;">
                    <div 
                        x-data="{
                            activeIndex: 0,
                            videos: @js($videoUrls),
                            nextVideo() {
                                this.activeIndex = (this.activeIndex + 1) % this.videos.length;
                            }
                        }" 
                        class="absolute inset-0 w-full h-full"
                    >
                        <!-- Mensaje cuando no hay videos -->
                        <div class="absolute inset-0 flex items-center justify-center text-white" x-show="videos.length === 0">
                            <div class="text-center">
                                <i class="fas fa-video-slash text-8xl mb-6 opacity-50"></i>
                                <p class="text-2xl opacity-75">No hay videos disponibles</p>
                            </div>
                        </div>

                        <!-- Video principal -->
                        <template x-if="videos.length > 0">
                            <video
                                :src="videos[activeIndex].url"
                                controls
                                autoplay
                                @ended="nextVideo"
                                class="absolute inset-0 w-full h-full"
                                style="object-fit: contain; object-position: center;"
                            ></video>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer con información del sistema -->
        <div class="mt-6 text-center">
            <div class="bg-white/80 rounded-lg p-3">
                <div class="flex justify-center items-center space-x-8 text-sm text-gray-600">
                    <div class="flex items-center">
                        <i class="fas fa-wifi text-green-500 mr-2"></i>
                        <span>Sistema Conectado</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-users text-blue-500 mr-2"></i>
                        <span>{{ $areas->count() }} Áreas Activas</span>
                    </div>
                    <div class="flex items-center" id="current-date">
                        <i class="fas fa-calendar text-purple-500 mr-2"></i>
                        <span>--</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audio para notificaciones -->
    <audio id="notification-sound-player" preload="auto">
        <source src="{{ asset('sounds/ticket-called.mp3') }}" type="audio/mpeg">
        <source src="{{ asset('sounds/ticket-called.ogg') }}" type="audio/ogg">
        <source src="{{ asset('sounds/ticket-called.wav') }}" type="audio/wav">
    </audio>

    <script>
        // 🔥 CONFIGURACIÓN GLOBAL
        window.kioskDebug = false; // Cambiar a true solo para debugging
        window.kioskInitialized = false;
        
        let isKioskMode = false;
        let notificationAudio = null;
        let audioUnlocked = false;
        
        // SISTEMA GLOBAL OPTIMIZADO
        window.kioskCards = window.kioskCards || {};
        window.activeIntervals = window.activeIntervals || {};
        window.lastEventTime = 0;
        window.eventBuffer = new Map(); // 🔥 NUEVO: Buffer para evitar eventos duplicados

        // 🔥 FUNCIÓN GLOBAL OPTIMIZADA PARA MANEJAR PARPADEO
        window.triggerAreaBlink = function(areaId) {
            const currentTime = Date.now();
            const lastTrigger = window.eventBuffer.get(areaId) || 0;
            
            // 🔥 DEBOUNCE: Evitar triggers muy frecuentes (2 segundos mínimo)
            if (currentTime - lastTrigger < 2000) {
                if (window.kioskDebug) {
                    console.log('⏳ Trigger bloqueado por debounce área ' + areaId);
                }
                return;
            }
            
            window.eventBuffer.set(areaId, currentTime);
            
            if (window.kioskDebug) {
                console.log('🎯 Sistema global: activando parpadeo área ' + areaId);
            }
            
            // Limpiar cualquier parpadeo anterior
            if (window.activeIntervals[areaId]) {
                clearInterval(window.activeIntervals[areaId].interval);
                clearTimeout(window.activeIntervals[areaId].timeout);
                delete window.activeIntervals[areaId];
            }
            
            // Activar parpadeo en todas las tarjetas de esta área
            updateAreaState(areaId, true);
            
            // Sonido inmediato
            playNotificationSound();
            
            // 🔥 INTERVALOS OPTIMIZADOS: Menos frecuentes
            const intervalId = setInterval(() => {
                playNotificationSound();
            }, 5000); // Cada 5 segundos en lugar de 4
            
            const timeoutId = setTimeout(() => {
                stopAreaBlink(areaId);
            }, 20000);
            
            window.activeIntervals[areaId] = {
                interval: intervalId,
                timeout: timeoutId
            };
        };

        // FUNCIÓN GLOBAL PARA DETENER PARPADEO
        window.stopAreaBlink = function(areaId) {
            if (window.kioskDebug) {
                console.log('🛑 Sistema global: deteniendo parpadeo área ' + areaId);
            }
            
            // Limpiar del buffer
            window.eventBuffer.delete(areaId);
            
            // Limpiar intervalos
            if (window.activeIntervals[areaId]) {
                clearInterval(window.activeIntervals[areaId].interval);
                clearTimeout(window.activeIntervals[areaId].timeout);
                delete window.activeIntervals[areaId];
            }
            
            // Desactivar parpadeo en todas las tarjetas de esta área
            updateAreaState(areaId, false);
        };

        // FUNCIÓN PARA ACTUALIZAR ESTADO EN TODAS LAS TARJETAS DE UN ÁREA
        function updateAreaState(areaId, isBlinking) {
            // Actualizar en Alpine.js
            if (window.kioskCards[areaId]) {
                window.kioskCards[areaId].isBlinking = isBlinking;
            }
            
            // Actualizar todas las instancias visibles
            const elements = document.querySelectorAll('[data-area-id="' + areaId + '"]');
            elements.forEach(el => {
                try {
                    const alpine = Alpine.$data(el);
                    if (alpine) {
                        alpine.isBlinking = isBlinking;
                    }
                } catch (e) {
                    // Ignorar errores de Alpine silenciosamente
                }
            });
        }

        // Inicialización principal
        document.addEventListener('DOMContentLoaded', function() {
            if (window.kioskInitialized) return; // 🔥 PREVENIR DOBLE INICIALIZACIÓN
            window.kioskInitialized = true;
            
            if (window.kioskDebug) {
                console.log('🚀 Iniciando sistema de kiosko...');
            }
            
            initializeKiosk();
            initializeAudio();
            initializeClock();
            setupEventListeners();
        });

        // 🔥 CONFIGURAR LISTENERS OPTIMIZADOS - UNA SOLA VEZ
        function setupEventListeners() {
            // 🔥 PREVENIR MÚLTIPLES CONFIGURACIONES
            if (window.listenersConfigured) return;
            window.listenersConfigured = true;
            
            document.addEventListener('livewire:initialized', () => {
                if (window.kioskDebug) {
                    console.log('📡 Configurando listeners de Livewire...');
                }
                
                // 🔥 LISTENERS CON DEBOUNCE
                let lastEventTime = 0;
                const eventDebounce = 1000; // 1 segundo mínimo entre eventos
                
                Livewire.on('blink-start', (data) => {
                    const currentTime = Date.now();
                    if (currentTime - lastEventTime < eventDebounce) {
                        return; // Ignorar eventos muy frecuentes
                    }
                    lastEventTime = currentTime;
                    
                    if (window.kioskDebug) {
                        console.log('🎯 EVENTO blink-start recibido:', data);
                    }
                    handleTicketEvent(data);
                });
                
                Livewire.on('ticket-called', (data) => {
                    const currentTime = Date.now();
                    if (currentTime - lastEventTime < eventDebounce) {
                        return;
                    }
                    lastEventTime = currentTime;
                    
                    if (window.kioskDebug) {
                        console.log('🎯 EVENTO ticket-called recibido:', data);
                    }
                    handleTicketEvent(data);
                });
                
                Livewire.on('blink-area', (areaId) => {
                    if (window.kioskDebug) {
                        console.log('🎯 EVENTO blink-area recibido:', areaId);
                    }
                    if (areaId) {
                        window.triggerAreaBlink(areaId);
                    }
                });
                
                Livewire.on('play-notification-sound', () => {
                    if (window.kioskDebug) {
                        console.log('🎯 EVENTO play-notification-sound recibido');
                    }
                    playNotificationSound();
                });
                
                if (window.kioskDebug) {
                    console.log('✅ Listeners configurados');
                }
            });
        }

        // 🔥 MANEJAR EVENTOS DE TICKETS OPTIMIZADO
        function handleTicketEvent(data) {
            if (window.kioskDebug) {
                console.log('🔄 Procesando evento de ticket:', data);
            }
            
            let targetAreaId = null;
            
            // Manejar diferentes formatos de datos
            if (Array.isArray(data)) {
                if (data.length > 0) {
                    targetAreaId = data[0].areaId || data[0].area_id || data[0];
                }
            } else if (data && typeof data === 'object') {
                targetAreaId = data.areaId || data.area_id || data.id;
            } else if (typeof data === 'number' || typeof data === 'string') {
                targetAreaId = data;
            }
            
            if (targetAreaId) {
                if (window.kioskDebug) {
                    console.log('✅ Activando parpadeo para área:', targetAreaId);
                }
                window.triggerAreaBlink(targetAreaId);
            } else if (window.kioskDebug) {
                console.log('❌ No se pudo determinar área objetivo');
            }
        }

        function initializeKiosk() {
            document.addEventListener('click', function() {
                if (!isKioskMode) {
                    enterKioskMode();
                }
                unlockAudio();
            }, { once: true });

            setTimeout(() => {
                if (!isKioskMode) {
                    enterKioskMode();
                }
            }, 3000);
        }

        function enterKioskMode() {
            const element = document.documentElement;
            
            if (element.requestFullscreen) {
                element.requestFullscreen().then(() => {
                    isKioskMode = true;
                    if (window.kioskDebug) {
                        console.log('✅ Modo kiosko activado');
                    }
                }).catch(err => {
                    if (window.kioskDebug) {
                        console.log('❌ No se pudo activar pantalla completa:', err);
                    }
                });
            } else if (element.webkitRequestFullscreen) {
                element.webkitRequestFullscreen();
                isKioskMode = true;
            } else if (element.mozRequestFullScreen) {
                element.mozRequestFullScreen();
                isKioskMode = true;
            } else if (element.msRequestFullscreen) {
                element.msRequestFullscreen();
                isKioskMode = true;
            }
        }

        function exitKioskMode() {
            if (document.exitFullscreen) {
                document.exitFullscreen().then(() => {
                    isKioskMode = false;
                }).catch(err => {
                    console.log('Error al salir de pantalla completa:', err);
                });
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
                isKioskMode = false;
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
                isKioskMode = false;
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
                isKioskMode = false;
            }
        }

        // 🔥 RELOJ OPTIMIZADO
        function initializeClock() {
            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('es-ES');
                const dateString = now.toLocaleDateString('es-ES', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                
                const timeElement = document.getElementById('current-time');
                const dateElement = document.getElementById('current-date');
                
                if (timeElement) timeElement.textContent = timeString;
                if (dateElement) {
                    dateElement.innerHTML = 
                        '<i class="fas fa-calendar text-purple-500 mr-2"></i><span>' + dateString + '</span>';
                }
            }
            
            updateClock();
            setInterval(updateClock, 1000);
        }

        function initializeAudio() {
            notificationAudio = document.getElementById('notification-sound-player');
            if (notificationAudio) {
                notificationAudio.volume = 0.9;
                notificationAudio.load();
                if (window.kioskDebug) {
                    console.log('🔊 Audio inicializado');
                }
            }
        }

        function unlockAudio() {
            if (audioUnlocked) return;
            
            if (notificationAudio) {
                notificationAudio.play().then(() => {
                    notificationAudio.pause();
                    notificationAudio.currentTime = 0;
                    audioUnlocked = true;
                    if (window.kioskDebug) {
                        console.log('🔓 Audio desbloqueado');
                    }
                }).catch(() => {
                    if (window.kioskDebug) {
                        console.log('🔒 Audio bloqueado por navegador');
                    }
                });
            }
        }

        // 🔥 AUDIO CON THROTTLE
        let lastSoundPlay = 0;
        function playNotificationSound() {
            const currentTime = Date.now();
            if (currentTime - lastSoundPlay < 1000) { // Mínimo 1 segundo entre sonidos
                return;
            }
            lastSoundPlay = currentTime;
            
            if (!audioUnlocked) {
                playFallbackSound();
                return;
            }
            
            try {
                if (notificationAudio) {
                    notificationAudio.currentTime = 0;
                    const playPromise = notificationAudio.play();
                    
                    if (playPromise !== undefined) {
                        playPromise.then(() => {
                            if (window.kioskDebug) {
                                console.log('🎵 Sonido reproducido');
                            }
                        }).catch(error => {
                            if (window.kioskDebug) {
                                console.log('❌ Error audio:', error);
                            }
                            playFallbackSound();
                        });
                    }
                } else {
                    playFallbackSound();
                }
            } catch (e) {
                playFallbackSound();
            }
        }

        function playFallbackSound() {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                
                const frequencies = [800, 1000, 800, 1200, 1000];
                let startTime = audioContext.currentTime;
                
                frequencies.forEach((freq, index) => {
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    oscillator.frequency.value = freq;
                    oscillator.type = 'sine';
                    gainNode.gain.value = 0.5;
                    
                    const noteStart = startTime + (index * 0.2);
                    const noteEnd = noteStart + 0.18;
                    
                    oscillator.start(noteStart);
                    oscillator.stop(noteEnd);
                });
                
                if (window.kioskDebug) {
                    console.log('🎵 Sonido fallback reproducido');
                }
            } catch (e) {
                if (window.kioskDebug) {
                    console.log('❌ Audio no disponible:', e);
                }
            }
        }

        // Eventos de teclado
        document.addEventListener('keydown', function(event) {
            if (event.key === 'F11' || 
                (event.altKey && event.key === 'Tab') ||
                (event.ctrlKey && event.key === 't') ||
                (event.ctrlKey && event.key === 'T') ||
                (event.ctrlKey && event.key === 'n') ||
                (event.ctrlKey && event.key === 'N') ||
                (event.ctrlKey && event.key === 'w') ||
                (event.ctrlKey && event.key === 'W')) {
                event.preventDefault();
                return false;
            }
            
            if (event.key === 'Escape') {
                exitKioskMode();
            }
        });

        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                isKioskMode = false;
                if (window.kioskDebug) {
                    console.log('❌ Salió del modo kiosko');
                }
            }
        });

        // Desbloquear audio con cualquier interacción
        document.addEventListener('click', unlockAudio, { once: true });
        document.addEventListener('touchstart', unlockAudio, { once: true });

        // 🔥 LIMPIEZA AL CERRAR/RECARGAR PÁGINA
        window.addEventListener('beforeunload', function() {
            // Limpiar todos los intervalos activos
            Object.values(window.activeIntervals).forEach(interval => {
                if (interval.interval) clearInterval(interval.interval);
                if (interval.timeout) clearTimeout(interval.timeout);
            });
            window.activeIntervals = {};
        });

        if (window.kioskDebug) {
            console.log('✅ Sistema de kiosko inicializado');
        }
    </script>

    <style>
        @keyframes pulse-green {
            0%, 100% {
                transform: scale(1.05);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.9), 0 0 40px rgba(255, 255, 0, 0.8);
            }
            50% {
                transform: scale(1.18);
                box-shadow: 0 0 0 25px rgba(34, 197, 94, 0), 0 0 60px rgba(255, 255, 0, 1);
            }
        }

        .animate-pulse-green {
            animation: pulse-green 1.2s ease-in-out infinite;
        }

        @keyframes pulse-text {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.8;
                transform: scale(1.1);
            }
        }

        .animate-pulse-text {
            animation: pulse-text 0.8s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% {
                animation-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000);
                transform: translate3d(0,0,0) scale(1);
            }
            40%, 43% {
                animation-timing-function: cubic-bezier(0.755, 0.050, 0.855, 0.060);
                transform: translate3d(0, -20px, 0) scale(1.1);
            }
            70% {
                animation-timing-function: cubic-bezier(0.755, 0.050, 0.855, 0.060);
                transform: translate3d(0, -10px, 0) scale(1.05);
            }
            90% {
                transform: translate3d(0,-4px,0) scale(1.02);
            }
        }

        .animate-bounce {
            animation: bounce 0.6s infinite;
        }

        #notification-sound-player {
            display: none;
        }

        video {
            display: block !important;
            max-width: none !important;
            max-height: none !important;
        }

        ::-webkit-scrollbar {
            width: 0px;
            background: transparent;
        }

        :fullscreen {
            background: #f3f4f6;
        }

        :-webkit-full-screen {
            background: #f3f4f6;
        }

        :-moz-full-screen {
            background: #f3f4f6;
        }

        :-ms-fullscreen {
            background: #f3f4f6;
        }
    </style>
</div>