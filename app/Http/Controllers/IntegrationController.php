<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class IntegrationController extends Controller
{


// public function importJson(Request $request)
//     {
//         $file = $request->file('json_file');
//         $data = json_decode(file_get_contents($file), true);
//         $errorLogs = [];

//         try {
//             DB::beginTransaction();

//             $batchSize = 30;
//             $batch = [];
//             $groupedEntries = [];

//             // Recuperer la valeur maximale actuelle de EC_No
//             $nextECNo = DB::table('F_ECRITUREC')->max('EC_No') + 1;

//             foreach ($data as $entry) {
//                 try {
//                     // Verifier et corriger JO_Num
//                     $JO_Num = empty($entry['JO_Num']) ? "VTED" : (string) $entry['JO_Num'];
//                     if ($JO_Num !== "VTED") {
//                         throw new \Exception('JO_Num doit etre egal à "VTED".');
//                     }

//                     // Convertir les dates en millisecondes au format de date
//                     $JM_Date = Carbon::createFromTimestampMs($entry['JM_Date'])->startOfMonth()->toDateTimeString();
//                     $EC_Date = Carbon::createFromTimestampMs($entry['EC_Date'])->startOfMonth()->toDateTimeString();
//                     $EC_Echeance = Carbon::createFromTimestampMs($entry['EC_Echeance'])->toDateTimeString();

//                     // EC_Piece doit prendre le numero du mois de JM_Date
//                     $EC_Piece = Carbon::createFromTimestampMs($entry['JM_Date'])->month;

//                     // Verifier que CG_Num n'est pas vide
//                     if (empty($entry['CG_Num'])) {
//                         throw new \Exception('CG_Num ne doit pas etre vide.');
//                     }
//                     $CG_Num = (string) $entry['CG_Num'];

//                     // Verifier que CT_Num est present dans la table F_CompteT si fourni
//                     $CT_Num = empty($entry['CT_Num']) ? NULL : (string)$entry['CT_Num'];
//                     if ($CT_Num !== NULL) {
//                         $compte = DB::table('F_CompteT')->where('CT_Num', $CT_Num)->first();

//                         if (!$compte) {
//                             throw new \Exception('CT_Num non trouve dans F_CompteT.');
//                         }

//                         if ($compte->CT_Prospect == 1) {
//                             DB::table('F_CompteT')
//                                 ->where('CT_Num', $CT_Num)
//                                 ->update(['CT_Prospect' => 0]);
//                         }
//                     }

//                     // Verifier si JM_Date existe dans la table F_JMOUV
//                     $jmouv = DB::table('F_JMOUV')->where('JM_Date', $JM_Date)->first();

//                     // Si JM_Date n'existe pas, inserer une nouvelle ligne dans F_JMOUV
//                     if (!$jmouv) {
//                         DB::table('F_JMOUV')->insert([
//                             'JO_Num' => $JO_Num,
//                             'JM_Date' => $JM_Date,
//                             'JM_Cloture' => 0,
//                             'JM_Impression' => 0
//                         ]);
//                     }

//                     // Limiter la longueur de EC_Intitule à 67 caractères
//                     $EC_Intitule = substr((string) $entry['EC_Intitule'], 0, 67);

//                     $row = [
//                         'JO_Num' => $JO_Num,
//                         'EC_No' => $nextECNo++,
//                         'JM_Date' => $JM_Date,
//                         'EC_Jour' => $entry['EC_Jour'],
//                         'EC_Date' => $EC_Date,
//                         'EC_Piece' => $EC_Piece,
//                         'EC_RefPiece' => (string) $entry['EC_RefPiece'],
//                         'CG_Num' => $CG_Num,
//                         'CT_Num' => $CT_Num,
//                         'EC_Intitule' => $EC_Intitule,
//                         'EC_Echeance' => $EC_Echeance,
//                         'EC_Sens' => $entry['EC_Sens'],
//                         'EC_Montant' => (string) $entry['EC_Montant'],
//                         // Les colonnes ajoutees
//                         'EC_NoLink' => 0,
//                         'EC_TresoPiece' => '',
//                         'N_Reglement' => 0,
//                         'EC_Parite' => 0.000000,
//                         'EC_Quantite' => 0.000000,
//                         'N_Devise' => 0,
//                         'EC_Lettre' => 0,
//                         'EC_Lettrage' => '',
//                         'EC_Point' => 0,
//                         'EC_Pointage' => '',
//                         'EC_Impression' => 0,
//                         'EC_Cloture' => 0,
//                         'EC_CType' => 0,
//                         'EC_Rappel' => 0,
//                         'CT_NumCont' => NULL,
//                         'EC_LettreQ' => 0,
//                         'EC_LettrageQ' => '',
//                         'EC_ANType' => 0,
//                         'EC_RType' => 0,
//                         'EC_Devise' => 0.000000,
//                         'EC_Remise' => 0,
//                         'EC_ExportExpert' => 0,
//                         'EC_ExportRappro' => 0,
//                         'TA_Code' => 1,
//                         'EC_Norme' => NULL,
//                         'TA_Provenance' => 0,
//                         'EC_PenalType' => 0,
//                         'EC_DatePenal' => '1753-01-01 00:00:00',
//                         'EC_DateRelance' => '1753-01-01 00:00:00',
//                         'EC_DateRappro' => '1753-01-01 00:00:00',
//                         'EC_Reference' => '',
//                         'EC_StatusRegle' => 0,
//                         'EC_MontantRegle' => 0.000000,
//                         'EC_DateRegle' => '1753-01-01 00:00:00',
//                         'EC_RIB' => 0,
//                         'EC_NoCloture' => 1,
//                         'EC_DateOp' => '1753-01-01 00:00:00',
//                         'EC_DateCloture' => '1753-01-01 00:00:00',
//                         'EC_PayNowUrl' => '',
//                         'EC_ExtProvenance' => 0,
//                         'EC_ExtSequence' => 0,
//                         'cbCreationUser' => 'CA2D6792-F19C-4A59-9EA0-16FEB0560939',
//                         'SAC_Id' => '00000000-0000-0000-0000-000000000000'
//                     ];

//                     // Grouper les entrees par EC_RefPiece pour verification
//                     $groupedEntries[$row['EC_RefPiece']][] = $row;

//                     // Ajouter la ligne au batch
//                     $batch[] = $row;

//                     // Inserer le batch lorsque la taille atteint $batchSize
//                     if (count($batch) >= $batchSize) {
//                         $this->insertBatch($batch, $errorLogs);
//                         $batch = [];
//                     }
//                 } catch (\Exception $e) {
//                     $errorLogs[] = ['entry' => $entry, 'error' => $e->getMessage()];
//                 }
//             }

//             // Inserer les lignes restantes si le batch est non vide
//             if (!empty($batch)) {
//                 $this->insertBatch($batch, $errorLogs);
//             }

//             DB::commit();

//             // Enregistrer les erreurs dans un fichier JSON
//             if (!empty($errorLogs)) {
//                 file_put_contents(storage_path('logs/error_logs.json'), json_encode($errorLogs, JSON_PRETTY_PRINT));
//             }

//             // Verifier si le fichier de log d'erreurs a ete cre
//             if (file_exists(storage_path('logs/error_logs.json'))) {
//                 return redirect()->back()->with('error', 'Donnees importees avec quelques erreurs.');
//             } else {
//                 return redirect()->back()->with('success', 'Donnees importees avec succès.');
//             }
//         } catch (\Exception $e) {
//             DB::rollBack();
//             Log::error('Erreur lors de l\'importation des donnees : ' . $e->getMessage());
//             return redirect()->back()->with('error', 'Erreur lors de l\'importation des donnees : ' . $e->getMessage());
//         }
//     }


public function importJson(Request $request)
{
    $file = $request->file('json_file');
    $data = json_decode(file_get_contents($file), true);
    $errorLogs = [];

    try {
        DB::beginTransaction();

        $batchSize = 15;
        $batch = [];
        $groupedEntries = [];

        // Recuperer la valeur maximale actuelle de EC_No
        $nextECNo = DB::table('F_ECRITUREC')->max('EC_No') + 1;

        foreach ($data as $entry) {
            try {
                // Verifier et corriger JO_Num
                $JO_Num = empty($entry['JO_Num']) ? "VTED" : (string) $entry['JO_Num'];
                if ($JO_Num !== "VTED") {
                    throw new \Exception('JO_Num doit etre egal à "VTED".');
                }

                // Convertir les dates en millisecondes au format de date
                $JM_Date = Carbon::createFromTimestampMs($entry['JM_Date'])->startOfMonth()->toDateTimeString();
                $EC_Date = Carbon::createFromTimestampMs($entry['EC_Date'])->startOfMonth()->toDateTimeString();
                $EC_Echeance = Carbon::createFromTimestampMs($entry['EC_Echeance'])->toDateTimeString();

                // EC_Piece doit prendre le numero du mois de JM_Date
                $EC_Piece = Carbon::createFromTimestampMs($entry['JM_Date'])->month;

                // Verifier que CG_Num n'est pas vide
                if (empty($entry['CG_Num'])) {
                    throw new \Exception('CG_Num ne doit pas etre vide.');
                }
                $CG_Num = (string) $entry['CG_Num'];

                // Verifier que CT_Num est present dans la table F_CompteT si fourni
                $CT_Num = empty($entry['CT_Num']) ? NULL : (string)$entry['CT_Num'];
                if ($CT_Num !== NULL) {
                    $compte = DB::table('F_CompteT')->where('CT_Num', $CT_Num)->first();

                    if (!$compte) {
                        throw new \Exception('CT_Num non trouve dans F_CompteT.');
                    }

                    if ($compte->CT_Prospect == 1) {
                        DB::table('F_CompteT')
                            ->where('CT_Num', $CT_Num)
                            ->update(['CT_Prospect' => 0]);
                    }
                }

                // Verifier si JM_Date existe dans la table F_JMOUV
                $jmouv = DB::table('F_JMOUV')->where('JM_Date', $JM_Date)->first();

                // Si JM_Date n'existe pas, inserer une nouvelle ligne dans F_JMOUV
                if (!$jmouv) {
                    DB::table('F_JMOUV')->insert([
                        'JO_Num' => $JO_Num,
                        'JM_Date' => $JM_Date,
                        'JM_Cloture' => 0,
                        'JM_Impression' => 0
                    ]);
                }

                // Limiter la longueur de EC_Intitule à 67 caractères
                $EC_Intitule = substr((string) $entry['EC_Intitule'], 0, 67);

                $row = [
                    'JO_Num' => $JO_Num,
                    'EC_No' => $nextECNo++,
                    'JM_Date' => $JM_Date,
                    'EC_Jour' => $entry['EC_Jour'],
                    'EC_Date' => $EC_Date,
                    'EC_Piece' => $EC_Piece,
                    'EC_RefPiece' => (string) $entry['EC_RefPiece'],
                    'CG_Num' => $CG_Num,
                    'CT_Num' => $CT_Num,
                    'EC_Intitule' => $EC_Intitule,
                    'EC_Echeance' => $EC_Echeance,
                    'EC_Sens' => $entry['EC_Sens'],
                    'EC_Montant' => (string) $entry['EC_Montant'],
                    // Les colonnes ajoutees
                    'EC_NoLink' => 0,
                    'EC_TresoPiece' => '',
                    'N_Reglement' => 0,
                    'EC_Parite' => 0.000000,
                    'EC_Quantite' => 0.000000,
                    'N_Devise' => 0,
                    'EC_Lettre' => 0,
                    'EC_Lettrage' => '',
                    'EC_Point' => 0,
                    'EC_Pointage' => '',
                    'EC_Impression' => 0,
                    'EC_Cloture' => 0,
                    'EC_CType' => 0,
                    'EC_Rappel' => 0,
                    'CT_NumCont' => NULL,
                    'EC_LettreQ' => 0,
                    'EC_LettrageQ' => '',
                    'EC_ANType' => 0,
                    'EC_RType' => 0,
                    'EC_Devise' => 0.000000,
                    'EC_Remise' => 0,
                    'EC_ExportExpert' => 0,
                    'EC_ExportRappro' => 0,
                    'TA_Code' => 1,
                    'EC_Norme' => NULL,
                    'TA_Provenance' => 0,
                    'EC_PenalType' => 0,
                    'EC_DatePenal' => '1753-01-01 00:00:00',
                    'EC_DateRelance' => '1753-01-01 00:00:00',
                    'EC_DateRappro' => '1753-01-01 00:00:00',
                    'EC_Reference' => '',
                    'EC_StatusRegle' => 0,
                    'EC_MontantRegle' => 0.000000,
                    'EC_DateRegle' => '1753-01-01 00:00:00',
                    'EC_RIB' => 0,
                    'EC_NoCloture' => 1,
                    'EC_DateOp' => '1753-01-01 00:00:00',
                    'EC_DateCloture' => '1753-01-01 00:00:00',
                    'EC_PayNowUrl' => '',
                    'EC_ExtProvenance' => 0,
                    'EC_ExtSequence' => 0,
                    'cbCreationUser' => 'CA2D6792-F19C-4A59-9EA0-16FEB0560939',
                    'SAC_Id' => '00000000-0000-0000-0000-000000000000'
                ];

                // Grouper les entrees par EC_RefPiece pour verification
                $groupedEntries[$row['EC_RefPiece']][] = $row;

            } catch (\Exception $e) {
                $errorLogs[] = ['entry' => $entry, 'error' => $e->getMessage()];
            }
        }

        // Traiter chaque groupe d'entrées
        foreach ($groupedEntries as $refPiece => $entries) {
            $batch = [];
            foreach ($entries as $row) {
                $batch[] = $row;

                // Inserer le batch lorsque la taille atteint $batchSize
                if (count($batch) >= $batchSize) {
                    $this->insertBatch($batch, $errorLogs);
                    $batch = [];
                }
            }

            // Inserer les lignes restantes si le batch est non vide
            if (!empty($batch)) {
                $this->insertBatch($batch, $errorLogs);
            }
        }

        DB::commit();

        // Enregistrer les erreurs dans un fichier JSON
        if (!empty($errorLogs)) {
            file_put_contents(storage_path('logs/error_logs.json'), json_encode($errorLogs, JSON_PRETTY_PRINT));
        }

        // Verifier si le fichier de log d'erreurs a ete cree
        if (file_exists(storage_path('logs/error_logs.json'))) {
            return redirect()->back()->with('error', 'Donnees importees avec quelques erreurs.');
        } else {
            return redirect()->back()->with('success', 'Donnees importees avec succès.');
        }
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur lors de l\'importation des donnees : ' . $e->getMessage());
        return redirect()->back()->with('error', 'Erreur lors de l\'importation des donnees : ' . $e->getMessage());
    }
}



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

            // Vérifier l'équilibre pour chaque EC_RefPiece
            if ($totalDebit == $totalCredit) {
                // Si équilibré, ajouter les entrées à la liste des entrées valides
                $validEntries = array_merge($validEntries, $entries->toArray());
            } else {
                // Sinon, enregistrer les détails d'erreurs
                $errorDetails[$refPiece] = [
                    'refPiece' => $refPiece,
                    'sumDebit' => $totalDebit,
                    'sumCredit' => $totalCredit,
                    'entries' => $entries->toArray(),  // Ajouter les entrées à l'erreur pour plus de contexte
                ];
            }
        }

        // Insérer les entrées valides dans la base de données
        if (!empty($validEntries)) {
            try {
                DB::table('F_ECRITUREC')->insert($validEntries);
            } catch (\Exception $e) {
                foreach ($validEntries as $entry) {
                    $errorLogs[] = [
                        'entry' => $entry,
                        'error' => 'Database insertion error: ' . $e->getMessage()
                    ];
                }
            }
        }

        // Traiter les erreurs d'équilibre pour les EC_RefPiece
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

            // Vérifier l'équilibre global pour ce EC_RefPiece
            if ($globalTotalDebit == $globalTotalCredit) {
                // Si équilibré globalement, retirer les erreurs individuelles
                unset($errorDetails[$refPiece]);
            } else {
                // Ajouter les erreurs au fichier JSON si pas équilibré
                $errorLogs[] = [
                    'refPiece' => $refPiece,
                    'sumDebit' => $globalTotalDebit,
                    'sumCredit' => $globalTotalCredit,
                    'entries' => $entries,  // Ajouter les entrées pour plus de contexte
                    'error' => 'Somme des montants débit et crédit ne correspond pas'
                ];
            }
        }

        // Réinitialiser le batch après l'insertion
        $batch = [];
    }


}
