<?php
function dump($var, $multiple=false){
		echo "<textarea style='width:100%; height:400px'>";
		if($multiple == true){
			foreach($var as $itm){
				print_r($itm);
				echo "\n\n\n";
			}
		}
		else
			var_dump($var);
		echo "</textarea>";
}

function is_same($a, $b, $out = ""){
	if($a == $b){
		echo $out;
	}
}

function get_extension($filename){
	$exp = explode(".",$filename);
	$n = count($exp);
	return strtolower($exp[$n-1]);
}

function single_process($s){

	$string = stem($s);
	if($string){
		$data = get_data_latih($string);
		$data['item'][0] = $string;

		$kata = $data['item'];
		$sentimen = $data['sentimen'];

		$jumlah_data = count($kata) - 1;
		$token = create_token($data);
		$tf = cari_tf($token, $kata);
		$df = cari_df($tf);

		foreach($tf as $kk=>$vv){//sori kalau nama variabelnya ga jelas
			$ddf[$kk] = ($df[$kk] == 0) ? 1 : ($jumlah_data / $df[$kk]);
			$idf[$kk] = log10($ddf[$kk]);
		}

		$bobot = hitung_bobot($tf, $idf);

		//Masuk ke metode K-Means
		$pusat1 = $bobot[0];
		$jarak1 = hitung_jarak($pusat1, $bobot);
		$jarak_max = array_keys($jarak1, max($jarak1));

		$pusat2 = $bobot[$jarak_max[0]];
		$jarak2 = hitung_jarak($pusat2, $bobot);

		$bagi = bagi_cluster($jarak1, $jarak2);

		$c1_temp = $bagi["c1"];
		$c2_temp = $bagi["c2"];

		//selesai perhitungan pertama
		$max_iterasi = 500;

		$sama = 0;
		for($i=0;$i<$max_iterasi;$i++){
			$pusat1 = means($c1_temp, $bobot);
			$jarak1 = hitung_jarak($pusat1, $bobot);
			$pusat2 = means($c2_temp, $bobot);
			$jarak2 = hitung_jarak($pusat2, $bobot);


			$cluster = bagi_cluster($jarak1, $jarak2);

			if(count($cluster["c1"]) == count($c1_temp)){
				if($sama == ceil($max_iterasi/10)){
					break;
				}
				else{
					$sama++;
				}
			}
			else{
				$sama = 0;
			}

			$c1_temp = $cluster["c1"];
			$c2_temp = $cluster["c2"];

		}

		//sampai disini perulangan berakhir
		//cari cluster dengan value 0 ada dimana
		if(in_array(0, $cluster["c1"])){
			$c_final = $cluster["c1"];
			$pusat = $pusat1;
			$outp = "Cluster 1";
		}
		else{
			$c_final = $cluster["c2"];
			$pusat = $pusat2;
			$outp = "Cluster 2";
		}

		$final_sentiment = knn($c_final, $sentimen, $pusat, $bobot);

		return $final_sentiment;

	}
	else{
		return -1;
	}

	return $final_sentiment;
}