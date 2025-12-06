
@csrf
<x-card>
    <div class="space-y-6">
        <div class="relative">
            <x-input-label for="name">
                {{ __('messages.name') }} <span class="text-red-500" title="{{ __('messages.required') }}">* {{ __('messages.required') }}</span>
            </x-input-label>
            <div class="flex items-center mt-1">
                <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-500">
                    <i class="fa fa-briefcase"></i>
                </span>
                <x-input id="name" name="name" type="text" class="w-full rounded-l-none" :value="old('name', $project->name ?? '')" required />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="relative">
            <x-input-label for="code">
                {{ __('messages.code') }} <span class="text-red-500" title="{{ __('messages.required') }}">* {{ __('messages.required') }}</span>
            </x-input-label>
            <div class="flex items-center mt-1">
                <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-500">
                    <i class="fa fa-hashtag"></i>
                </span>
                <x-input id="code" name="code" type="text" class="w-full rounded-l-none" :value="old('code', $project->code ?? '')" required />
            </div>
            <small>{{ __('messages.projects.unique_code') }}</small>
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="relative">
            <x-input-label for="description" :value="__('messages.description')" />
            <div class="flex items-center mt-1">
                <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-500">
                    <i class="fa fa-align-left"></i>
                </span>
                <x-textarea id="description" name="description" class="w-full rounded-l-none">{{ old('description', $project->description ?? '') }}</x-textarea>
            </div>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="flex gap-4 items-center">
            <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $project->is_active ?? true)) /> <span>{{ __('messages.active') }}</span></label>
            <!--label class="flex items-center gap-2"><input type="checkbox" name="billable" value="1" @checked(old('billable', $project->billable ?? true)) /> <span>Billable</span></label-->
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="relative">
                <x-input-label for="starts_at" :value="__('messages.start_date')" />
                <div class="flex items-center mt-1">
                    <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-500">
                        <i class="fa fa-calendar"></i>
                    </span>
                    <x-input id="starts_at" name="starts_at" type="date" class="w-full rounded-l-none" :value="old('starts_at', optional($project->starts_at ?? null)->format('Y-m-d'))" />
                </div>
                <x-input-error :messages="$errors->get('starts_at')" class="mt-2" />
            </div>
            <div class="relative">
                <x-input-label for="ends_at" :value="__('messages.end_date')" />
                <div class="flex items-center mt-1">
                    <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-500">
                        <i class="fa fa-calendar"></i>
                    </span>
                    <x-input id="ends_at" name="ends_at" type="date" class="w-full rounded-l-none" :value="old('ends_at', optional($project->ends_at ?? null)->format('Y-m-d'))" />
                </div>
                <x-input-error :messages="$errors->get('ends_at')" class="mt-2" />
            </div>
        </div>

        <div class="relative">
            <x-input-label for="responsible_user_id">
                {{ __('messages.responsible') }} <span class="text-red-500" title="{{ __('messages.required') }}">* {{ __('messages.required') }}</span>
            </x-input-label>
            <div class="flex items-center mt-1">
                <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-gray-500">
                    <i class="fa fa-user"></i>
                </span>
                <select id="responsible_user_id" name="responsible_user_id" class="w-full rounded-l-none" required>
                    <option value="">Select user</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('responsible_user_id', $project->responsible_user_id ?? '') == $user->id)>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <x-input-error :messages="$errors->get('responsible_user_id')" class="mt-2" />
        </div>

        
        <div class="flex justify-end gap-2">
            <x-button as="a" href="{{ route('projects.index') }}" variant="neutral" icon="arrow-left">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="save">{{ __('messages.Save') }}</x-button>
        </div>
        
    </div>
</x-card>
