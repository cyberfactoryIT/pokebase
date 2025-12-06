<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Help;

class HelpSeeder extends Seeder
{
    public function run(): void
    {
        Help::updateOrCreate(['key'=>'security.2fa'], [
            'icon' => 'shield-check',
            'title'=> ['en'=>'Two-Factor Authentication', 'it'=>'Autenticazione a Due Fattori', 'da'=>'Tofaktor-godkendelse'],
            'short'=> [
                'en'=>'Enable 2FA to better protect your account.',
                'it'=>'Attiva la 2FA per proteggere meglio il tuo account.',
                'da'=>'Aktivér 2FA for bedre at beskytte din konto.',
            ],
            'long' => [
                'en'=>"Two-Factor Authentication adds a second step to sign in.\nScan the QR code with your authenticator app, then enter the 6-digit code.\nIf you lose access, use your one-time **recovery codes**.",
                'it'=>"L’Autenticazione a Due Fattori aggiunge un secondo passaggio.\nScansiona il QR con l’app di autenticazione e inserisci il codice a 6 cifre.\nSe perdi l’accesso, usa i **codici di recupero**.",
                'da'=>"Tofaktor-godkendelse tilføjer et ekstra trin ved login.\nScan QR-koden med din autentifikator-app, og indtast 6-cifret kode.\nHvis du mister adgangen, brug dine **gendannelseskoder**.",
            ],
            'links' => [
                ['route'=>'2fa.show','label'=>['en'=>'Set up 2FA','it'=>'Configura 2FA','da'=>'Konfigurer 2FA']],
            ],
            'meta' => ['category'=>'security'],
            'is_active' => true,
        ]);
    }
}
