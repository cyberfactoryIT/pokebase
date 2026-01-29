@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-[#161615] border border-white/15 shadow-xl rounded-2xl overflow-hidden">
            <div class="p-6 text-gray-100">
                
                @if($sets->isEmpty())
                    <p class="text-gray-500">No sets found.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($sets as $set)
                            <a href="{{ route('pokemon.set.cards', $set->tcgdex_id) }}" 
                               class="block bg-[#1a1a19] border border-white/20 rounded-lg p-4 hover:shadow-xl hover:border-white/40 transition-all">
                                
                                @if($set->logo_url)
                                    <img src="{{ $set->logo_url }}/high.webp" 
                                         alt="{{ $set->name_en }}"
                                         class="w-full h-32 object-contain mb-4"
                                         onerror="this.onerror=null; this.src='{{ $set->logo_url }}.webp';">
                                @endif
                                
                                <h3 class="font-semibold text-lg mb-2 text-white">
                                    {{ $set->name_en }}
                                </h3>
                                
                                <div class="text-sm text-gray-400">
                                    <p>Series: {{ $set->series_name['en'] ?? 'N/A' }}</p>
                                    <p>Cards: {{ $set->card_count_total ?? 0 }}</p>
                                    @if($set->released_at)
                                        <p>Released: {{ $set->released_at->format('Y-m-d') }}</p>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                    
                    <div class="mt-6">
                        {{ $sets->links() }}
                    </div>
                @endif
                
            </div>
        </div>
    </div>
</div>
@endsection
