@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-lg shadow p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Organizations</h2>
                <form method="GET" class="flex gap-2">
                    <input name="search" value="{{ $search }}" placeholder="Search name, code, email..." class="px-3 py-2 rounded-lg border border-gray-300 shadow-sm bg-white text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition" />
                    <x-button type="submit" icon="search" variant="primary">Search</x-button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Code</th>
                            <th class="px-4 py-2 text-left">Current Plan</th>
                            <th class="px-4 py-2 text-left">Billing Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($organizations as $org)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium text-gray-800">{{ $org->name }}</td>
                                <td class="px-4 py-2 text-gray-700">{{ $org->code }}</td>
                                <td class="px-4 py-2">
                                    @if($org->pricingPlan)
                                        <x-badge>{{ $org->pricingPlan->code }}</x-badge>
                                    @else
                                        <x-badge variant="neutral">None</x-badge>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-gray-700">{{ $org->admin_email }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-right text-sm text-gray-500">
                                Showing {{ $organizations->count() }} of {{ $organizations->total() }} organizations
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="mt-4">
                {{ $organizations->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
