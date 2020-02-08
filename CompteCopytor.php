<?php

use lib\Runner;
use lib\Table;

require __DIR__.'/vendor/autoload.php';

class CompteCopytor
{

    public static function start($login, $createTable = false, $importData = false)
    {

        // Configuration des bases
        $src = new PDO("mysql:dbname=copy1;host=127.0.0.1", "root", "root");
        $dest = new PDO("mysql:dbname=copy2;host=127.0.0.1", "root", "root");

        // Configuration de la table de départ
        $root = new Table('compte');
        $root->searchOn('login', $login);

        // Récupération des tables liées
        $identite = new Table('identite');
        $facture = new Table('facture');
        $facture_ko = new Table('facture_ko');

        // Liens
        $root->link('id', $identite, 'compte');
        $root->link('id', $facture, 'compte');
        $facture->link('id', $facture_ko, 'idFacture');

        // Lancement
        $r = new Runner($src, $dest, $root);
        $r->run($createTable, $importData);

    }

}