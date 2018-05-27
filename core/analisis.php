<?php
function stem($txt){
	$input = filter_var(strtolower($txt), FILTER_SANITIZE_STRING);
	$result = preg_replace("/[^a-zA-Z0-9 ]/", "", $input);
	$pecah = explode(" ",$result);
	foreach($pecah as $item){
		if(strlen($item) > 0){
			$hasil = nazief($item);
			$save[] = $hasil;
		}
	}

	if(empty($save))
		return false;

	return stopword($save);

}

function stopword($arr){
	global $db;

	if(count($arr) > 0){
		$imp = implode("','",$arr);
		$query = $db->query("SELECT stopword FROM stopword_list WHERE stopword IN ('$imp')");
		if($query->rowCount() > 0){
			foreach($query as $ck){
				foreach (array_keys($arr, $ck['stopword'], true) as $key) {
				    unset($arr[$key]);
				}
			}
		}
		return $arr;
	}
	else{
		return false;
	}
}

function get_data_latih($arr){
	global $db;

	$addQuery = "";
	foreach($arr as $st){
		$addQuery .= "(stem LIKE ".$db->quote("$st,%")." OR stem LIKE ".$db->quote("%,$st,%")." OR ".$db->quote(",$st%").") OR ";
	}
	$addQuery = substr($addQuery, 0, -4);
	$get_data_latih = $db->query("SELECT * FROM vw_komentar WHERE $addQuery");
	$n = 1;
	$send[0] = null;
	$sentimen[0] = null;
	foreach($get_data_latih as $row){
		$item = explode(",",$row['stem']);
		$sentimen[$n] = $row['sentimen'];
		$send[$n] = $item;
		$n++;
	}

	if($n == 1){
		//alias tidak ada perubahan
		$send = null;
		$sentimen = null;
	}
	return array(
		"item" => $send,
		"sentimen" => $sentimen
	);
}

function update_database(){
	global $db;
	$sql = $db->query("SELECT * FROM skripsi_komentar");
	$upd = "";
	foreach($sql as $row){
		$stem = stem($row['komentar']);
		$imp = "";
		if(count($stem) > 0){
			$imp = implode(",",$stem);
		}
		$upd .= "UPDATE skripsi_komentar SET stem = ".$db->quote($imp)." WHERE no = ".$db->quote($row['no'])."; ";
	}
	$run = $db->query($upd);
	return true;
}

function create_token($data){
	$token = array();
	foreach($data['item'] as $itm){
		$token = array_merge($token, array_values($itm));
	}
	$token = array_unique($token);
	return $token;
}

function cari_tf($token, $data){
	$tf = array();
	foreach($token as $kata){
		foreach($data as $key=>$value){
			$val = array_count_values($value);
			if(isset($val[$kata])){
				$tf[$kata][$key] = $val[$kata];
			}
			else{
				$tf[$kata][$key] = 0;
			}
		}
	}
	return $tf;
}

function cari_df($tf){
	//menghitung jumlah kemunculan kata dalam dokumen
	foreach($tf as $key=>$value){
		$df = 0;
		$n = count($value);
		for($i=1;$i<$n;$i++){
			if($value[$i] > 0)
				$df++;
		}

		$retdf[$key] = $df;
	}
	return $retdf;
}

function hitung_bobot($tf, $idf){
	$bobot = array();
	foreach($idf as $key=>$value){
		//just try
		if(!isset($tf[$key])){
			$n = 0;
		}
		else{
			$n = count($tf[$key]);
		}
		for($i=0;$i<$n;$i++){
			if(!isset($bobot[$i]))
				$bobot[$i] = 0;
			$bobot[$i] += ($tf[$key][$i] * $value);
		}
	}
	return $bobot;
}

function hitung_jarak($x1, $y1){
	$n = count($y1);
	for($i=0;$i<$n;$i++){
		$jarak[$i] = abs($x1-$y1[$i]);
	}
	return $jarak;
}

function bagi_cluster($jarak1, $jarak2){
	$n = count($jarak1);
	$c1 = array();
	$c2 = array();

	for($i=0;$i<$n;$i++){
		if($jarak1[$i] < $jarak2[$i])
			$c1[] = $i;
		else
			$c2[] = $i;
	}

	return array("c1" => $c1, "c2" => $c2);
}

function means($index, $bobot){
	$sum = 0;
	$n = 0;
	foreach($index as $key=>$value){
		$sum += $bobot[$value];
		$n++;
	}

	$means = ($n == 0) ? 1 : $sum / $n;

	return $means;
}

