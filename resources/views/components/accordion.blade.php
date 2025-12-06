
@props(['items'])
<div class="space-y-4">
    @foreach($items as $faq)
        <div x-data="{ open: false }" class="border border-gray-200 rounded-xl bg-white shadow-lg transition-all duration-200">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 text-left font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-200">
                <span><i class="fa fa-question-circle mr-2 text-blue-500"></i>{{ $faq->question }}</span>
                <i :class="open ? 'fa fa-chevron-up' : 'fa fa-chevron-down'" class="fa text-gray-400"></i>
            </button>
            <div x-show="open" x-transition class="px-6 pb-4 text-gray-700">
                {!! nl2br(e($faq->answer)) !!}
            </div>
        </div>
    @endforeach
</div>
