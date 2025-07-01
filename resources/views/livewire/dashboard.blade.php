<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        ¡Bienvenido, {{ Auth::user()->name }}! 👋
                    </h1>
                    <p class="text-gray-600 mt-1">
                        Puesto: <span class="font-semibold">{{ $puesto->name ?? 'No asignado' }}</span> - 
                        Área: <span class="font-semibold">{{ $area->name ?? 'No asignada' }}</span>
                    </p>
                </div>
                
                <!-- Controles -->
                <div class="flex items-center space-x-4">
                    <select wire:model.live="selectedPeriod" 
                            class="rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach($availablePeriods as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    
                    <button wire:click="refreshStats"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors duration-200">
                        <i class="fas fa-sync-alt mr-2"></i>Actualizar
                    </button>
                </div>
            </div>
        </div>

        <!-- Tarjetas de estadísticas principales -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total de tickets -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm font-medium">Total Atendidos</p>
                        <p class="text-4xl font-bold text-blue-600 mt-1" wire:loading.class="opacity-50">
                            {{ $myStats['total'] ?? 0 }}
                        </p>
                        <p class="text-gray-500 text-xs mt-1">{{ $availablePeriods[$selectedPeriod] }}</p>
                    </div>
                    <div class="text-blue-500">
                        <i class="fas fa-users text-4xl"></i>
                    </div>
                </div>
            </div>

            <!-- Tickets Normales -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm font-medium">Tickets Normales</p>
                        <div class="flex items-baseline gap-2 mt-1">
                            <p class="text-4xl font-bold text-green-600" wire:loading.class="opacity-50">
                                {{ $myStats['normal'] ?? 0 }}
                            </p>
                            @if(($myStats['total'] ?? 0) > 0)
                                <span class="text-sm text-green-500 font-semibold">
                                    {{ $myStats['porcentaje_normal'] ?? 0 }}%
                                </span>
                            @endif
                        </div>
                        <p class="text-gray-500 text-xs mt-1">Atención regular</p>
                    </div>
                    <div class="text-green-500">
                        <i class="fas fa-user text-4xl"></i>
                    </div>
                </div>
            </div>

            <!-- Tickets Tercera Edad -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500 transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm font-medium">Tercera Edad</p>
                        <div class="flex items-baseline gap-2 mt-1">
                            <p class="text-4xl font-bold text-purple-600" wire:loading.class="opacity-50">
                                {{ $myStats['senior'] ?? 0 }}
                            </p>
                            @if(($myStats['total'] ?? 0) > 0)
                                <span class="text-sm text-purple-500 font-semibold">
                                    {{ $myStats['porcentaje_senior'] ?? 0 }}%
                                </span>
                            @endif
                        </div>
                        <p class="text-gray-500 text-xs mt-1">Atención prioritaria</p>
                    </div>
                    <div class="text-purple-500">
                        <i class="fas fa-user-check text-4xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfica visual de proporción -->
        @if(($myStats['total'] ?? 0) > 0)
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">
                <i class="fas fa-chart-pie mr-2 text-blue-500"></i>
                Distribución de Tickets Atendidos
            </h3>
            
            <!-- Barra de progreso visual -->
            <div class="flex rounded-lg overflow-hidden h-8 bg-gray-200 mb-4">
                @if(($myStats['normal'] ?? 0) > 0)
                    <div class="bg-green-500 flex items-center justify-center text-white text-sm font-semibold transition-all duration-500"
                         style="width: {{ $myStats['porcentaje_normal'] ?? 0 }}%">
                        @if(($myStats['porcentaje_normal'] ?? 0) > 15)
                            {{ $myStats['normal'] }} Normal
                        @endif
                    </div>
                @endif
                
                @if(($myStats['senior'] ?? 0) > 0)
                    <div class="bg-purple-500 flex items-center justify-center text-white text-sm font-semibold transition-all duration-500"
                         style="width: {{ $myStats['porcentaje_senior'] ?? 0 }}%">
                        @if(($myStats['porcentaje_senior'] ?? 0) > 15)
                            {{ $myStats['senior'] }} Senior
                        @endif
                    </div>
                @endif
            </div>
            
            <!-- Leyenda -->
            <div class="flex justify-center space-x-6 text-sm">
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                    <span>Normal: {{ $myStats['normal'] ?? 0 }} ({{ $myStats['porcentaje_normal'] ?? 0 }}%)</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-purple-500 rounded mr-2"></div>
                    <span>Tercera Edad: {{ $myStats['senior'] ?? 0 }} ({{ $myStats['porcentaje_senior'] ?? 0 }}%)</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Mensaje motivacional con información de tipos -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl shadow-lg p-6 text-white text-center transform hover:scale-105 transition-transform duration-200">
                <div class="mb-4">
                    @if(($myStats['total'] ?? 0) >= 50)
                        <div class="text-4xl mb-2">🎉</div>
                        <h2 class="text-2xl font-bold">¡Excelente trabajo!</h2>
                        <p class="text-blue-100 mt-1">Has superado las expectativas</p>
                    @elseif(($myStats['total'] ?? 0) >= 30)
                        <div class="text-4xl mb-2">🚀</div>
                        <h2 class="text-2xl font-bold">¡Vas muy bien!</h2>
                        <p class="text-blue-100 mt-1">Mantén ese ritmo excelente</p>
                    @elseif(($myStats['total'] ?? 0) >= 15)
                        <div class="text-4xl mb-2">💪</div>
                        <h2 class="text-2xl font-bold">¡Buen ritmo!</h2>
                        <p class="text-blue-100 mt-1">Estás en el camino correcto</p>
                    @elseif(($myStats['total'] ?? 0) >= 5)
                        <div class="text-4xl mb-2">📈</div>
                        <h2 class="text-2xl font-bold">¡Sigue así!</h2>
                        <p class="text-blue-100 mt-1">Cada ticket cuenta</p>
                    @elseif(($myStats['total'] ?? 0) > 0)
                        <div class="text-4xl mb-2">⭐</div>
                        <h2 class="text-2xl font-bold">¡Buen comienzo!</h2>
                        <p class="text-blue-100 mt-1">Vas por buen camino</p>
                    @else
                        <div class="text-4xl mb-2">🌟</div>
                        <h2 class="text-2xl font-bold">¡Es hora de brillar!</h2>
                        <p class="text-blue-100 mt-1">Comienza tu jornada de trabajo</p>
                    @endif
                </div>
                
                <div class="text-lg mb-4">
                    @if(($myStats['total'] ?? 0) > 0)
                        Has atendido <span class="font-bold text-yellow-300">{{ $myStats['total'] }}</span> 
                        ticket{{ ($myStats['total'] ?? 0) > 1 ? 's' : '' }} 
                        {{ strtolower($availablePeriods[$selectedPeriod]) }}
                    @else
                        Aún no has atendido tickets {{ strtolower($availablePeriods[$selectedPeriod]) }}
                    @endif
                </div>

                <!-- Desglose por tipos en el mensaje -->
                @if(($myStats['total'] ?? 0) > 0)
                    <div class="flex justify-center space-x-8 text-sm bg-white bg-opacity-20 rounded-lg p-4">
                        @if(($myStats['normal'] ?? 0) > 0)
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-300">{{ $myStats['normal'] }}</div>
                                <div class="text-blue-100">Normales</div>
                            </div>
                        @endif
                        
                        @if(($myStats['senior'] ?? 0) > 0)
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-300">{{ $myStats['senior'] }}</div>
                                <div class="text-blue-100">Tercera Edad</div>
                            </div>
                        @endif
                        
                        @if(($myStats['senior'] ?? 0) > 0)
                            <div class="text-center">
                                <div class="text-lg font-bold text-yellow-300">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="text-blue-100 text-xs">Atención Prioritaria</div>
                            </div>
                        @endif
                    </div>
                @endif
                
                <!-- Botón de acción -->
                @if(($myStats['total'] ?? 0) === 0)
                    <div class="mt-4">
                        <a href="{{ route('manage') }}" 
                           class="inline-block bg-yellow-400 hover:bg-yellow-500 text-gray-900 px-6 py-3 rounded-lg font-bold transition-colors duration-200">
                            ¡Comenzar a atender!
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>