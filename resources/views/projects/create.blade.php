@extends('layouts.app')
@section('page_title', __('messages.Create_Project'))
@section('content')
<div class="max-w-xl mx-auto">
    <form method="POST" action="{{ route('projects.store') }}">
        @include('projects._form', ['project' => null, 'users' => $users])
        
    </form>
</div>
@endsection
