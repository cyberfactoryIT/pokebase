<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <title>Handelsbetingelser – {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-black text-white min-h-screen flex flex-col items-center p-6 lg:p-10">
    <main class="w-full max-w-3xl bg-[#161615] border border-white/10 rounded-3xl p-6 md:p-10 shadow-xl">
        <h1 class="text-3xl md:text-4xl font-bold mb-8">
            Handelsbetingelser for basecard.dk
        </h1>

        <section class="mb-8">
            <h2 class="text-xl font-semibold mb-3">1. Parter, hvem vi er</h2>
            <p class="mb-2 text-gray-200">Sælger: <strong>Basios ApS</strong></p>
            <p class="mb-2 text-gray-200">CVR: <strong>46023021</strong></p>
            <p class="mb-2 text-gray-200">Adresse: <strong>Støberigården 11, 2. 1, 7500 Holstebro, Danmark</strong></p>
            <p class="mb-2 text-gray-200">E-mail: <strong><a href="mailto:info@basecard.dk" class="text-blue-400 hover:text-blue-300">info@basecard.dk</a></strong></p>
            <p class="mb-4 text-gray-200">Hjemmeside: <strong>basecard.dk</strong></p>
            <p class="text-gray-200">Når du handler på basecard.dk, indgår du en bindende aftale med Basios ApS.</p>
        </section>

        <section class="mb-8">
            <h2 class="text-xl font-semibold mb-3">2. Betaling</h2>
            <p class="mb-3 text-gray-200">Vi accepterer følgende betalingsmetoder:</p>
            <ul class="list-disc list-inside mb-4 text-gray-200 space-y-1">
                <li>Vipps MobilePay</li>
                <li>Betalingskort (Visa og Mastercard)</li>
            </ul>
            <p class="mb-4 text-gray-200">Alle betalinger gennemføres i danske kroner (DKK).</p>
            
            <h3 class="text-lg font-semibold mb-2 mt-6">Priser og moms</h3>
            <p class="mb-4 text-gray-200">Alle priser er angivet i DKK og inkluderer moms, medmindre andet er tydeligt angivet.</p>
            
            <h3 class="text-lg font-semibold mb-2 mt-6">Aktuelle priser og abonnementsniveauer</h3>
            <p class="mb-2 text-gray-200">Gældende priser, abonnementsniveauer og betalingsintervaller fremgår af:</p>
            <p class="mb-4 text-gray-200">
                <a href="https://app.basecard.dk/pricing" class="text-blue-400 hover:text-blue-300 underline" target="_blank" rel="noopener">https://app.basecard.dk/pricing</a>
            </p>
            <p class="mb-4 text-gray-200">Disse oplysninger er tilgængelige for kunden inden gennemførelse af betaling.</p>
            
            <h3 class="text-lg font-semibold mb-2 mt-6">Hvornår trækkes betalingen</h3>
            <p class="mb-2 text-gray-200">Ved køb af digitale produkter, tjenester eller abonnementer trækkes beløbet straks ved gennemført betaling.</p>
            <p class="mb-4 text-gray-200">Ved abonnementer trækkes betalingen automatisk ved starten af hver ny abonnementsperiode, indtil abonnementet opsiges.</p>
            
            <h3 class="text-lg font-semibold mb-2 mt-6">Betalingssikkerhed</h3>
            <p class="mb-4 text-gray-200">Betalinger gennemføres via sikre og krypterede betalingsløsninger. Basios ApS opbevarer ikke betalingskort- eller betalingsoplysninger.</p>
            
            <h3 class="text-lg font-semibold mb-2 mt-6">Afviste eller fejlede betalinger</h3>
            <p class="mb-4 text-gray-200">Hvis en betaling afvises eller ikke gennemføres, fuldføres købet ikke, og der gives ikke adgang til produktet eller tjenesten, før betalingen er gennemført korrekt.</p>
        </section>

        <section class="mb-8">
            <h2 class="text-xl font-semibold mb-3">3. Levering</h2>
            <h3 class="text-lg font-semibold mb-2">Digitale produkter og tjenester</h3>
            <p class="mb-2 text-gray-200">Digitale ydelser leveres som udgangspunkt straks efter gennemført betaling via brugerens konto på basecard.dk.</p>
            <p class="mb-4 text-gray-200">Der leveres ikke fysiske varer.</p>
        </section>

        <section class="mb-8">
            <h2 class="text-xl font-semibold mb-3">4. Fortrydelsesret</h2>
            <p class="mb-4 text-gray-200">Ved køb af digitale ydelser eller digitalt indhold, som leveres straks, accepterer kunden, at leveringen påbegyndes med det samme, og at fortrydelsesretten dermed bortfalder i henhold til gældende forbrugerlovgivning. Dette bekræftes i forbindelse med checkout.</p>
        </section>

        <section class="mb-8">
            <h2 class="text-xl font-semibold mb-3">5. Refundering</h2>
            <p class="mb-2 text-gray-200">Der ydes som udgangspunkt ikke refundering for digitale produkter, abonnementer eller påbegyndte abonnementsperioder, medmindre andet følger af ufravigelig forbrugerlovgivning eller der foreligger en væsentlig fejl, som ikke afhjælpes inden for rimelig tid.</p>
            <p class="mb-4 text-gray-200">Eventuelle refunderinger gennemføres til samme betalingsmiddel, som blev anvendt ved købet.</p>
        </section>

        <section class="mb-8">
            <h2 class="text-xl font-semibold mb-3">6. Support og kontakt</h2>
            <p class="mb-4 text-gray-200">Ved spørgsmål, reklamationer eller tekniske problemer kan du kontakte os på: <a href="mailto:info@basecard.dk" class="text-blue-400 hover:text-blue-300">info@basecard.dk</a></p>
        </section>

        <section class="mb-8">
            <h2 class="text-xl font-semibold mb-3">7. Abonnementer</h2>
            <p class="mb-2 text-gray-200">Abonnementer fornyes automatisk for hver abonnementsperiode, indtil de opsiges.</p>
            <p class="mb-2 text-gray-200">Opsigelse kan foretages når som helst via brugerens konto på basecard.dk og træder i kraft ved udløbet af den allerede betalte periode.</p>
            <p class="mb-4 text-gray-200">Basios ApS forbeholder sig retten til at ændre priser og vilkår med rimeligt varsel.</p>
        </section>

        <section class="mb-8">
            <h2 class="text-xl font-semibold mb-3">8. Lovvalg og værneting</h2>
            <p class="mb-4 text-gray-200">Disse handelsbetingelser er underlagt dansk ret. Eventuelle tvister søges løst i mindelighed og kan ellers indbringes for de danske domstole.</p>
        </section>

        <!-- Back to home -->
        <div class="mt-10 pt-6 border-t border-white/10">
            <a href="/" class="inline-flex items-center text-blue-400 hover:text-blue-300 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Tilbage til forsiden
            </a>
        </div>
    </main>
</body>
</html>
