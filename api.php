<?php
require_once("core/koneksi.php");
require_once("core/function.php");
require_once("core/nazief.php");
require_once("core/analisis.php");

$out = array();
if(isset($_GET['q'])){
	$sentimen = single_process($_GET['q']);

	if($sentimen == -1){
		$out['input'] = $_GET['q'];
		$out['error'] = true;
		$out['message'] = "Kalimat tersebut memiliki sentimen netral.";
		$out['sentiment'] = -1;
	}
	else{
		//jangan lupa simpan ke tabel log untuk analisa selanjutnya kalau data belum ada


		$stem = stem($_GET['q']);
		$imploded = implode(",",$stem);

		$cek = $db->query("SELECT * FROM skripsi_rekap WHERE stem = ".$db->quote($imploded));
		if($cek->rowCount() == 0){
			$sv = $db->prepare("INSERT INTO skripsi_rekap VALUES (NULL, :a, :b, :c, 0)");
			$sv->bindParam(":a",$_GET['q']);
			$sv->bindParam(":b",$imploded);
			$sv->bindParam(":c",$sentimen);
			$sv->execute();
			$last_id = $db->lastInsertId();			
		}
		else{
			$row = $cek->fetch();
			$last_id = $row['no'];
		}

		$out['input'] = $_GET['q'];
		$out['error'] = false;
		if($sentimen==1){
			$msg = "positif";
		}
		else{
			$msg = "negatif";
		}

		$out['message'] = "Kalimat tersebut memiliki sentimen $msg .";
		$out['sentiment'] = $sentimen;
		$out['unique_id'] = $last_id;
	}

	echo json_encode($out);
}

else if(isset($_GET['revise'])){
	$cek = $db->query("SELECT * FROM skripsi_rekap WHERE no = ".intval($_GET['revise']));
	if($cek->rowCount() > 0){
		$get = $cek->fetch();
		$senti = $get['sentimen'];
		if($senti == 0){
			$chg = 1;
			$chgg = "positif";
		}
		else{
			$chg = 0;
			$chgg = "negatif";
		}

		$upd = $db->query("UPDATE skripsi_rekap SET flag = 1,  sentimen = $chg WHERE no = ".intval($_GET['revise']));

		$out['error'] = false;
		$out['message'] = "Berhasil memperbaiki hasil analisis tersebut menjadi $chgg";
		$out['new'] = $chg;
	}
	else{
		$out['error'] = true;
		$out['message'] = "Tidak ditemukan rekap analisis dengan id sekian";
	}
	echo json_encode($out);
}

else if(isset($_GET['id'])){
	//cari unique analysis ID
	$cek = $db->query("SELECT * FROM skripsi_rekap WHERE no = ".$db->quote(intval($_GET['id'])));
	if($cek->rowCount() > 0){
		//data ada
		$row = $cek->fetch();
		if($row['sentimen'] == 0)
			$msg = "negatif";
		else
			$msg = "positif";

		$out['error'] = false;
		$out['id'] = intval($_GET['id']);
		$out['message'] = "Kalimat tersebut menghasilkan sentimen $msg";
		$out['input'] = $row['komentar'];
		$out['sentimen'] = $row['sentimen'];
		$out['stem'] = $row['stem'];
		$out['flag'] = $row['flag'];
	}
	else{
		$out['error'] = true;
		$out['id'] = intval($_GET['id']);
		$out['message'] = "Tidak ditemukan rekap analisis dengan ID ".intval($_GET['id']);
	}

	echo json_encode($out);
}

