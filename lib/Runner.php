<?php

namespace lib;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PDO;

/*
 * TODO :
 * ajout transaction
 * nettoyage code
 */

class Runner
{
    private $log;

    private $src;
    private $dest;
    private $root;

    private $finalObjects;

    public function __construct(PDO $src, PDO $dest, Table $root)
    {
        $this->src = $src;
        $this->dest = $dest;
        $this->root = $root;
        $this->log = new Logger('Copytor', array(new StreamHandler('php://stdout')));
    }

    public function run($createTables, $importData)
    {
        // Récupération de l'objet source
        $sql = "SELECT * FROM ".$this->root->name." WHERE 1=1 ";
        foreach ($this->root->searchOn as $searchCriteria) {
            $sql .= "AND ".$searchCriteria['column'].' = '.$this->src->quote($searchCriteria['value']).' ';
        }

        $rootItem = $this->src->query($sql)->fetchObject();
        if (!$rootItem) {
            throw new Exception('Impossible de trouver l\'objet source');
        }

        // Récupération des objets intermédiaires
        $this->getQueries($this->root, $rootItem);

        // Affichage & traitements finaux
        foreach ($this->finalObjects as $table => $obj) {
            if ($createTables) {
                $sql = "SHOW CREATE TABLE $table";
                $sql = $this->src->query($sql)->fetchColumn(1);
                if ($this->dest->query($sql) === false) {
                    if ($this->dest->errorInfo()[0] === '42S01') {
                        $this->log->notice($this->dest->errorInfo()[2]);
                    } else {
                        throw new Exception(print_r($this->dest->errorInfo(), true));
                    }
                } else {
                    $this->log->info("La table $table a bien été créée");
                }
            }

            if ($importData && sizeof($obj) > 0) {
                $fields = array_keys((array)$obj[0]);
                $baseInsert = "INSERT INTO $table (".implode(',', $fields).")";

                foreach ($obj as $row) {
                    $values = array_map(array($this->dest, 'quote'), array_values((array)$row));
                    $sql = $baseInsert." VALUES (".implode(',', $values).");";

                    if ($this->dest->query($sql) === false) {
                        if ($this->dest->errorCode() === '23000') {
                            $this->log->notice($this->dest->errorInfo()[2], [$table, $row]);
                        } else {
                            throw new Exception($this->dest->errorCode());
                        }
                    } else {
                        $this->log->info("L'objet a bien été importé", [$table, $row]);
                    }
                }
            }
        }
    }

    public function getQueries(Table $table, $srcItem)
    {
        // Ajout de l'objet de départ dans les objets à importer
        $this->finalObjects[$table->name][] = $srcItem;


        foreach ($table->links as $link) {
            if (!$srcItem) {
                throw new Exception(
                    "Item de type $table->name invalide lors de la recherche sur $link->destTable"
                );
            }

            if (!property_exists($srcItem, $link->srcField)) {
                throw new Exception(
                    "L'item de type $table->name ne dispose pas d'un champ $link->srcField lors de la recherche sur $link->destTable"
                );
            }

            $sql = "SELECT * FROM ".$link->destTable." WHERE ".$link->destField.' = '.$this->src->quote(
                    $srcItem->{$link->srcField}
                );
            $query = $this->src->query($sql);
            if ($query === false) {
                throw new Exception('Erreur lors de la requête. Champ invalide ?');
            }

            while ($obj = $query->fetchObject()) {
                $this->getQueries($link->destTable, $obj);
            }
        }
    }

}


