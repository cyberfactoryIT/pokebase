@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-lg shadow p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Plans</h2>
                <a href="{{ route('superadmin.plans.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                    <i class="fa fa-plus"></i>
                    <span>New Plan</span>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Code</th>
                            <th class="px-4 py-2 text-right">Monthly</th>
                            <th class="px-4 py-2 text-right">Yearly</th>
                            <th class="px-4 py-2 text-left">Currency</th>
                            <th class="px-4 py-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans as $plan)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium text-gray-800">{{ $plan->name }}</td>
                                <td class="px-4 py-2 text-gray-700">{{ $plan->code }}</td>
                                <td class="px-4 py-2 text-right"><x-money :cents="$plan->monthly_price_cents" :currency="$plan->currency" /></td>
                                <td class="px-4 py-2 text-right"><x-money :cents="$plan->yearly_price_cents" :currency="$plan->currency" /></td>
                                <td class="px-4 py-2 text-gray-700">{{ $plan->currency }}</td>
                                <td class="px-4 py-2 text-center flex gap-2 justify-center">
                                    <a href="{{ route('superadmin.plans.edit', $plan) }}" class="inline-flex items-center px-2 py-1 bg-gray-100 rounded hover:bg-yellow-100 text-yellow-600 transition" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('superadmin.plans.destroy', $plan) }}" onsubmit="return confirm('Delete this plan?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2 py-1 bg-gray-100 rounded hover:bg-red-100 text-red-600 transition" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="6" class="px-4 py-2 text-right text-sm text-gray-500">
                                Showing {{ $plans->count() }} of {{ $plans->total() }} plans
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="mt-4">
                {{ $plans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
