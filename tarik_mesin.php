<?php
include("config.php");
$IP="192.168.89.5"; //Isi IP Mesin Absensi X401
$Key="0"; //Isi Key Mesin Absensi X401 || Defaultnya 0
if($IP=="") $IP="192.168.89.5"; 
if($Key=="") $Key="0";  


    $Connect = fsockopen($IP, "80", $errno, $errstr, 1); //Untuk open port dari ip mesin absensi
    if($Connect){ //Jika Connect 
        $soap_request="<GetAttLog>
                            <ArgComKey xsi:type=\"xsd:integer\">".$Key."</ArgComKey>
                            <Arg><PIN xsi:type=\"xsd:integer\">All</PIN></Arg>
                        </GetAttLog>";
     
     

        $newLine="\r\n";
        fputs($Connect, "POST /iWsService HTTP/1.0".$newLine);
        fputs($Connect, "Content-Type: text/xml".$newLine);
  fputs($Connect, "Content-Length: ".strlen($soap_request).$newLine.$newLine);
        fputs($Connect, $soap_request.$newLine);
        $buffer="";
        while($Response=fgets($Connect, 1024)){
            $buffer=$buffer.$Response;
        }
    }else echo "Koneksi Gagal";
 $buffer=Parse_Data($buffer,"<GetAttLogResponse>","</GetAttLogResponse>");  //Parsing Data
    $buffer=explode("\r\n",$buffer);
    for($a=0;$a<count($buffer);$a++){
        $data=Parse_Data($buffer[$a],"<Row>","</Row>");
     
        $pin=Parse_Data($data,"<PIN>","</PIN>");
        $datetime=Parse_Data($data,"<DateTime>","</DateTime>");
        $status=Parse_Data($data,"<Status>","</Status>");
$cekdulu= "select * from rekap where id='$pin' and waktu='$datetime' "; //Cek Duplikasi Pada Sistem
$niss1 = "select nis from siswa where id_siswa= '$pin'";
 $prosescek= mysqli_query($koneksi,$cekdulu);
  // if ($prosescek) { //proses mengingatkan data sudah ada
  // echo "<script>alert('Username Sudah Digunakan');history.go(-1) </script>";
  // }
  // else { //proses menambahkan data, tambahkan sesuai dengan yang kalian gunakan
   $sql = "INSERT INTO rekap (id, waktu, nis, status, tanggal) values ('$pin','$datetime','$niss1',$status', '$datetime')"; //Insert Data Mesin Absensi ke Sistem 
   mysqli_query($koneksi,$sql);
  //}
  ini_set('max_execution_time', 300); //Durasi MAX Eksekusi
     
    }
echo "<script>alert('Selesai tarik data absen'); </script>";

function Parse_Data ($data,$p1,$p2) {
  $data = " ".$data;
  $hasil = "";
  $awal = strpos($data,$p1);
  if ($awal != "") {
    $akhir = strpos(strstr($data,$p1),$p2);
    if ($akhir != ""){
      $hasil=substr($data,$awal+strlen($p1),$akhir-strlen($p1));
    }
  }
  return $hasil; 
}
?>
