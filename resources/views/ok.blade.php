    private function insertBatch(&$batch, &$errorLogs)
    {
        // Regrouper les entrées par EC_RefPiece
        $grouped = collect($batch)->groupBy('EC_RefPiece');
        $validEntries = [];
        $errorDetails = [];

        foreach ($grouped as $refPiece => $entries) {
            // Initialiser les variables pour les totaux des débits et crédits
            $totalDebit = 0;
            $totalCredit = 0;

            // Parcourir les entrées pour calculer les totaux
            foreach ($entries as $entry) {
                if ($entry['EC_Sens'] == 0) {
                    // Additionner les montants débit
                    $totalDebit += $entry['EC_Montant'];
                } elseif ($entry['EC_Sens'] == 1) {
                    // Additionner les montants crédit
                    $totalCredit += $entry['EC_Montant'];
                }
            }

            // Vérifier l'équilibre pour chaque `EC_RefPiece`
            if ($totalDebit == $totalCredit) {
                // Si équilibré, ajouter les entrées à la liste des entrées valides
                $validEntries = array_merge($validEntries, $entries->toArray());
            } else {
                // Sinon, enregistrer les détails d'erreurs
                $errorDetails[$refPiece] = [
                    'refPiece' => $refPiece,
                    'sumDebit' => $totalDebit,
                    'sumCredit' => $totalCredit,
                ];
            }
        }

        // Vérifier les erreurs globales pour les EC_RefPiece
        foreach ($errorDetails as $refPiece => $details) {
            // Additionner les montants débit et crédit des erreurs
            $globalTotalDebit = 0;
            $globalTotalCredit = 0;

            // Les détails pour cet EC_RefPiece
            $entries = $details['entries'];

            foreach ($entries as $entry) {
                if ($entry['EC_Sens'] == 0) {
                    $globalTotalDebit += $entry['EC_Montant'];
                } elseif ($entry['EC_Sens'] == 1) {
                    $globalTotalCredit += $entry['EC_Montant'];
                }
            }

            // Vérifier l'équilibre global pour ce `EC_RefPiece`
            if ($globalTotalDebit == $globalTotalCredit) {
                // Si équilibré globalement, retirer les erreurs individuelles
                unset($errorDetails[$refPiece]);
            } else {
                // Ajouter les erreurs au fichier JSON si pas équilibré
                $errorLogs[] = [
                    'refPiece' => $refPiece,
                    'sumDebit' => $globalTotalDebit,
                    'sumCredit' => $globalTotalCredit,
                    'error' => 'Somme des montants débit et crédit ne correspond pas'
                ];
            }
        }

        // Insérer les entrées valides dans la base de données
        if (!empty($validEntries)) {
            DB::table('F_ECRITUREC')->insert($validEntries);
        }

        // Réinitialiser le batch après l'insertion
        $batch = [];
    }