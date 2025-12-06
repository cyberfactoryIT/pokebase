@extends('layouts.app')

@section('page_title', __('faq.faq'))

@section('content')
<div class="max-w-5xl mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ __('faq.faq') }}</h1>
        <a href="{{ route('faqs.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded shadow">{{ __('faq.add_faq') }}</a>
    </div>
    <table class="min-w-full bg-white rounded shadow">
        <thead>
            <tr>
                <th class="px-4 py-2">{{ __('faq.category') }}</th>
                <th class="px-4 py-2">{{ __('faq.question') }}</th>
                <th class="px-4 py-2">{{ __('faq.status') }}</th>
                <th class="px-4 py-2">{{ __('faq.sort_order') }}</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody id="faq-table-body">
            @foreach($faqs as $faq)
                <tr data-id="{{ $faq->id }}">
                    <td class="px-4 py-2">{{ $faq->category }}</td>
                    <td class="px-4 py-2">{{ $faq->question[app()->getLocale()] ?? reset($faq->question) }}</td>
                    <td class="px-4 py-2">
                        <button onclick="togglePublish({{ $faq->id }})" class="px-2 py-1 rounded {{ $faq->is_published ? 'bg-green-500 text-white' : 'bg-gray-300' }}">
                            {{ $faq->is_published ? 'Published' : 'Draft' }}
                        </button>
                    </td>
                    <td class="px-4 py-2 drag-handle cursor-move">{{ $faq->sort_order }}</td>
                    <td class="px-4 py-2 flex gap-2">
                        <a href="{{ route('faqs.edit', $faq) }}" class="text-blue-600">Edit</a>
                        <form method="POST" action="{{ route('faqs.destroy', $faq) }}" onsubmit="return confirm('Delete?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<script>
// Minimal drag-and-drop reorder
let dragged;
document.querySelectorAll('.drag-handle').forEach(handle => {
    handle.addEventListener('dragstart', e => {
        dragged = e.target.parentElement;
    });
    handle.parentElement.setAttribute('draggable', true);
    handle.parentElement.addEventListener('dragover', e => e.preventDefault());
    handle.parentElement.addEventListener('drop', e => {
        e.preventDefault();
        if (dragged && dragged !== e.target.parentElement) {
            e.target.parentElement.parentElement.insertBefore(dragged, e.target.parentElement);
            updateOrder();
        }
    });
});
function updateOrder() {
    const order = Array.from(document.querySelectorAll('#faq-table-body tr')).map((row, i) => ({id: row.dataset.id, sort_order: i+1}));
    fetch('{{ route('faqs.reorder') }}', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({order})
    });
}
function togglePublish(id) {
    fetch(`/faqs/${id}/toggle`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
    }).then(r => r.json()).then(data => location.reload());
}
</script>
@endsection
