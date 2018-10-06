<?php
  if($argv[1]=="--debug"){
    $debug=true;
  }else {
    $debug=false;
  }

require 'vendor/autoload.php';
require '.auth.php';
$table_prefix  = 'wp_';

use XBase\Table;

$pdo = new PDO($cnn, $user, $pass);


$sql="DROP TABLE sitart;";
$stml = $pdo->prepare($sql);
$stml->execute();

$sql="CREATE TABLE IF NOT EXISTS `sitart` (
      `magazzino_id` varchar(2) DEFAULT NULL,
      `articolo_id`  varchar(15) DEFAULT NULL,
      `anno` int(11) DEFAULT NULL,
      `giacenza` int(11) DEFAULT NULL,
      `negozio_id`  varchar(2) DEFAULT NULL,
      `scarico_del` datetime DEFAULT NULL,
      `registrazione_del` datetime DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$stml = $pdo->prepare($sql);
$stml->execute();

$dbfPath='/home/artisan/kiri-origin/dbf/';
$table = new Table($dbfPath.'kiri/SITART.DBF',array('tmcod','arcod1','saanno','saesist','tmcods','sadults','sadultc'));

$count=0;
$punti=0;
echo "Importazione: sitart\r\n";
while ($record = $table->nextRecord()) {
  $count++;
  $punti++;
  $start=strtotime("2015-01-01");
  //echo date("jS F, Y", $start)."\r\n";
  $registrazioneDel=strtotime($record->sadultc);
  if ($registrazioneDel>$start){
    if ($record->tmcods=='K1'){
      $sql="INSERT INTO `sitart` (magazzino_id, articolo_id, anno, giacenza, negozio_id, scarico_del, registrazione_del) VALUES ";
      $sql.= "(";
      $sql.= $pdo->quote($record->tmcod)   . " , "; //magazzino_id
      $sql.= $pdo->quote($record->arcod1)  ." , "; // articolo_id
      $sql.= $pdo->quote($record->saanno)  ." , "; // anno
      $sql.= $pdo->quote($record->saesist)." , ";   // giacenza
      $sql.= $pdo->quote($record->tmcods)." , ";   // negozio_id
      $sql.= $pdo->quote(date("Y-m-d H:i:s", strtotime($record->sadults))) . " , ";
      $sql.= $pdo->quote(date("Y-m-d H:i:s", strtotime($record->sadultc)));
      $sql.= "); \n";
      $stml = $pdo->prepare($sql);
      $stml->execute();
    }
  }
  if ($punti>99){
    echo ".";
    $punti=0;
  }
  if ($debug & $count>999){
    break;
  }
}
echo "\n\rsitart: " . $count ."\n\r";



