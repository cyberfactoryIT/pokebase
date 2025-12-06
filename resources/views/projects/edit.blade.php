@extends('layouts.app')
@section('page_title', __('messages.Edit_Project'))
@section('content')
<div class="max-w-xl mx-auto">
    <form method="POST" action="{{ route('projects.update', $project) }}">
        @method('PUT')
        @include('projects._form', ['project' => $project, 'users' => $users])
            
    </form>
</div>
@endsection
