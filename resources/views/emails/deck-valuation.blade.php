<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Pokémon Deck Valuation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2563eb;
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .emoji {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .content p {
            margin: 15px 0;
            font-size: 16px;
        }
        .deck-name {
            background-color: #f0f9ff;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #2563eb;
            margin: 20px 0;
        }
        .deck-name strong {
            color: #1e40af;
            font-size: 18px;
        }
        .cta-button {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff !important;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
            font-size: 16px;
        }
        .cta-button:hover {
            background-color: #1d4ed8;
        }
        .link-box {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            word-break: break-all;
            font-size: 14px;
            color: #6b7280;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .tip {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .tip-title {
            font-weight: 600;
            color: #92400e;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="emoji">🎴✨</div>
            <h1>{{ __('deckvaluation.email_title') }}</h1>
        </div>

        <div class="content">
            <p>{{ __('deckvaluation.email_greeting') }}</p>
            
            <p>{{ __('deckvaluation.email_intro') }}</p>

            <div class="deck-name">
                <strong>{{ $deckName }}</strong>
            </div>

            <p>{{ __('deckvaluation.email_cta_text') }}</p>

            <div style="text-align: center;">
                <a href="{{ route('pokemon.deck-valuation.step3', $guestDeck->uuid) }}" class="cta-button">
                    {{ __('deckvaluation.email_button') }}
                </a>
            </div>

            <p style="font-size: 14px; color: #6b7280;">
                {{ __('deckvaluation.email_copy_link') }}
            </p>
            <div class="link-box">
                {{ route('pokemon.deck-valuation.step3', $guestDeck->uuid) }}
            </div>

            <div class="tip">
                <div class="tip-title">{{ __('deckvaluation.email_tip_title') }}</div>
                {!! __('deckvaluation.email_tip_text') !!}
            </div>
        </div>

        <div class="footer">
            <p><strong>{{ config('app.name') }}</strong></p>
            <p>{{ __('deckvaluation.email_footer_tagline') }}</p>
            <p style="font-size: 12px; margin-top: 15px;">
                {{ __('deckvaluation.email_footer_note') }}
            </p>
        </div>
    </div>
</body>
</html>
