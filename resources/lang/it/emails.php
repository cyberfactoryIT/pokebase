<?php

return [
    'renewal_reminder' => [
        'subject' => 'Il tuo abbonamento si rinnova tra :days giorni',
        'greeting' => 'Ciao :name,',
        'intro' => 'Questo è un promemoria che il tuo abbonamento si rinnoverà automaticamente tra :days giorni il :date.',
        'plan_details' => 'Piano: :plan (:period)',
        'amount' => 'Importo del rinnovo: :amount :currency',
        'cancel_info' => 'Se desideri cancellare il tuo abbonamento, ti preghiamo di farlo prima della data di rinnovo per evitare addebiti.',
        'manage_subscription' => 'Gestisci Abbonamento',
        'thank_you' => 'Grazie per il tuo continuo supporto!',
    ],    'payment_failed' => [
        'subject' => 'Pagamento Fallito - Azione Richiesta',
        'greeting' => 'Ciao :name,',
        'intro' => 'Non siamo riusciti a processare il pagamento del tuo abbonamento.',
        'amount' => 'Importo dovuto: :amount :currency',
        'action_required' => 'Per favore aggiorna il tuo metodo di pagamento per continuare il tuo abbonamento.',
        'update_payment' => 'Aggiorna Metodo di Pagamento',
        'warning' => 'Se il pagamento non viene aggiornato, il tuo abbonamento potrebbe essere cancellato.',
    ],];
