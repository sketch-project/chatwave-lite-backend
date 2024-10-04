@extends('layouts.auth')

@section('title', 'Email Verification')

@section('content')
    <main class="container">
        <div class="bg-body-tertiary p-5 rounded">
            <h2><i class="mdi mdi-mailbox-open-outline me-2"></i> Email Verification Needed</h2>
            @if (url()->previous() != url()->current())
                <a href="{{ url()->previous() }}">{{ url()->previous() }}</a>
            @endif
            <p class="lead">In order to continue, you need to confirm your email address.</p>
            @if (session('message'))
                <div class="alert alert-warning alert-dismissible fade show">
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <form action="{{ route('verification.send') }}" method="post" class="need-validation">
                @csrf
                <a href="{{ url()->previous() != url()->current() ? url()->previous() : route('/') }}" class="btn me-3">
                    Go Back
                </a>
                <button type="submit" class="btn btn-primary" data-toggle="one-touch">Resend Email</button>
            </form>
        </div>
    </main>
@endsection
