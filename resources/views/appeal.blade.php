<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appeal Your Ban — SiteSphere</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 50%, #f0fdf4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #1f2937;
        }

        .appeal-container {
            width: 100%;
            max-width: 620px;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.75rem;
            text-decoration: none;
        }

        .brand-link svg {
            width: 36px;
            height: 36px;
        }

        .brand-name {
            font-size: 1.375rem;
            font-weight: 700;
            color: #6c5ce7;
        }

        .appeal-card {
            background: #ffffff;
            border-radius: 1.25rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 30px -5px rgba(108, 92, 231, 0.1);
            padding: 2.5rem 2rem;
            border: 1px solid rgba(108, 92, 231, 0.08);
        }

        /* Header */
        .appeal-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .appeal-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #ede9fe, #f5f3ff);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.75rem;
        }

        .appeal-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .appeal-header p {
            font-size: 0.938rem;
            color: #6b7280;
            font-style: italic;
        }

        /* Ban reason box */
        .ban-reason-box {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            margin-bottom: 1.75rem;
        }

        .ban-reason-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.813rem;
            font-weight: 600;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .ban-reason-text {
            font-size: 0.938rem;
            color: #78350f;
            line-height: 1.6;
        }

        /* Textarea hover tooltip */
        .textarea-wrapper {
            position: relative;
        }

        .textarea-tooltip {
            position: absolute;
            top: -4px;
            right: -8px;
            transform: translateY(-100%);
            background: #1f2937;
            color: #ffffff;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            width: 280px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s, visibility 0.25s, transform 0.25s;
            transform: translateY(-100%) translateY(-8px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 10;
            pointer-events: none;
        }

        .textarea-tooltip::after {
            content: '';
            position: absolute;
            bottom: -6px;
            right: 24px;
            width: 12px;
            height: 12px;
            background: #1f2937;
            transform: rotate(45deg);
        }

        .textarea-wrapper:hover .textarea-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateY(-100%) translateY(-12px);
        }

        .tooltip-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #a5b4fc;
            margin-bottom: 0.625rem;
        }

        .tooltip-hint {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.813rem;
            color: #e5e7eb;
            line-height: 1.5;
            padding: 0.25rem 0;
        }

        .tooltip-hint span {
            flex-shrink: 0;
        }

        /* Form group */
        .form-group {
            margin-bottom: 0.75rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .appeal-textarea {
            width: 100%;
            min-height: 180px;
            padding: 1rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            font-family: inherit;
            font-size: 0.938rem;
            line-height: 1.7;
            color: #1f2937;
            background: #fafafa;
            resize: vertical;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        .appeal-textarea::placeholder {
            color: #9ca3af;
        }

        .appeal-textarea:focus {
            outline: none;
            border-color: #6c5ce7;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.15);
            background: #ffffff;
        }

        .char-counter {
            display: flex;
            justify-content: flex-end;
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: #9ca3af;
            transition: color 0.2s;
        }

        .char-counter.warning {
            color: #f59e0b;
        }

        .char-counter.danger {
            color: #ef4444;
        }

        /* Validation errors */
        .field-error {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            margin-top: 0.5rem;
            font-size: 0.813rem;
            color: #ef4444;
        }

        .field-error svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        /* Submit button */
        .submit-btn {
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #6c5ce7, #7c6cf7);
            color: #ffffff;
            border: none;
            border-radius: 0.75rem;
            font-family: inherit;
            font-size: 0.938rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(108, 92, 231, 0.3);
            margin-top: 1rem;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #5b4bd5, #6c5ce7);
            box-shadow: 0 4px 12px rgba(108, 92, 231, 0.4);
            transform: translateY(-1px);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .submit-btn svg {
            width: 18px;
            height: 18px;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 2rem 0 1.5rem;
            color: #9ca3af;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        /* What happens next — milestone timeline */
        .next-steps {
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .step {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            position: relative;
            padding-bottom: 1.25rem;
        }

        .step:last-child {
            padding-bottom: 0;
        }

        /* Vertical connector line */
        .step:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 9px;
            top: 20px;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #e5e7eb, #d1d5db);
        }

        .step-milestone {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }

        .step-milestone::after {
            content: '';
            position: absolute;
            inset: 4px;
            border-radius: 50%;
            background: #ffffff;
        }

        /* Step 1 — Purple (submitted) */
        .step:nth-child(1) .step-milestone {
            background: linear-gradient(135deg, #6c5ce7, #7c6cf7);
            box-shadow: 0 0 0 4px rgba(108, 92, 231, 0.15);
        }

        /* Step 2 — Amber (under review) */
        .step:nth-child(2) .step-milestone {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);
        }

        /* Step 3 — Green (resolved) */
        .step:nth-child(3) .step-milestone {
            background: linear-gradient(135deg, #10b981, #34d399);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
        }

        .step-content {
            padding-top: 0;
        }

        .step-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.15rem;
        }

        .step-desc {
            font-size: 0.813rem;
            color: #6b7280;
            line-height: 1.5;
        }

        /* Rate limit notice */
        .rate-limit-notice {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
            padding: 0.75rem 1rem;
            background: #f9fafb;
            border-radius: 0.5rem;
            font-size: 0.813rem;
            color: #6b7280;
        }

        .rate-limit-notice svg {
            width: 16px;
            height: 16px;
            color: #9ca3af;
            flex-shrink: 0;
        }

        /* Success overlay */
        .success-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            z-index: 50;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .success-overlay.visible {
            display: flex;
        }

        .success-card {
            background: #ffffff;
            border-radius: 1.25rem;
            padding: 2.5rem 2rem;
            text-align: center;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes popIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .success-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #d1fae5, #ecfdf5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 2rem;
        }

        .success-card h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .success-card p {
            font-size: 0.938rem;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .success-close-btn {
            padding: 0.75rem 2rem;
            background: #6c5ce7;
            color: #ffffff;
            border: none;
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .success-close-btn:hover {
            background: #5b4bd5;
        }

        /* Responsive */
        @media (max-width: 640px) {
            body {
                padding: 1rem;
                align-items: flex-start;
            }

            .appeal-card {
                padding: 1.75rem 1.25rem;
            }

            .appeal-header h1 {
                font-size: 1.25rem;
            }

            .appeal-textarea {
                min-height: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="appeal-container">
        <!-- Appeal Card -->
        <div class="appeal-card">
            <!-- Brand -->
            <a href="{{ route('welcome') }}" class="brand-link">
                <x-app-logo />
                <span class="brand-name">SiteSphere</span>
            </a>

            <div class="appeal-header">
                <div class="appeal-icon">✉️</div>
                <h1>We'd love to hear from you</h1>
                <p>"Everyone deserves a second chance"</p>
            </div>

            <!-- Ban Reason -->
            @if ($user->ban_reason)
                <div class="ban-reason-box">
                    <div class="ban-reason-label">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                        </svg>
                        Reason for ban
                    </div>
                    <div class="ban-reason-text">{{ $user->ban_reason }}</div>
                </div>
            @endif

            @if ($user->appeal_submitted_at && $user->appeal_submitted_at->diffInHours(now()) < 24)
                <!-- Already submitted — under review -->
                <div style="text-align:center; padding: 2rem 0;">
                    <div style="width:64px;height:64px;background:linear-gradient(135deg,#dbeafe,#eff6ff);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.75rem;">⏳</div>
                    <h2 style="font-size:1.25rem;font-weight:700;color:#1f2937;margin-bottom:0.5rem;">Appeal Under Review</h2>
                    <p style="font-size:0.938rem;color:#6b7280;line-height:1.6;margin-bottom:1rem;">
                        Your appeal has been submitted and is being reviewed by our team.
                        You will receive an email with our decision within 24–48 hours.
                    </p>
                    <p style="font-size:0.813rem;color:#9ca3af;">
                        You can submit a new appeal after 24 hours if needed.
                    </p>
                </div>
            @else
                <form method="POST" action="{{ route('appeal.store') }}" id="appealForm">
                    @csrf

                    <!-- Textarea -->
                    <div class="form-group">
                        <label for="reason">Tell us your side of the story</label>
                        <div class="textarea-wrapper">
                            <div class="textarea-tooltip">
                                <div class="tooltip-title">Tips for an effective appeal</div>
                                <div class="tooltip-hint">- Explain what happened from your perspective</div>
                                <div class="tooltip-hint">- Acknowledge the situation and show understanding</div>
                                <div class="tooltip-hint">- Share what you'll do differently going forward</div>
                                <div class="tooltip-hint">- Be honest and sincere — we read every appeal</div>
                            </div>
                            <textarea
                                class="appeal-textarea"
                                id="reason"
                                name="reason"
                                placeholder="What happened? Why should you be unbanned? What will you do differently?"
                                maxlength="2000"
                                required
                            >{{ old('reason') }}</textarea>
                        </div>
                        <div class="char-counter" id="charCounter">
                            <span id="charCount">0</span> / 2000 characters
                        </div>
                        @error('reason')
                            <div class="field-error">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path d="M3.105 2.289a.75.75 0 00-.826.95l1.414 4.925A1.5 1.5 0 005.135 9.25h6.115a.75.75 0 010 1.5H5.135a1.5 1.5 0 00-1.442 1.086l-1.414 4.926a.75.75 0 00.826.95 28.896 28.896 0 0015.293-7.154.75.75 0 000-1.115A28.897 28.897 0 003.105 2.289z"/>
                        </svg>
                        Send My Appeal
                    </button>
                </form>
            @endif

            <!-- Divider -->
            <div class="divider">What happens next?</div>

            <!-- Next Steps — Milestone Timeline -->
            <div class="next-steps">
                <div class="step">
                    <div class="step-milestone"></div>
                    <div class="step-content">
                        <div class="step-title">Appeal Received</div>
                        <div class="step-desc">Your appeal is submitted and queued for review</div>
                    </div>
                </div>
                <div class="step">
                    <div class="step-milestone"></div>
                    <div class="step-content">
                        <div class="step-title">Under Review</div>
                        <div class="step-desc">Our team carefully reviews your appeal within 24–48 hours</div>
                    </div>
                </div>
                <div class="step">
                    <div class="step-milestone"></div>
                    <div class="step-content">
                        <div class="step-title">Decision Sent</div>
                        <div class="step-desc">You'll receive an email with our decision — if approved, your access is restored</div>
                    </div>
                </div>
            </div>

            <!-- Rate limit notice -->
            <div class="rate-limit-notice">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd"/>
                </svg>
                You can submit one appeal every 24 hours
            </div>
        </div>
    </div>

    <!-- Success Overlay -->
    @if (session('appeal_submitted'))
        <div class="success-overlay visible" id="successOverlay">
            <div class="success-card">
                <div class="success-icon">💌</div>
                <h2>Appeal Sent Successfully</h2>
                <p>Thank you for sharing your side. Our team will review your appeal and get back to you via email within 24–48 hours.</p>
                <button class="success-close-btn" onclick="document.getElementById('successOverlay').classList.remove('visible')">
                    Got it, thanks
                </button>
            </div>
        </div>
    @endif

    <script>
        // Character counter
        const textarea = document.getElementById('reason');
        const charCount = document.getElementById('charCount');
        const charCounter = document.getElementById('charCounter');

        if (textarea) {
            const updateCounter = () => {
                const count = textarea.value.length;
                charCount.textContent = count;

                charCounter.classList.remove('warning', 'danger');
                if (count > 1800) {
                    charCounter.classList.add('danger');
                } else if (count > 1500) {
                    charCounter.classList.add('warning');
                }
            };

            textarea.addEventListener('input', updateCounter);
            updateCounter();
        }
    </script>
</body>
</html>
