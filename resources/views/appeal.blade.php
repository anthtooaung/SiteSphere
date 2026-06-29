@extends('index')

@section('title')
    Submit Appeal - SiteSphere
@endsection

@push('styles')
    @vite(['resources/css/auth.css'])
@endpush

@section('content')
    <main class="auth-page">
        <section class="auth-shell" aria-label="SiteSphere ban appeal">
            <a href="{{ route('welcome') }}" class="brand-title" aria-label="SiteSphere">
                <x-app-logo />
                <span class="brand-word" aria-hidden="true">
                    <span class="brand-sphere">SiteSphere</span>
                </span>
            </a>

            <div class="form-stage">
                <section class="form-panel" aria-labelledby="appeal-title">
                    @if (session('appeal_submitted'))
                        {{-- Success State --}}
                        <div style="text-align: center; padding: 40px 20px;">
                            <div style="width: 64px; height: 64px; margin: 0 auto 20px; background: linear-gradient(135deg, #48bb78, #38a169); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <svg width="32" height="32" fill="white" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <h1 id="appeal-title" style="font-size: 24px; font-weight: 700; color: var(--text-color); margin-bottom: 12px;">
                                Appeal Submitted
                            </h1>
                            <p style="font-size: 15px; color: color-mix(in srgb, var(--text-color) 70%, transparent); line-height: 1.6; margin-bottom: 24px;">
                                Your appeal has been sent to the admin team. They will review your request and get back to you within <strong>24 hours</strong>.
                            </p>
                            <p style="font-size: 14px; color: color-mix(in srgb, var(--text-color) 55%, transparent); line-height: 1.6;">
                                You will receive an email notification once your account has been reviewed.
                            </p>
                            <div style="margin-top: 32px;">
                                <a href="{{ route('welcome') }}" style="display: inline-block; padding: 12px 28px; background: linear-gradient(135deg, #6c5ce7, #00cec9); color: #fff; text-decoration: none; border-radius: 10px; font-size: 14px; font-weight: 600;">
                                    Back to Home
                                </a>
                            </div>
                        </div>
                    @else
                        {{-- Appeal Form --}}
                        <form method="POST" action="{{ route('appeal.store') }}" class="auth-form">
                            @csrf
                            <div class="form-heading">
                                <p class="section-label" style="color: #e53e3e;">Account Restricted</p>
                                <h1 id="appeal-title">Submit Appeal</h1>
                                <p>Your account has been banned. If you believe this was a mistake, you can submit an appeal below.</p>
                            </div>

                            {{-- Ban Info --}}
                            <div style="margin-bottom: 20px; padding: 16px; border-radius: 10px; background: #fff5f5; border: 1px solid #fed7d7;">
                                <p style="margin: 0 0 8px; font-size: 13px; font-weight: 600; color: #742a2a;">Ban Details</p>
                                @if ($user->ban_reason)
                                    <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.6;">
                                        <strong>Reason:</strong> {{ $user->ban_reason }}
                                    </p>
                                @endif
                                @if ($user->banned_at)
                                    <p style="margin: 8px 0 0; font-size: 13px; color: #94a3b8;">
                                        Banned on {{ $user->banned_at->format('M d, Y') }}
                                    </p>
                                @endif
                            </div>

                            {{-- Appeal Reason --}}
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label for="reason" style="display: block; font-size: 14px; font-weight: 600; color: var(--text-color); margin-bottom: 8px;">
                                    Your Appeal Statement
                                </label>
                                <textarea
                                    id="reason"
                                    name="reason"
                                    rows="6"
                                    placeholder="Explain why you believe the ban should be lifted. Be honest and provide any relevant context..."
                                    style="width: 100%; padding: 14px; border-radius: 10px; border: 1px solid var(--ui-border, #e2e8f0); background: var(--ui-surface, #fff); color: var(--text-color); font-size: 14px; line-height: 1.6; resize: vertical; outline: none; transition: border-color 0.2s;"
                                    onfocus="this.style.borderColor='var(--accent-color, #6c5ce7)'"
                                    onblur="this.style.borderColor='var(--ui-border, #e2e8f0)'"
                                    required
                                    minlength="20"
                                    maxlength="2000"
                                >{{ old('reason') }}</textarea>
                                @error('reason')
                                    <p style="margin-top: 6px; font-size: 13px; color: #e53e3e;">{{ $message }}</p>
                                @enderror
                                <p style="margin-top: 6px; font-size: 12px; color: #94a3b8;">Minimum 20 characters. Be specific about your situation.</p>
                            </div>

                            {{-- Submit --}}
                            <button type="submit" class="auth-submit" style="width: 100%; padding: 14px; background: linear-gradient(135deg, #6c5ce7, #00cec9); color: #fff; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; transition: opacity 0.2s;">
                                Submit Appeal
                            </button>

                            <p style="margin-top: 16px; text-align: center; font-size: 13px; color: #94a3b8;">
                                You can submit one appeal every 24 hours.
                            </p>
                        </form>
                    @endif
                </section>
            </div>
        </section>
    </main>
@endsection