/*
*   A N A R T I
*/
$sql="DROP TABLE anarti;";
$stml = $pdo->prepare($sql);
$stml->execute();
$sql="CREATE TABLE IF NOT EXISTS `anarti` (
  `magazzino_id`varchar(2) DEFAULT NULL,        /*tmcod*/
  `articolo_id` varchar(15) DEFAULT NULL,       /*arcod1*/
  `ean` varchar(15) DEFAULT NULL,               /*arcod2*/
  `descrizione` varchar(30) DEFAULT NULL,       /*ardes*/
  `categoria_id` varchar(3) DEFAULT NULL,       /*tgcod*/
  `sotto_categoria_id` varchar(2) DEFAULT NULL, /*tgcods*/
  `marchio_id` varchar(5) DEFAULT NULL,         /*tscod*/
  `linea_id` varchar(3) DEFAULT NULL,           /*ar_linea*/
  `unita_misura` varchar(2) DEFAULT NULL,       /*armis1*/
  `ordine_minimo` int(11) DEFAULT NULL,         /*arordmin*/
  `iva` varchar(3) DEFAULT NULL,                /*ascod*/
  `prezzo_listino` decimal(15,2) DEFAULT NULL,  /*arlist1*/
  `lordo` int(11) DEFAULT NULL,                 /*arpeso*/
  `tara` int(11) DEFAULT NULL,                  /*artara*/
  `data_immissione` datetime DEFAULT NULL,      /*ardtimm*/
  `data_variazione` datetime DEFAULT NULL,      /*ardtvar*/
  `articolo_attivo` varchar(1) DEFAULT NULL,    /*aratt S/s/N/E/D/M)F*/
  `altezza` int(11) DEFAULT NULL,               /*ar_alte*/
  `larghezza` int(11) DEFAULT NULL,             /*ar_larg*/
  `lunghezza` int(11) DEFAULT NULL,             /*ar_lung*/
  `punti_fidelity_card` varchar(6) DEFAULT NULL,/*ar_punti*/
  `verifica_giacenza` varchar(1) DEFAULT NULL,  /*ar_esist*/
  `is_rendibile` varchar(1) DEFAULT NULL        /*ar_rendi*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$stml = $pdo->prepare($sql);
$stml->execute();

$table = new Table($dbfPath.'ANARTI.DBF',
array(
'tmcod',
'arcod1',
'arcod2',
'ardes',
'tgcod',
'tgcods',
'tscod',
'ar_linea',
'armis1',
'arordmin',
'ascod',
'arlist1',
'arpeso',
'artara',
'ardtimm',
'ar_datvar',
'aratt',
'ar_alte',
'ar_larg',
'ar_lung',
'ar_punti',
'ar_esist',
'ar_rendi'));

$count=0;
$punti=0;
$eanZeroIniziale=0;
$eanZeroFinale=0;
$eanIndeterminato=0;

echo "Importazione: anarti\r\n";
while ($record = $table->nextRecord()) {
  $count++;
  $punti++;
  if(is_numeric($record->arcod2)){
    $codeEan=$record->arcod2;
    if(strlen($codeEan)>13){
      if (substr($codeEan,0,1)=="0"){
        $codeEan=substr($codeEan,1,13);
        $eanZeroIniziale++;
        //echo "\n\rEAN: Rimosso zero iniziale: " . $codeEan . "\n\r";
        }else{
          if (substr($codeEan,13,1)=="0"){
            $codeEan=substr($codeEan,0,12);
            $eanZeroFinale++;
            //echo "\n\rEAN: Rimosso zero finale: " . $codeEan . "\n\r";
          }
            $eanIndeterminato++;
            //echo "\n\rEAN: Impossibile aggiustare: " . $codeEan . "\n\r";
        }
      }

    $sql="
    INSERT INTO `anarti` (
        magazzino_id,
        articolo_id,
        ean,
        descrizione,
        categoria_id,
        sotto_categoria_id,
        marchio_id,
        linea_id,
        unita_misura,
        ordine_minimo,
        iva,
        prezzo_listino,
        lordo,
        tara,
        data_immissione,
        data_variazione,
        articolo_attivo,
        altezza,
        larghezza,
        lunghezza,
        punti_fidelity_card,
        verifica_giacenza,
        is_rendibile
      ) VALUES ";

      $sql.= "(";
      $sql.= $pdo->quote(addslashes($record->tmcod)) . " , "; //magazzino_id
      $sql.= $pdo->quote(addslashes($record->arcod1))  . " , ";
      $codeEan=str_pad($codeEan,13,'0', STR_PAD_LEFT);
      $sql.= $pdo->quote($codeEan)   . " , "; //ean
      $sql.= $pdo->quote($record->ardes)   . " , ";
      $sql.= $pdo->quote($record->tgcod)   . " , ";
      $sql.= $pdo->quote($record->tgcods)   . " , ";
      $sql.= $pdo->quote($record->tscod)   . " , ";
      $sql.= $pdo->quote($record->ar_linea)   . " , ";
      $sql.= $pdo->quote($record->armis1)   . " , ";
      $sql.= $pdo->quote($record->arordmin)   . " , ";
      $sql.= $pdo->quote($record->ascod) . " , ";
      $sql.= $pdo->quote($record->arlist1) . " , ";
      $sql.= $pdo->quote($record->arpeso) . " , ";
      $sql.= $pdo->quote($record->artara) . " , ";
      $sql.= $pdo->quote(date("Y-m-d H:i:s", strtotime($record->ardtimm))). " , ";
      $sql.= $pdo->quote(date("Y-m-d H:i:s", strtotime($record->ar_datvar))). " , ";
      $sql.= $pdo->quote($record->aratt) . " , ";
      $sql.= $pdo->quote($record->ar_alte) . " , ";
      $sql.= $pdo->quote($record->ar_larg) . " , ";
      $sql.= $pdo->quote($record->ar_lung) . " , ";
      $sql.= $pdo->quote($record->ar_punti) . " , ";
      $sql.= $pdo->quote($record->ar_esist) . " , ";
      $sql.= $pdo->quote($record->ar_rendi);
      $sql.= "); \n";

      $stml = $pdo->prepare($sql);
      $stml->execute();
    }
    if ($punti>99){
      echo ".";
      $punti=0;
    }
    if ($debug & $count>999){
      break;
    }
}
echo "anarti: " . $count ." Codici EAN: rimosso zero iniziale " . $eanZeroIniziale . ", rimosso zero finale " . $eanZeroFinale .", non aggiustabili " . $eanIndeterminato . "\n\r";

/*
*   T A B L I N
*/
$sql="DROP TABLE tablin";
$stml = $pdo->prepare($sql);
$stml->execute();


