@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('tcgdex.sets.show', $card->set->tcgdex_id) }}" class="inline-flex items-center text-blue-400 hover:text-blue-300">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to {{ $card->set->getLocalizedName() }}
            </a>
        </div>

        <!-- Card Detail Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column: Card Image -->
            <div class="space-y-6">
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <div class="aspect-[245/342] max-w-md mx-auto">
                        @php
                            // TCGdex high quality: add /high.webp to URL
                            $hdImageUrl = $card->image_large_url ? $card->image_large_url . '/high.webp' : null;
                            $fallbackUrl = $card->image_large_url ?? $card->image_small_url;
                        @endphp
                        @if($hdImageUrl)
                            <img 
                                src="{{ $hdImageUrl }}" 
                                alt="{{ $card->getLocalizedName() }}"
                                class="w-full h-full object-contain rounded-lg shadow-lg"
                                onerror="this.src='{{ $fallbackUrl ?? "https://via.placeholder.com/490x684/1a1a19/666?text=No+Image" }}'"
                            >
                        @elseif($fallbackUrl)
                            <img 
                                src="{{ $fallbackUrl }}" 
                                alt="{{ $card->getLocalizedName() }}"
                                class="w-full h-full object-contain rounded-lg shadow-lg"
                            >
                        @else
                            <div class="w-full h-full bg-black/50 rounded-lg flex items-center justify-center">
                                <svg class="w-24 h-24 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Basic Details -->
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Card Details</h2>
                    
                    <dl class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-white/10">
                            <dt class="text-sm font-medium text-gray-400">Set</dt>
                            <dd class="text-sm text-white">{{ $card->set->getLocalizedName() }}</dd>
                        </div>
                        
                        @if($card->local_id)
                        <div class="flex justify-between py-2 border-b border-white/10">
                            <dt class="text-sm font-medium text-gray-400">Number</dt>
                            <dd class="text-sm text-white">#{{ $card->local_id }}</dd>
                        </div>
                        @endif
                        
                        @if($card->rarity)
                        <div class="flex justify-between py-2 border-b border-white/10">
                            <dt class="text-sm font-medium text-gray-400">Rarity</dt>
                            <dd class="text-sm text-white">{{ $card->rarity }}</dd>
                        </div>
                        @endif
                        
                        @if($card->supertype)
                        <div class="flex justify-between py-2 border-b border-white/10">
                            <dt class="text-sm font-medium text-gray-400">Supertype</dt>
                            <dd class="text-sm text-white">{{ $card->supertype }}</dd>
                        </div>
                        @endif
                        
                        @if($card->hp)
                        <div class="flex justify-between py-2 border-b border-white/10">
                            <dt class="text-sm font-medium text-gray-400">HP</dt>
                            <dd class="text-sm text-white">{{ $card->hp }}</dd>
                        </div>
                        @endif
                        
                        @if($card->types && count($card->types) > 0)
                        <div class="flex justify-between py-2 border-b border-white/10">
                            <dt class="text-sm font-medium text-gray-400">Types</dt>
                            <dd class="text-sm text-white">{{ implode(', ', $card->types) }}</dd>
                        </div>
                        @endif
                        
                        @if($card->evolves_from)
                        <div class="flex justify-between py-2 border-b border-white/10">
                            <dt class="text-sm font-medium text-gray-400">Evolves From</dt>
                            <dd class="text-sm text-white">{{ $card->evolves_from }}</dd>
                        </div>
                        @endif
                        
                        @if($card->illustrator)
                        <div class="flex justify-between py-2 border-b border-white/10">
                            <dt class="text-sm font-medium text-gray-400">Illustrator</dt>
                            <dd class="text-sm text-white">{{ $card->illustrator }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Right Column: Extended Details -->
            <div class="space-y-6">
                <!-- Card Header -->
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h1 class="text-3xl font-bold text-white mb-2">{{ $card->getLocalizedName() }}</h1>
                    
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-300 mb-4">
                        <a href="{{ route('tcgdex.sets.show', $card->set->tcgdex_id) }}" class="inline-flex items-center hover:text-blue-400">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            {{ $card->set->getLocalizedName() }}
                        </a>
                        
                        @if($card->local_id)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/10 border border-white/20 text-gray-200">
                                #{{ $card->local_id }}
                            </span>
                        @endif

                        @if($card->hp)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/20 border border-red-400/30 text-red-300">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $card->hp }} HP
                            </span>
                        @endif

                        @if($card->supertype)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 border border-blue-400/30 text-blue-300">
                                {{ $card->supertype }}
                            </span>
                        @endif
                        
                        @if($card->rarity)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-500/20 border border-purple-400/30 text-purple-300">
                                {{ $card->rarity }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Abilities -->
                @if(isset($card->raw['abilities']) && count($card->raw['abilities']) > 0)
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Abilities</h2>
                    @foreach($card->raw['abilities'] as $ability)
                    <div class="mb-4 last:mb-0 p-4 bg-black/40 rounded-lg border border-white/10">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="font-bold text-blue-400">{{ $ability['name'] ?? 'Ability' }}</span>
                            @if(isset($ability['type']))
                                <span class="text-xs px-2 py-0.5 bg-blue-500/20 rounded text-blue-300">{{ $ability['type'] }}</span>
                            @endif
                        </div>
                        @if(isset($ability['effect']))
                            <p class="text-sm text-gray-300">{{ $ability['effect'] }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Attacks -->
                @if(isset($card->raw['attacks']) && count($card->raw['attacks']) > 0)
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Attacks</h2>
                    @foreach($card->raw['attacks'] as $attack)
                    <div class="mb-4 last:mb-0 p-4 bg-black/40 rounded-lg border border-white/10">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                @if(isset($attack['cost']) && is_array($attack['cost']))
                                    <div class="flex gap-1">
                                        @foreach($attack['cost'] as $energy)
                                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold bg-gradient-to-br from-gray-600 to-gray-800 text-white border border-white/20">
                                                {{ substr($energy, 0, 1) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                <span class="font-bold text-white">{{ $attack['name'] ?? 'Attack' }}</span>
                            </div>
                            @if(isset($attack['damage']))
                                <span class="text-xl font-bold text-red-400">{{ $attack['damage'] }}</span>
                            @endif
                        </div>
                        @if(isset($attack['effect']))
                            <p class="text-sm text-gray-300">{{ $attack['effect'] }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Weaknesses, Resistances, Retreat -->
                @if(isset($card->raw['weaknesses']) || isset($card->raw['resistances']) || isset($card->raw['retreat']))
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Combat Stats</h2>
                    
                    @if(isset($card->raw['weaknesses']))
                    <div class="mb-3">
                        <div class="text-sm font-medium text-gray-400 mb-2">Weaknesses</div>
                        <div class="flex gap-2 flex-wrap">
                            @foreach($card->raw['weaknesses'] as $weakness)
                                <span class="px-3 py-1 bg-red-500/20 border border-red-400/30 text-red-300 rounded-lg text-sm">
                                    {{ $weakness['type'] ?? 'Unknown' }}
                                    @if(isset($weakness['value']))
                                        {{ $weakness['value'] }}
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if(isset($card->raw['resistances']))
                    <div class="mb-3">
                        <div class="text-sm font-medium text-gray-400 mb-2">Resistances</div>
                        <div class="flex gap-2 flex-wrap">
                            @foreach($card->raw['resistances'] as $resistance)
                                <span class="px-3 py-1 bg-green-500/20 border border-green-400/30 text-green-300 rounded-lg text-sm">
                                    {{ $resistance['type'] ?? 'Unknown' }}
                                    @if(isset($resistance['value']))
                                        {{ $resistance['value'] }}
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if(isset($card->raw['retreat']))
                    <div>
                        <div class="text-sm font-medium text-gray-400 mb-2">Retreat Cost</div>
                        <div class="flex gap-1">
                            @for($i = 0; $i < (int)$card->raw['retreat']; $i++)
                                <span class="w-6 h-6 rounded-full bg-gradient-to-br from-gray-600 to-gray-800 border border-white/20"></span>
                            @endfor
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Legal Formats -->
                @if(isset($card->raw['legal']) && is_array($card->raw['legal']))
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Legal Formats</h2>
                    <div class="flex gap-2 flex-wrap">
                        @foreach($card->raw['legal'] as $format => $isLegal)
                            @if($isLegal)
                                <span class="px-3 py-1 bg-green-500/20 border border-green-400/30 text-green-300 rounded-lg text-sm">
                                    {{ ucfirst($format) }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
