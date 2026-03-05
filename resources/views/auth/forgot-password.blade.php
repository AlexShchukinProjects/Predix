<x-guest-layout>
    <div class="mb-4 text-center">
        <h4>{{ __('auth.Forgot your password?') }}</h4>
        <p class="text-muted">{{ __('auth.No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success mb-3" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('auth.Email') }}</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                   name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <a class="text-decoration-none" href="{{ route('login') }}">
                {{ __('auth.Back to login') }}
            </a>

            <button type="submit" class="btn btn-primary">
                {{ __('auth.Email Password Reset Link') }}
            </button>
        </div>
    </form>
</x-guest-layout>