$sql="CREATE TABLE IF NOT EXISTS `tablin` (
  `marchio_id` varchar(5) DEFAULT NULL,         /*tl_s_cod*/
  `linea_id` varchar(3) DEFAULT NULL,           /*tl_l_cod*/
  `marchio` varchar(30) DEFAULT NULL,           /*tl_s_des*/
  `linea` varchar(30) DEFAULT NULL,             /*tl_l_des*/
  `linea_sequenza_id` varchar(8) DEFAULT NULL   /*tl_l_seq*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$stml = $pdo->prepare($sql);
$stml->execute();


$table = new Table($dbfPath.'tablin.dbf');
$count=0;
$punt=0;
echo "Importazione: tablin\r\n";
while ($record = $table->nextRecord()) {
  $count++;
  $punti++;
  $sql="
  INSERT INTO `tablin` (
      marchio_id,
      linea_id,
      marchio,
      linea,
      linea_sequenza_id
    ) VALUES ";
    $sql.= "(";
    $sql.= $pdo->quote($record->tl_s_cod) . " , ";
    $sql.= $pdo->quote($record->tl_l_cod) . " , ";
    $sql.= $pdo->quote($record->tl_s_des) . " , ";
    $sql.= $pdo->quote($record->tl_l_des) . " , ";
    $sql.= $pdo->quote($record->tl_l_seq) ;
    $sql.= "); \n";
    $stml = $pdo->prepare($sql);
    $stml->execute();
    if ($punti>99){
      echo ".";
      $punti=0;
    }
    if ($debug & $count>999){
      break;
    }

}
echo "\n\rtablin: " . $count ."\n\r";


/*
*   T A B M E R
*/
$sql="DROP TABLE tabmer";
$stml = $pdo->prepare($sql);
$stml->execute();

$sql="CREATE TABLE IF NOT EXISTS `tabmer` (
  `categoria_id` varchar(3) DEFAULT NULL,           /*tgcod*/
  `sotto_categoria_id` varchar(2) DEFAULT NULL,     /*tgcods*/
  `descrizione_categoria` varchar(30) DEFAULT NULL, /*tgdes*/
  `descrizione_parent` varchar(30) DEFAULT NULL,    /*tgdesp*/
  `iva` varchar(3) DEFAULT NULL                     /*ascod*/
  );";
$stml = $pdo->prepare($sql);
$stml->execute();



$table = new Table($dbfPath.'tabmer.dbf');
$count=0;
$punt=0;
echo "Importazione: tabmer\r\n";
while ($record = $table->nextRecord()) {
  $count++;
  $punti++;
  $sql="
  INSERT INTO `tabmer` (
      categoria_id,
      sotto_categoria_id,
      descrizione_categoria,
      descrizione_parent,
      iva
    ) VALUES ";
    $sql.= "(";
    $sql.= $pdo->quote(addslashes($record->tgcod)) . " , ";
    $sql.= $pdo->quote(addslashes($record->tgcods)) . " , ";
    $sql.= $pdo->quote(addslashes($record->tgdes)) . " , ";
    $sql.= $pdo->quote(addslashes($record->tgdesp)) . " , ";
    $sql.= $pdo->quote(addslashes($record->ascod));
    $sql.= "); \n";

    $stml = $pdo->prepare($sql);
    $stml->execute();
    if ($punti>99){
      echo ".";
      $punti=0;
    }
    if ($debug & $count>999){
      break;
    }

}
echo "\r\ntabmer " . $count ."\n\r";




/*
*   TABSTA
*/
$sql="DROP TABLE tabsta";
$stml = $pdo->prepare($sql);
$stml->execute();

$sql="CREATE TABLE IF NOT EXISTS `tabsta` (
    `marchio_id` varchar(5) DEFAULT NULL,       /*tscod*/
    `descrizione` varchar(30) DEFAULT NULL,     /*tsdes*/
    `ean` varchar(13) DEFAULT NULL,             /*ean*/
    `marchio_sequenza_id` int(11) DEFAULT NULL, /*ts_seq*/
    `fornitore_id` varchar(7) DEFAULT NULL,     /*ts_for*/
    `is_gestito` varchar(1) DEFAULT NULL,       /*ts_gestito*/
    `is_attivo` varchar(1) DEFAULT NULL         /*ts_attivo*/
  );";
$stml = $pdo->prepare($sql);
$stml->execute();

$table = new Table($dbfPath.'tabsta.dbf');
$count=0;
$punt=0;
echo "Importazione: tabsta\r\n";
while ($record = $table->nextRecord()) {
  $count++;
  $punti++;
  $sql="
  INSERT INTO `tabsta` (
      marchio_id,
      descrizione,
      ean,
      marchio_sequenza_id,
      fornitore_id,
      is_gestito,
      is_attivo
    ) VALUES ";
    $sql.= "(";
    $sql.= $pdo->quote($record->tscod) . " , ";
    $sql.= $pdo->quote($record->tsdes) . " , ";
    $sql.= $pdo->quote($record->ts_ean_pre.$record->ts_ean_num) . " , ";
    $sql.= $pdo->quote($record->ts_seq) . " , ";
    $sql.= $pdo->quote($record->ts_for) . " , ";
    $sql.= $pdo->quote($record->ts_gestito) . " , ";
    $sql.= $pdo->quote($record->ts_attivo);
    $sql.= "); \n";

    $stml = $pdo->prepare($sql);
    $stml->execute();
    if ($punti>99){
      echo ".";
      $punti=0;
    }
    if ($debug & $count>999){
      break;
    }
}
echo "\n\rtabsta " . $count ."\n\r";
