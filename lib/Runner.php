<?php

namespace lib;

use Exception;
use PDO;

/*
 * TODO :
 * nettoyage code
 */

class Runner
{
    private $log;

    private $src;
    private $dest;
    private $root;

    private $doneRequests;
    private $finalObjects;

    public function __construct(PDO $src, PDO $dest, Table $root)
    {
        $this->src = $src;
        $this->dest = $dest;
        $this->root = $root;
        //$this->log = new Logger('Copytor', array(new StreamHandler('php://stdout'), new StreamHandler('/tmp/copytor_' . date('d-m-Y') . '.log')));
    }

    public function run($createTables, $importData)
    {
        $this->doneRequests = [];
        $this->dest->beginTransaction();

        try {
            $this->dest->query("SET FOREIGN_KEY_CHECKS=0");

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

                    //echo $sql;
                    if ($this->dest->query($sql) === false) {
                        if ($this->dest->errorInfo()[0] === '42S01') {
                            //$this->log->notice($this->dest->errorInfo()[2]);
                            echo "NOTICE : ".$this->dest->errorInfo()[2]."<br/>";
                        } else {
                            //$this->log->critical(print_r($this->dest->errorInfo(), true));
                            echo "EXCEPTION : ".print_r($this->dest->errorInfo(), true)."<br/>";
                            throw new Exception(print_r($this->dest->errorInfo(), true));
                        }
                    } else {
                        //$this->log->info("La table $table a bien été créée");
                        echo "INFO : La table $table a bien été créée<br/>";
                    }
                }

                if ($importData && sizeof($obj) > 0) {
                    $fields = array_keys((array)$obj[0]);
                    foreach ($fields as $key => $field) {
                        $fields[$key] = "`$field`";
                    }

                    $baseInsert = "INSERT INTO $table (".implode(',', $fields).")";

                    foreach ($obj as $row) {
                        $values = array_map(
                            function ($var) {
                                if (isset($var)) {
                                    return $this->dest->quote($var);
                                } else {
                                    return 'NULL';
                                }
                            },
                            array_values((array)$row)
                        );
                        $sql = $baseInsert." VALUES (".implode(',', $values).")";
                        $sql .= " ON DUPLICATE KEY UPDATE ";
                        $update = "";
                        foreach ($fields as $field) {
                            $update .= " $field = VALUES($field), ";
                        }
                        $update = trim($update, " ,");
                        $sql .= $update;

                        //echo "$sql\n";

                        if ($this->dest->query($sql) === false) {
                            if ($this->dest->errorCode() === '23000') {
                                //$this->log->notice($this->dest->errorInfo()[2], [$table, $row]);
                                echo "NOTICE : ".$this->dest->errorInfo()[2]." table $table "." objet ".print_r(
                                        $row,
                                        true
                                    )."<br/>";
                            } else {
                                //$this->log->critical($this->dest->errorCode());
                                echo "EXCEPTION : ".$this->dest->errorCode()."<br/>";
                                throw new Exception($this->dest->errorCode());
                            }
                        } else {
                            //$this->log->info("L'objet a bien été importé", [$table, $row]);
                            echo "INFO : L'objet a bien été importé dans la table $table : ".print_r(
                                    $row,
                                    true
                                )."<br/>";
                        }
                    }
                }
            }

            $this->dest->query("SET FOREIGN_KEY_CHECKS=1");
            $this->dest->commit();
        } catch (Exception $e) {
            $this->dest->rollBack();
            throw $e;
        }


    }

    public function getQueries(Table $table, $srcItem)
    {
        //echo "$table->name\n";
        //echo print_r($srcItem, true) . "\n";

        // Ajout de l'objet de départ dans les objets à importer
        $this->finalObjects[$table->name][] = $srcItem;

        foreach ($table->links as $link) {
            if (in_array([$link->destTable, $link->destField, $srcItem->{$link->srcField}], $this->doneRequests)) {
                continue;
            } else {
                $this->doneRequests[] = [$link->destTable, $link->destField, $srcItem->{$link->srcField}];
            }

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

            $sql = "SELECT * FROM ".$link->destTable." WHERE `".$link->destField.'` = '.$this->src->quote(
                    $srcItem->{$link->srcField}
                );
            //echo $sql."\n";
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


