<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <script lang="javascript" src="../sheetjs/dist/xlsx.full.min.js"></script>
    <title>Document</title>
</head>
<body>
  <?php
    //require_once '../requettes/fpdf/vendor/autoload.php';
    require_once '../requettes/Class/Manager.php';
        //session_start();
    function content(){

  ?>
    <style type="text/css">
        body{
          /*padding-left: 100px;
          padding-right: 100px;
          margin-top: 100px*/
        } 

       .tgl{
         /*text-decoration:uppercase !important;*/
       }
        .tg  {border-collapse:collapse;border-spacing:0; text-align:center;}
        .tg td{font-family:Arial, sans-serif;font-size:13px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;}
        .tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;}
        .tg .tg-yzt1{background-color:#efefef;vertical-align:top}
        .tg .tg-uhkr{background-color:#ffce93}
        .tg .tg-i6eq{background-color:#ffccc9;vertical-align:top}
        .tg .tg-nvd0{background-color:#f8a102;vertical-align:top}
        .tg .tg-gr78{background-color:#ffce93;vertical-align:top}
        .tg .tg-c57o{background-color:#ecf4ff;vertical-align:top}
        .tg .tg-2s1i{background-color:#fd6864;vertical-align:top}
        .tg .tg-yw4l{vertical-align:top }
    </style> 

        <table class="tg" id="document">
          
<thead>
<tr>
      <th class="tg-uhkr" rowspan="2">N° de sequence</th>
      <th class="tg-uhkr" rowspan="2" style="width:2cm;">Date</th>
      <th class="tg-uhkr" rowspan="2" style="width:2cm;">Nom / Raison social</th>
      <th class="tg-uhkr" rowspan="2">Résident / Nonrésident</th>
      <th class="tg-uhkr" rowspan="2">Profession / secteur d\'activité</th>
      <th class="tg-uhkr" rowspan="2">Devise vendue ou achetée</th>
      <th class="tg-uhkr" colspan="2">Montant de l\'operation en devise</th>
      <th class="tg-uhkr" rowspan="2" style="width:0.5cm;">N° compt e évent uelle ment</th>
      <th class="tg-uhkr" rowspan="2" style="width:0.5cm;">Cour s de référ ence de la BEAC</th>
      <th class="tg-uhkr" rowspan="2" style="width:0.5cm;">Cours appliq ué</th>
      <th class="tg-uhkr" rowspan="2">Periode de voyage</th>
      <th  class="tg-uhkr" rowspan="2">Pays de destination</th>
      <th  class="tg-uhkr" rowspan="2">N° passeport</th>
      <th class="tg-uhkr" rowspan="2">Date d\'émission du passeport</th>
      <th class="tg-uhkr" rowspan="2">Pays d\'émission du passeport</th>
      <th class="tg-uhkr" rowspan="2">origine des fonds</th>
    </tr> 
    <tr>
      <th class="tg-uhkr" style="height:20%;">achat</th>
      <th class="tg-uhkr" style="height:20%;">vente</th>
    </tr>
</thead>

      <tbody> 
<?php
$manager = new Manager;
 
/**
 * Display data
 */
$dbconn = new PDO('mysql:host=localhost;dbname=ceo','root',''); 
$nomSession = $_SESSION['sessionName'];
$display = $manager->afficherDonnees($dbconn, $nomSession); 
//var_dump($display);
//echo $display;  
foreach ($display as $key => $data) {$key+=1; 
?>     
         
         
         <tr>
          <td class="tgl"><?=$key?></td>
          <td class="tgl"><?=$data['createdAt']?></td>
          <td class="tgl"><?=$data['nomMandataire']?></td>
          <td class="tgl"><?=$data['statut']?></td>
          <td class="tgl"><?=$data['profession']?></td>
          <td class="tgl">EURO</td>
          <td class="tgl">/</td>
          <td class="tgl"><?=$default = 7200.00;?></td>
          <td class="tgl">/</td>
          <td class="tgl">/</td>
          <td class="tgl">/</td>
          <td class="tgl"><?=$data['depart'].'<br>AU<br>'.$data['arrive']?></td>
          <td class="tgl"><?=$data['destination']?></td>
          <td class="tgl"><?=$data['matricule']?></td>
          <td class="tgl">05-08-2013</td>
          <td class="tgl"><?=$data['paysEmission']?></td>
          <td class="tgl"><?=$data['origineDesFonds']?></td>
        </tr>
              <?php
}
?>
</tbody>  

          <tfoot>
            <tr>
            <td colspan=5 style="border: none;"></td>
            
            <td colspan="1">Total</td>
            <td>/</td>
            <td><?php
                echo $default*$key;
  ?></td>
            </tr> 
          </tfoot>
            </table>
<?php
  }

  echo content();

  if (isset($_POST['genererPdf'])) {
   $mpdf =  new \Mpdf\Mpdf();

$mpdf->AddPage('L');//___Orientation de la page : Landscape
$mpdf->WriteHTML('dsflkdfsl');
$mpdf->Output();
  }
?>
            <div class="imprimer">
              <form>
                <button name="" style="background-color: blue; height: 50px; color: white; margin-left: 40em; margin-top: 100px; cursor: pointer; font-family: poppins;" onclick="window.print()">IMPRIMER OU ENREGISTRER</button>
              </form>
            </div>

<script>

function prepareTable() {
  var str = "",
    header = "",
    graphImg;
  
  
  header = '\uFEFF<h2 style="text-align:center;">Google</h2>';
  
  str = '<table border="1">'
    +'<tr><td style="text-align:center" colspan="6">Yahoo</td></tr>'
    +'<tr><td style="font-weight:bold" colspan="6">(2017.03.20)</td></tr>'
    +'<thead>'
    +'    <tr style="background-color:#788496; color: #ffffff">'
    +'      <th scope="col" rowspan="2">'
    +'        <div>Yahoo</div>'
    +'      </th>'
    +'      <th scope="col">'
    +'        <div class="tar">Yahoo(2017-01)</div>'
    +'      </th>'
    +'      <th scope="col" colspan="2">'
    +'        <div class="tar">Yahoo(2016-12)</div>'
    +'      </th>'
    +'      <th scope="col" colspan="2">'
    +'        <div class="tar">Yahoo(2016-12)</div>'
    +'      </th>'
    +'    </tr>'
    +'    <tr style="background-color:#788496; color: #ffffff">'
    +'      <th height="40" align="right">'
    +'        <div>Yahoo</div>'
    +'      </th>'
    +'      <th align="right">'
    +'        <div>Yahoo</div>'
    +'      </th>'
    +'      <th align="right">'
    +'        <div>Yahoo</div>'
    +'      </th>'
    +'      <th align="right">'
    +'        <div>Yahoo</div>'
    +'      </th>'
    +'      <th align="right">'
    +'        <div>Yahoo</div>'
    +'      </th>'
    +'    </tr>'
    +'</thead>'
    +'  <tbody>'
    
    +'    <tr style="text-align: right">'
    +'      <td style="padding:0 20px 0 0">'
    +'        <div>NAME</div>'
    +'      </td>'
    +'      <td style="width: 150px;">'
    +'        <div>311,210</div>'
    +'      </td>'
    +'      <td style="width: 150px;">'
    +'        <div>311,210</div>'
    +'      </td>'
    +'      <td style="width: 150px;">'
    +'        <div>311,210%</div>'
    +'      </td>'
    +'      <td style="width: 150px;">'
    +'        <div>311,210</div>'
    +'      </td>'
    +'      <td style="width: 150px;">'
    +'        <div>311,210%</div>'
    +'      </td>'
    +'    </tr>';
    +'  </tbody>'
    +'</table>';
        
    return header + str;
}


function doExcel1 () {
  var blob,
    template = prepareTable();

  blob = new Blob([template], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=utf-8"
  });
  
  saveAs(blob, "tests.xls");
}
</script>
<a href="javascript:" class="btn_style1 excel" onclick="doExcel1()"><span>Excel</span></a>








      
</body>
</html>
