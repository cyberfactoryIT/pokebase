<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faq;
use Carbon\Carbon;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        Faq::create([
            'category' => 'General',
            'question' => [
                'en' => 'What is Evalua?',
                'it' => 'Che cos’è Evalua?',
                'da' => 'Hvad er Evalua?',
            ],
            'answer' => [
                'en' => 'Evalua is a SaaS platform designed for companies to manage and evaluate projects efficiently.',
                'it' => 'Evalua è una piattaforma SaaS progettata per le aziende per gestire e valutare i progetti in modo efficiente.',
                'da' => 'Evalua er en SaaS-platform designet til virksomheder til at administrere og evaluere projekter effektivt.',
            ],
            'is_published' => true,
            'sort_order' => 1,
            'published_at' => Carbon::now(),
        ]);

        Faq::create([
            'category' => 'Account',
            'question' => [
                'en' => 'How do I create an account?',
                'it' => 'Come creo un account?',
                'da' => 'Hvordan opretter jeg en konto?',
            ],
            'answer' => [
                'en' => 'Click on “Sign Up”, complete the form, and confirm your email address.',
                'it' => 'Clicca su “Registrati”, compila il modulo e conferma il tuo indirizzo email.',
                'da' => 'Klik på “Tilmeld dig”, udfyld formularen og bekræft din e-mailadresse.',
            ],
            'is_published' => true,
            'sort_order' => 2,
            'published_at' => Carbon::now(),
        ]);

        Faq::create([
            'category' => 'Account',
            'question' => [
                'en' => 'Can I invite team members?',
                'it' => 'Posso invitare membri del team?',
                'da' => 'Kan jeg invitere teammedlemmer?',
            ],
            'answer' => [
                'en' => 'Yes, admins can invite members by email and assign roles with different permissions.',
                'it' => 'Sì, gli amministratori possono invitare membri tramite email e assegnare ruoli con permessi diversi.',
                'da' => 'Ja, administratorer kan invitere medlemmer via e-mail og tildele roller med forskellige rettigheder.',
            ],
            'is_published' => true,
            'sort_order' => 3,
            'published_at' => Carbon::now(),
        ]);

        Faq::create([
            'category' => 'Account',
            'question' => [
                'en' => 'Does Evalua support two-factor authentication (2FA)?',
                'it' => 'Evalua supporta l’autenticazione a due fattori (2FA)?',
                'da' => 'Understøtter Evalua to-faktor-godkendelse (2FA)?',
            ],
            'answer' => [
                'en' => 'Yes, you can enable 2FA in your profile settings to add an extra layer of security.',
                'it' => 'Sì, puoi attivare la 2FA nelle impostazioni del profilo per aggiungere un ulteriore livello di sicurezza.',
                'da' => 'Ja, du kan aktivere 2FA i dine profilindstillinger for at tilføje et ekstra sikkerhedslag.',
            ],
            'is_published' => true,
            'sort_order' => 4,
            'published_at' => Carbon::now(),
        ]);

        Faq::create([
            'category' => 'Billing',
            'question' => [
                'en' => 'What payment methods are supported?',
                'it' => 'Quali metodi di pagamento sono supportati?',
                'da' => 'Hvilke betalingsmetoder understøttes?',
            ],
            'answer' => [
                'en' => 'We accept major credit cards and bank transfers for yearly plans.',
                'it' => 'Accettiamo le principali carte di credito e bonifici per i piani annuali.',
                'da' => 'Vi accepterer de fleste kreditkort og bankoverførsler for årlige planer.',
            ],
            'is_published' => true,
            'sort_order' => 5,
            'published_at' => Carbon::now(),
        ]);

        Faq::create([
            'category' => 'Billing',
            'question' => [
                'en' => 'Can I change my subscription plan?',
                'it' => 'Posso cambiare il mio piano di abbonamento?',
                'da' => 'Kan jeg ændre mit abonnement?',
            ],
            'answer' => [
                'en' => 'Yes, you can upgrade or downgrade your plan at any time from the billing page.',
                'it' => 'Sì, puoi effettuare upgrade o downgrade del piano in qualsiasi momento dalla pagina di fatturazione.',
                'da' => 'Ja, du kan op- eller nedgradere dit abonnement når som helst fra faktureringssiden.',
            ],
            'is_published' => true,
            'sort_order' => 6,
            'published_at' => Carbon::now(),
        ]);

        Faq::create([
            'category' => 'Billing',
            'question' => [
                'en' => 'Do you offer a free trial?',
                'it' => 'Offrite una prova gratuita?',
                'da' => 'Tilbyder I en gratis prøveperiode?',
            ],
            'answer' => [
                'en' => 'Yes, Evalua offers a free trial period so you can test the platform before subscribing.',
                'it' => 'Sì, Evalua offre un periodo di prova gratuita per testare la piattaforma prima dell’abbonamento.',
                'da' => 'Ja, Evalua tilbyder en gratis prøveperiode, så du kan teste platformen, før du abonnerer.',
            ],
            'is_published' => true,
            'sort_order' => 7,
            'published_at' => Carbon::now(),
        ]);

        Faq::create([
            'category' => 'Billing',
            'question' => [
                'en' => 'Can I download my invoices?',
                'it' => 'Posso scaricare le mie fatture?',
                'da' => 'Kan jeg downloade mine fakturaer?',
            ],
            'answer' => [
                'en' => 'Yes, all invoices are available in your billing section and can be downloaded as PDF.',
                'it' => 'Sì, tutte le fatture sono disponibili nella sezione fatturazione e scaricabili in PDF.',
                'da' => 'Ja, alle fakturaer er tilgængelige i din faktureringssektion og kan downloades som PDF.',
            ],
            'is_published' => true,
            'sort_order' => 8,
            'published_at' => Carbon::now(),
        ]);

        Faq::create([
            'category' => 'Security',
            'question' => [
                'en' => 'Is Evalua GDPR compliant?',
                'it' => 'Evalua è conforme al GDPR?',
                'da' => 'Er Evalua GDPR-kompatibel?',
            ],
            'answer' => [
                'en' => 'Yes, Evalua complies with GDPR requirements, ensuring data protection and privacy.',
                'it' => 'Sì, Evalua è conforme al GDPR e garantisce protezione e riservatezza dei dati.',
                'da' => 'Ja, Evalua overholder GDPR-kravene og sikrer databeskyttelse og privatliv.',
            ],
            'is_published' => true,
            'sort_order' => 9,
            'published_at' => Carbon::now(),
        ]);

        Faq::create([
            'category' => 'Security',
            'question' => [
                'en' => 'How does Evalua protect my data?',
                'it' => 'Come protegge Evalua i miei dati?',
                'da' => 'Hvordan beskytter Evalua mine data?',
            ],
            'answer' => [
                'en' => 'All data is encrypted in transit and at rest, with daily backups and disaster recovery procedures.',
                'it' => 'Tutti i dati sono crittografati in transito e a riposo, con backup giornalieri e procedure di disaster recovery.',
                'da' => 'Alle data krypteres under overførsel og i hvile, med daglige sikkerhedskopier og katastrofegendannelsesprocedurer.',
            ],
            'is_published' => true,
            'sort_order' => 10,
            'published_at' => Carbon::now(),
        ]);

        Faq::create([
            'category' => 'Integration',
            'question' => [
                'en' => 'Can I integrate Evalua with other tools?',
                'it' => 'Posso integrare Evalua con altri strumenti?',
                'da' => 'Kan jeg integrere Evalua med andre værktøjer?',
            ],
            'answer' => [
                'en' => 'Yes, Evalua provides an API and webhooks for integration with external systems like CRM or ERP. Contact customer support for more information.',
                'it' => 'Sì, Evalua offre un’API e webhook per l’integrazione con sistemi esterni come CRM o ERP. Contatta il supporto clienti per ulteriori informazioni.',
                'da' => 'Ja, Evalua leverer en API og webhooks til integration med eksterne systemer som CRM eller ERP. Kontakt kundesupport for mere information.',
            ],
            'is_published' => true,
            'sort_order' => 11,
            'published_at' => Carbon::now(),
        ]);

        Faq::create([
            'category' => 'Account',
            'question' => [
                'en' => 'What happens if I cancel my subscription?',
                'it' => 'Cosa succede se cancello il mio abbonamento?',
                'da' => 'Hvad sker der, hvis jeg annullerer mit abonnement?',
            ],
            'answer' => [
                'en' => 'Your data will remain accessible until the end of the paid period, then archived for 30 days before deletion.',
                'it' => 'I tuoi dati resteranno accessibili fino al termine del periodo pagato, poi archiviati per 30 giorni prima della cancellazione.',
                'da' => 'Dine data forbliver tilgængelige indtil slutningen af den betalte periode og arkiveres derefter i 30 dage, før de slettes.',
            ],
            'is_published' => true,
            'sort_order' => 12,
            'published_at' => Carbon::now(),
        ]);
    }
}