function cari_sentimen($cluster, $sentimen, $pusat, $bobot, $debug=false){
	//golongkan nilai sentimen yang ada di dalam cluster
	$positif = 0;
	$negatif = 0;
	foreach($cluster as $c){
//		if($c <> 0){
			if($sentimen[$c] == 1){
				$positif++;
			}
			else{
				$negatif++;
			}
//		}
	}

	if($debug){
		echo "
		<span class='label label-success'>Data positif : $positif</span>
		<span class='label label-danger'>Data negatif : $negatif</span>
		<br>
		";
	}



	//Metode K-NN ditentukan di baris ini.
	//jika ingin dijalankan secara default, hapus baris IF dibawah ini.
	if($positif == $negatif){
		//kalau jumlah sentimen positif dan negatifnya sama, maka sentimennya adalah data terdekat
		foreach($cluster as $c){
			$jarak[$c] = abs($pusat-$bobot[$c]);
			if($debug)
				echo "Jarak <strong>K-$c</strong> ke pusat data = ".$pusat." - ".$bobot[$c]." = <strong>".$jarak[$c]."</strong><br>";
		}
		$jarak_min = array_keys($jarak, min($jarak));
		$hasil = $sentimen[$jarak_min[0]];

		if($debug){
			if($hasil==0)
				$cl = "danger";
			else
				$cl = "success";
			echo "Sentimen ditentukan berdasarkan jarak terdekat yaitu di <span class='label label-$cl'>K-".$jarak_min[0]."</span><br>";
		}

		if($hasil == 1)
			$positif++;
		else
			$negatif++;
	}


	if($positif > $negatif){
		return 1;
	}
	else{
		return 0;
	}
}


function knn($cluster, $sentimen, $pusat, $bobot, $debug=false){
	//golongkan nilai sentimen yang ada di dalam cluster
	$positif = 0;
	$negatif = 0;
	foreach($cluster as $c){
		if($c <> 0){
			if($sentimen[$c] == 1){
				$positif++;
			}
			else{
				$negatif++;
			}
		}
	}

	if($debug){
		echo "
		<span class='label label-success'>Data positif : $positif</span>
		<span class='label label-danger'>Data negatif : $negatif</span>
		<br>
		";
	}



	//jalan tengah
	//kalau selisih data positif dan negatif tidak lebih dari 6.66%, maka KNN baru dijalankan untuk mencari tetangga terdekat
	//selebihnya, sentimen kebanyakan dalam sebuah cluster seharusnya sudah mewakili.
	$total_coba = $positif + $negatif;
	$selisih_coba = abs($positif - $negatif);
	if($selisih_coba < ($total_coba / 15)){

		//Metode K-NN ditentukan di baris ini.
		$jarak = array();
		foreach($cluster as $c){
			//jadikan data uji sebagai pusat data
			if($c == 0){
				$pusat = $bobot[$c];
				continue;
			}
			$jarak[$c] = abs($pusat-$bobot[$c]);
			if($debug)
				echo "Jarak <strong>K-$c</strong> ke pusat data = ".$pusat." - ".$bobot[$c]." = <strong>".$jarak[$c]."</strong><br>";
		}

		if(count($jarak) > 0){
			//nggak ada data apapun di cluster tersebut
			$jarak_min = array_keys($jarak, min($jarak));
			$hasil = $sentimen[$jarak_min[0]];

			if($hasil==0)
				$cl = "danger";
			else
				$cl = "success";

			$dbug_text = "Sentimen ditentukan berdasarkan jarak terdekat yaitu di <span class='label label-$cl'>K-".$jarak_min[0]."</span><br>";
		}
		else{
			$hasil = -1;
			$dbug_text = "Tidak ada data apapun yang dapat dijadikan dasar penentuan sentimen.";
		}

	}
	else{
		if($positif > $negatif)
			$hasil = 1;
		else
			$hasil = 0;

		if($positif == 0 and $negatif == 0){
			$hasil = -1;
		}
		$dbug_text = "Metode KNN tidak dijalankan karena mengikuti sentimen terbanyak di cluster tersebut";
	}


	if($debug){
		echo $dbug_text;
	}

	return intval($hasil);
}










function revise($id){
	global $db;
	$cek = $db->query("SELECT * FROM skripsi_rekap WHERE no = ".intval($id)." AND flag = 0");

	if($cek->rowCount() >= 1){
		$row = $cek->fetch();
		if($row['sentimen'] == 0)
			$to = 1;
		else
			$to = 0;

		$upd = $db->query("UPDATE skripsi_rekap SET flag = 1, sentimen = $to WHERE no = ".intval($id));
	}

	return true;
}