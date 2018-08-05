<?php
require "vendor/autoload.php";
require "cookpad/Cookpad.php";
include_once("connect.php");

$cookpad = new Cookpad(new \PHPHtmlParser\Dom);
$cookpad->set('locate', 'id');

if((isset($_GET["id"]))&&(isset($_GET["name"]))) { //Set Nama
	$lineid = $_GET["id"];
	$name = $_GET["name"];
	$result = mysqli_query($mysqli, "INSERT INTO user_name(lineid, name) VALUES('$lineid','$name')");
}

if((isset($_GET["id"]))&&(isset($_GET["tipe"]))) { //Get Pola
	$lineid = $_GET["id"];
	$tipe = $_GET["tipe"];
	$sth = mysqli_query($mysqli, "SELECT age from user_name where lineid = '$lineid'");
	$rows = array();
	while($r = mysqli_fetch_assoc($sth)) {
		$rows[] = $r;
	}
	$age = $rows[0]['age'];
	$age = substr($age,0,1);
	$sth = mysqli_query($mysqli, "SELECT food, benefits from healthy where age = '$age' and type = '$tipe'");
	$hasil = array();
	while($r = mysqli_fetch_assoc($sth)) {
		$hasil[] = $r;
	}
	echo json_encode(array('data' => $hasil));
}

if((isset($_GET["id"]))&&(isset($_GET["age"]))) { //Set Usia
	$lineid = $_GET["id"];
	$age = $_GET["age"];
	$result = mysqli_query($mysqli, "UPDATE user_name SET age='$age' where lineid='$lineid'");
}

if((isset($_GET["id"]))&&(isset($_GET["sex"]))) { //Set Jenis Kelamin
	$lineid = $_GET["id"];
	$sex = $_GET["sex"];
	$result = mysqli_query($mysqli, "UPDATE user_name SET sex='$sex' where lineid='$lineid'");
}

if(isset($_GET['getid'])) { // Get Nama
	$lineid = $_GET["getid"];
	$sth = mysqli_query($mysqli, "SELECT * from user_name where lineid = '$lineid'");
	$rows = array();
	while($r = mysqli_fetch_assoc($sth)) {
		$rows[] = $r;
	}
	echo json_encode(array('data' => $rows));
}

if(isset($_GET['agefilter'])) { //Filter Usia
	$ageStr = $_GET["agefilter"];
	preg_match_all('!\d+!', $ageStr, $age);
	header('Content-Type: application/json');
	echo json_encode(array('data' => $age[0][0]));
}

if(isset($_GET['city'])) { // Get Kota
	$placeid = strtolower($_GET["city"]);
	$sth = mysqli_query($mysqli, "SELECT * from places where nama like '%$placeid%' limit 1");
	$rows = array();
	while($r = mysqli_fetch_assoc($sth)) {
		$rows[] = $r;
	}
	echo json_encode(array('data' => $rows));
}

if(isset($_GET['disease'])) { //Get Makanan Pantangan
	$diseaseid = strtolower($_GET["disease"]);
	$sth = mysqli_query($mysqli, "SELECT disease, food from food where disease like '%$diseaseid%' limit 1");
	$rows = array();
	while($r = mysqli_fetch_assoc($sth)) {
		$rows[] = $r;
	}
	echo json_encode(array('data' => $rows));
}

if(isset($_GET['chat'])) { //Log
	$chat = $_GET['chat'];
	$result = mysqli_query($mysqli, "INSERT INTO chat_logs(chat) VALUES('$chat')");
}

if(isset($_GET["bumil"])) { //Resep Bumil
	$bumil = $_GET["bumil"];
	if($bumil === "1"){
		echo $cookpad->search("bumil",1,0);
	}
}

if(isset($_GET["ageid"])) { //Get Usia
	$lineid = $_GET["ageid"];
	$sth = mysqli_query($mysqli, "SELECT age from user_name where lineid = '$lineid'");
	$rows = array();
	while($r = mysqli_fetch_assoc($sth)) {
		$rows[] = $r;
	}
	$age = $rows[0]['age'];
	echo json_encode(array('age' => $age));
}

if((isset($_GET["id"]))&&(isset($_GET["hitungkalori"]))) { //Hitung Kalori Harian
	$makananStr = strtolower($_GET["hitungkalori"]);
	$makananArr = explode(',',$makananStr);
	$totalKalori = 0;
	foreach($makananArr as $makananStr){
		$ch = curl_init();
		$makananStr = str_replace(" ", "%20", $makananStr);
		curl_setopt($ch, CURLOPT_URL, 'http://209.97.163.246/irta/api/cook/fat.php?srch=' . ltrim($makananStr)); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		$content = trim(curl_exec($ch));
		curl_close($ch);
		$json = json_decode($content, true);
		$begin = strpos($json['data']['gizi'],'Kalori: ') + 8;
		$end = strpos($json['data']['gizi'],'kcal');
		$kalori = (int)(substr($json['data']['gizi'],$begin, $end-$begin));
		$totalKalori += $kalori;
	}
	//echo $totalKalori;
	$lineid = $_GET["id"];
	$sth = mysqli_query($mysqli, "SELECT age, sex from user_name where lineid = '$lineid'");
	$rows = array();
	while($r = mysqli_fetch_assoc($sth)) {
		$rows[] = $r;
	}
	$age = (int)$rows[0]['age'];
	$gender = $rows[0]['sex'];
	if(($age > 50) && ($gender == 'perempuan')){
		if($totalKalori > 2200) $hasil = 'Kamu kelebihan kalori, sebaiknya kurangi mengonsumsi makanan yang tinggi kalori.';
		elseif(($totalKalori > 2000) && ($totalKalori <= 2200)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang aktif untuk membakar kalori.';
		elseif(($totalKalori > 1800) && ($totalKalori <= 2000)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang sedang saja.';
		elseif(($totalKalori >= 1600) && ($totalKalori <= 1800)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang ringan dan banyak beristirahat.';
		elseif($totalKalori < 1600) $hasil = 'Kamu kekurangan kalori. Kekurangan kalori dapat menyebabkan tubuh merasa lelah, rambut rontok dan berat badan tetap sama walaupun diet. Untuk itu sebaiknya kamu mengonsumsi makanan yang tinggi kalori seperti alpukat, kuning telur, susu, kacang, dan minyak zaitun.';
	}elseif((($age < 50) && ($age > 30)) && ($gender == 'perempuan')){
		if($totalKalori > 2200) $hasil = 'Kamu kelebihan kalori, sebaiknya kurangi mengonsumsi makanan yang tinggi kalori.';
		elseif(($totalKalori > 2000) && ($totalKalori <= 2200)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang aktif untuk membakar kalori.';
		elseif(($totalKalori > 1900) && ($totalKalori <= 2000)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang sedang saja.';
		elseif(($totalKalori >= 1800) && ($totalKalori <= 1900)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang ringan dan banyak beristirahat.';
		elseif($totalKalori < 1800) $hasil = 'Kamu kekurangan kalori. Kekurangan kalori dapat menyebabkan tubuh merasa lelah, rambut rontok dan berat badan tetap sama walaupun diet. Untuk itu sebaiknya kamu mengonsumsi makanan yang tinggi kalori seperti alpukat, kuning telur, susu, kacang, dan minyak zaitun.';
	}elseif((($age < 31) && ($age > 18)) && ($gender == 'perempuan')){
		if($totalKalori > 2400) $hasil = 'Kamu kelebihan kalori, sebaiknya kurangi mengonsumsi makanan yang tinggi kalori.';
		elseif(($totalKalori > 2200) && ($totalKalori <= 2400)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang aktif untuk membakar kalori.';
		elseif(($totalKalori > 2000) && ($totalKalori <= 2200)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang sedang saja.';
		elseif(($totalKalori >= 1900) && ($totalKalori <= 2000)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang ringan dan banyak beristirahat.';
		elseif($totalKalori < 1900) $hasil = 'Kamu kekurangan kalori. Kekurangan kalori dapat menyebabkan tubuh merasa lelah, rambut rontok dan berat badan tetap sama walaupun diet. Untuk itu sebaiknya kamu mengonsumsi makanan yang tinggi kalori seperti alpukat, kuning telur, susu, kacang, dan minyak zaitun.';
	}elseif((($age < 19) && ($age > 13)) && ($gender == 'perempuan')){
		if($totalKalori > 2400) $hasil = 'Kamu kelebihan kalori, sebaiknya kurangi mengonsumsi makanan yang tinggi kalori.';
		elseif(($totalKalori > 2200) && ($totalKalori <= 2400)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang aktif untuk membakar kalori.';
		elseif(($totalKalori > 2000) && ($totalKalori <= 2200)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang sedang saja.';
		elseif(($totalKalori >= 1800) && ($totalKalori <= 2000)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang ringan dan banyak beristirahat.';
		elseif($totalKalori < 1800) $hasil = 'Kamu kekurangan kalori. Kekurangan kalori dapat menyebabkan tubuh merasa lelah, rambut rontok dan berat badan tetap sama walaupun diet. Untuk itu sebaiknya kamu mengonsumsi makanan yang tinggi kalori seperti alpukat, kuning telur, susu, kacang, dan minyak zaitun.';
	}elseif((($age < 14) && ($age > 8)) && ($gender == 'perempuan')){
		if($totalKalori > 2200) $hasil = 'Kamu kelebihan kalori, sebaiknya kurangi mengonsumsi makanan yang tinggi kalori.';
		elseif(($totalKalori > 1800) && ($totalKalori <= 2200)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang aktif untuk membakar kalori.';
		elseif(($totalKalori > 1600) && ($totalKalori <= 1800)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang sedang saja.';
		elseif(($totalKalori >= 1500) && ($totalKalori <= 1600)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang ringan dan banyak beristirahat.';
		elseif($totalKalori < 1500) $hasil = 'Kamu kekurangan kalori. Kekurangan kalori dapat menyebabkan tubuh merasa lelah, rambut rontok dan berat badan tetap sama walaupun diet. Untuk itu sebaiknya kamu mengonsumsi makanan yang tinggi kalori seperti alpukat, kuning telur, susu, kacang, dan minyak zaitun.';
	}elseif((($age < 9) && ($age > 3)) && ($gender == 'perempuan')){
		if($totalKalori > 1800) $hasil = 'Kamu kelebihan kalori, sebaiknya kurangi mengonsumsi makanan yang tinggi kalori.';
		elseif(($totalKalori > 1600) && ($totalKalori <= 1800)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang aktif untuk membakar kalori.';
		elseif(($totalKalori > 1400) && ($totalKalori <= 1600)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang sedang saja.';
		elseif(($totalKalori >= 1200) && ($totalKalori <= 1400)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang ringan dan banyak beristirahat.';
		elseif($totalKalori < 1200) $hasil = 'Kamu kekurangan kalori. Kekurangan kalori dapat menyebabkan tubuh merasa lelah, rambut rontok dan berat badan tetap sama walaupun diet. Untuk itu sebaiknya kamu mengonsumsi makanan yang tinggi kalori seperti alpukat, kuning telur, susu, kacang, dan minyak zaitun.';
	}elseif(($age > 50) && ($gender == 'laki-laki')){
		if($totalKalori > 2800) $hasil = 'Kamu kelebihan kalori, sebaiknya kurangi mengonsumsi makanan yang tinggi kalori.';
		elseif(($totalKalori > 2400) && ($totalKalori <= 2800)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang aktif untuk membakar kalori.';
		elseif(($totalKalori > 2200) && ($totalKalori <= 2400)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang sedang saja.';
		elseif(($totalKalori >= 2000) && ($totalKalori <= 2200)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang ringan dan banyak beristirahat.';
		elseif($totalKalori < 2000) $hasil = 'Kamu kekurangan kalori. Kekurangan kalori dapat menyebabkan tubuh merasa lelah, rambut rontok dan berat badan tetap sama walaupun diet. Untuk itu sebaiknya kamu mengonsumsi makanan yang tinggi kalori seperti alpukat, kuning telur, susu, kacang, dan minyak zaitun.';
	}elseif((($age < 50) && ($age > 30)) && ($gender == 'laki-laki')){
		if($totalKalori > 3000) $hasil = 'Kamu kelebihan kalori, sebaiknya kurangi mengonsumsi makanan yang tinggi kalori.';
		elseif(($totalKalori > 2800) && ($totalKalori <= 3000)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang aktif untuk membakar kalori.';
		elseif(($totalKalori > 2400) && ($totalKalori <= 2800)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang sedang saja.';
		elseif(($totalKalori >= 2200) && ($totalKalori <= 2400)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang ringan dan banyak beristirahat.';
		elseif($totalKalori < 2200) $hasil = 'Kamu kekurangan kalori. Kekurangan kalori dapat menyebabkan tubuh merasa lelah, rambut rontok dan berat badan tetap sama walaupun diet. Untuk itu sebaiknya kamu mengonsumsi makanan yang tinggi kalori seperti alpukat, kuning telur, susu, kacang, dan minyak zaitun.';
	}elseif((($age < 31) && ($age > 18)) && ($gender == 'laki-laki')){
		if($totalKalori > 3000) $hasil = 'Kamu kelebihan kalori, sebaiknya kurangi mengonsumsi makanan yang tinggi kalori.';
		elseif(($totalKalori > 2800) && ($totalKalori <= 3000)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang aktif untuk membakar kalori.';
		elseif(($totalKalori > 2600) && ($totalKalori <= 2800)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang sedang saja.';
		elseif(($totalKalori >= 2400) && ($totalKalori <= 2600)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang ringan dan banyak beristirahat.';
		elseif($totalKalori < 2400) $hasil = 'Kamu kekurangan kalori. Kekurangan kalori dapat menyebabkan tubuh merasa lelah, rambut rontok dan berat badan tetap sama walaupun diet. Untuk itu sebaiknya kamu mengonsumsi makanan yang tinggi kalori seperti alpukat, kuning telur, susu, kacang, dan minyak zaitun.';
	}elseif((($age < 19) && ($age > 13)) && ($gender == 'laki-laki')){
		if($totalKalori > 3200) $hasil = 'Kamu kelebihan kalori, sebaiknya kurangi mengonsumsi makanan yang tinggi kalori.';
		elseif(($totalKalori > 2800) && ($totalKalori <= 3200)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang aktif untuk membakar kalori.';
		elseif(($totalKalori > 2600) && ($totalKalori <= 2800)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang sedang saja.';
		elseif(($totalKalori >= 2400) && ($totalKalori <= 2600)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang ringan dan banyak beristirahat.';
		elseif($totalKalori < 2400) $hasil = 'Kamu kekurangan kalori. Kekurangan kalori dapat menyebabkan tubuh merasa lelah, rambut rontok dan berat badan tetap sama walaupun diet. Untuk itu sebaiknya kamu mengonsumsi makanan yang tinggi kalori seperti alpukat, kuning telur, susu, kacang, dan minyak zaitun.';
	}elseif((($age < 14) && ($age > 8)) && ($gender == 'laki-laki')){
		if($totalKalori > 2600) $hasil = 'Kamu kelebihan kalori, sebaiknya kurangi mengonsumsi makanan yang tinggi kalori.';
		elseif(($totalKalori > 2000) && ($totalKalori <= 2600)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang aktif untuk membakar kalori.';
		elseif(($totalKalori > 1800) && ($totalKalori <= 2000)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang sedang saja.';
		elseif(($totalKalori >= 1700) && ($totalKalori <= 1800)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang ringan dan banyak beristirahat.';
		elseif($totalKalori < 1700) $hasil = 'Kamu kekurangan kalori. Kekurangan kalori dapat menyebabkan tubuh merasa lelah, rambut rontok dan berat badan tetap sama walaupun diet. Untuk itu sebaiknya kamu mengonsumsi makanan yang tinggi kalori seperti alpukat, kuning telur, susu, kacang, dan minyak zaitun.';
	}elseif((($age < 9) && ($age > 3)) && ($gender == 'laki-laki')){
		if($totalKalori > 2000) $hasil = 'Kamu kelebihan kalori, sebaiknya kurangi mengonsumsi makanan yang tinggi kalori.';
		elseif(($totalKalori > 1600) && ($totalKalori <= 2000)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang aktif untuk membakar kalori.';
		elseif(($totalKalori > 1400) && ($totalKalori <= 1600)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang sedang saja.';
		elseif(($totalKalori >= 1300) && ($totalKalori <= 1400)) $hasil = 'Kamu sebaiknya melakukan kegiatan yang ringan dan banyak beristirahat.';
		elseif($totalKalori < 1300) $hasil = 'Kamu kekurangan kalori. Kekurangan kalori dapat menyebabkan tubuh merasa lelah, rambut rontok dan berat badan tetap sama walaupun diet. Untuk itu sebaiknya kamu mengonsumsi makanan yang tinggi kalori seperti alpukat, kuning telur, susu, kacang, dan minyak zaitun.';
	}
	echo json_encode(array('total' => $totalKalori, 'hasil' => $hasil));
}

$bahanList = array('bumil','sop','soto','bakso','gulai','semur','tongseng','rawon','lodeh','opor','sayur asem','minuman es','jus','smoothies','minuman cokelat','minuman teh','minuman kopi','minuman tradisional','gorengan','roti','donat','martabak','bakwan','pancake','lumpia','siomay','risoles','pie','kue bolu','brownies','cheesecake','kue tart','kue cokelat','kue lapis','kue cubit','dadar gulung','kue lumpur','kue pukis','nastar','kastengel','kue kacang','putri salju','cookies','lidah kucing','sagu keju','kue semprit','kue bawang','biji ketapang','masakan jawa','masakan sunda','masakan padang','masakan aceh','masakan medan','masakan palembang','masakan bali','masakan banjar','masakan manado','masakan makassar','masakan barat','masakan korea','masakan tiongkok','masakan jepang','masakan thailand','masakan india','masakan timur tengah','telur','kambing','sapi','puyuh','ayam','almond','anggur','apel','aprikot','apokat','ara','asam','atap','arbei','avokad','bacang','belimbing','sayur','bengkuang','benda','beri','emu','bit','binjai','bisbul','blackberry','blackcurrant','blewah','blueberry','burahol','cempaka','cempedak','ceplukan','cermai','ceri','cokelat','cranberry','delima','duku','durian','duren','duwet','enau','erbis','frambos','feijoa','flamboyan','gandaria','gandum','gooseberry','gowok','hazelnut','jagung','jamblang','jambu','air','batu','biji','bol','mawar','mede','semarang','jengkol','jeruk','bali','jepara','keprok','kingkit','nipis','purut','kacang','tanah','kapulasan','kastanye','kawista','kecapi','kedondong','kelapa','kelengkeng','kenari','ketela','kemang','kepel','kersen','kesemek','kiwi','kismis','kokosan','kolang-kaling','kopi','kurma','kates','kenitu','kweni','lai','langsat','lemon','lengkeng','leci','limau','lobak','labu','mahkota','dewa','maja','malaka','mangga','lalijiwa','pari','manggis','markisa','matoa','melon','mengkudu','menteng','mentimun','suri','namnam','nanas','nangka','naga','nektarin','paprika','pomelo','pepaya','persik','pinang','pear','pisang','petai','peria','plum','prune','rambai','raspberry','rambutan','rambusa','red','currant','salak','sawo','kecik','manila','semangka','sirsak','siwalan','srikaya','stroberi','sukun','terap','terong','timun','tomat','tin','talok','ubi','uni','vanili','waluh','widuri','wuni','zaitun','acorn','squash','andewi','artichoke','asparagus','jawa','bawang','bombay','putih','merah','bayam','bendi','brinjal','buncis','hitam','serabut','brokoli','cabai','cendawan','daun','obat','singkong','seledri','escarole','gambas','jalapeno','jamur','bogor','hijau','kapri','kelisa','kuning','panjang','polong','kailan','kangkung','kecambah','kecipir','keledek','kemiri','kentang','ketumbar','kubis','bunga','brussel','kucai','manis','lada','cina','loncang','siam','lembayung','merica','mint','okra','pegaga','petola','pucuk','paku','sawi','selada','sengkuang','serai','terung','jalar','rambat','wortel','zukini','baronang','ekor','hiu','kakap','kerapu','kue','marlin','layaran','tenggiri','teri','tongkol','baung','bawal','belut','gabus','gurami','lele','mas','mujair','nila','nilem','patin','sepat','tawes','demersa','pelagis','tuna','cakalang','karang','lobster','udang','cumi','gulee','itek','eungkot','rambeu','yee','mie','aceh','bu','gureng','dalca','tangkap','sunti','boh','puniaram','nasi','guri','paya','kanji','rumbi','keumamah','martabak','sate','matang','sie','kameng','reuboh','bada','reuteuek','doidoi','karah','kuwah','tuhe','meuseukat','peunajoh','tho','wajeb','sambai','udeung','manok','bak','kala','payeh','kareng','terucroh','peukasam','paeh','bileh','on','peugaga','rampoe','sei','puteh','timpan','roti','cane','jala','teh','tarik','arsik','angsle','babi','panggang','karo','bika','ambon','bolu','gulung','cimpa','cipera','dalini','horbo','ikan','holat','sale','kidu-kidu','kwetiau','lappet','gadong','pohul-pohul','lapis','legit','lemang','lomok-lomok','gomak','mutiara','bagan','siapi-api','nanidugu','naniura','ombus-ombus','pangsit','pok','pia','molen','ketawa','saksang','sambel','hebi','sambal','rias','tuk','sangsang','soto','medan','tanggo-tanggo','terites','tipa-tipa','tok-tok','tuak','uyen','kosong','bulung','gadung','ale-ale','pedas','pop','balado','goreng','beras','rendang','bubur','kampiun','siana','cindua','dadiah','dakak-dakak','dendeng','batokok','es','campur','tebak','galamai','gulai','banak','cubadak','itiak','kambiang','kemumu','pucuak','toco','tambusu','baka','kalio','karak','kaliang','ketan','sarikaya','kipang','keripik','sanjai','kerupuk','jangek','jariang','lontong','pitalah','lamang','tapai','kapau','palai','rinuak','pangek','masin','pensi','pergedel','pinyaram','rakik','maco','padang','pariaman','sagon','bakar','sambalado','tanak','sarang','balam','sarabi','talua','tolu-tolu','wajik','tempoyak','kojo','musibah','sele','nenas','gelamai','perentak','blacan','burgo','8','jam','engkak','celimpungan','empek-empek','pempek','kapal','selam','lenjer','lenggang','kulit','adaan','tahu','pistel','juadah','kemplang','lempok','tekwan','model','mi','celor','lepat','lakso','maksuba','srikayo','kumbu','laksan','malbi','pindang','daging','tulang','brengkes','gangan','pakasam','peda','rusip','pais','salai','kasam','kluntup','lingkung','mbacang','minyak','samin','lemar','gendar','bangket','juice','mata','pengantin','laksamana','mengamuk','kemojo','kamboja','rujak','maharaja','belacan','tiga','rasa','seruwit','sekubal','buak','balak','taboh','gelemok','lambang','sari','taoge','emping','bandeng','otak-otak','sumsum','rabeg','jojorong','cuwer','gipang','apem','keceprek','melinjo','bebek','gemblong','semur','lemeng','betawi','kerak','telor','sop','kaki','serani','pesalak','pucungcak','pecak','gurame','pelas','besan','gado-gado','boplo','doger','teler','telubuk','pesmo','talam','begana','daon','uduk','ketoprak','jongkong','unti','bukhari','khas','kroket','bir','pletok','bebeg','dodol','buaya','asinan','jakarta','geplak','ongol-ongol','rangi','risoles','marunda','capsay','kimlo','marak','bagane','kebuli','bebanci','serondeng','pepe','gila','onde','-','karedok','bandung','batagor','bakso','kocok','perkedel','bondon','maicih','lotek','serabi','combro','misro','timbel','pepes','oncom','peuyeum','cireng','gepuk','ambokueh','colenak','brownies','kukus','sus','suzanna','bala-bala','gehu','bandrek','bajigur','goyobod','cendol','sakoteng','kolontong','sumedang','sukasari','tauco','cianjur','toge','manisan','kupat','tutug','tasik','bakmi','babat','kremes','bulat','gedung','gincu','gula','cakar','tetel','hucap','ladu','garut','dorokdok','sega','lengko','empal','gentong','asem','gejrot','docang','koclok','cirebon','mlarat','laksa','tauge','jasinga','unyil','mochi','sukabumi','bunut','jahe','tangkar','opak','rangginang','gegeplak','semprong','hideung','ali','maranggi','sadang','ciganea','simping','bendul','aren','cikeris','pala','lumpia','pong','gimbal','sego','presto','petis','tite','gudeg','koyor','jenang','telo','swiekee','kuah','ungaran','gandul','salatiga','enting-enting','wingko','getuk','tape','aci','pilus','antor','bogares','lebaksiu','tegal','poci','gopek','dawet','asin','brebes','itik','glabed','kerang','sroto','sokaraja','gethuk','tempe','mendoan','lanting','ambal','jaket','nopya','mino','megono','pekalongan','adon-adon','coro','gempol','horok-horok','pecel','laut','tempong','kikil','oven','carang','madu','kudus','lentog','taoto','garang','grombyang','kelo','mrico','tuyuan','dumbek','blora','rawon','ndoreng','wedang','pekak','botok','liwet','areh','bacem','timlo','intip','usus','tengkleng','toprak','tumpang','pleret','ampyang','notosuman','semar','mendem','sosis','solo','dongo','gading','buntel','balap','srabi','cabuk','rambak','selat','langgi','gule','mandarin','abon','varia','ijo','tongseng','semir','mendhem','jadah','blondo','acar','gesing','ayu','plered','puter','kawis','cao','kencur','serbat','abangan','alen','godog','bakmoy','bakpia','suwar-suwir','beer','djawa','bunthil','cemplon','cenil','age','gaplek','gathot','geblek','growol','gudangan','manggar','manten','jamu','kunir','gronthol','rumput','sungsum','juwawut','kipo','klepon','jos','arang','legomoro','lempeng','legendar','trubus','mangut','kebo','mega','mendung','mendut','tektek','magelang','kucing','wiwit','ndog','gludug','onde-onde','opor','peyek','bayem','dele','jingking','rengginang','jok','degan','kota','gede','krecek','tolo','klathak','kocor','winong','abang','mesem','benguk','gembus','koro','timus','thiwul','trancam','trasikan','tumpeng','urap','ronde','secang','uwuh','yangko','surabaya','cingur','uleg','legi','semanggi','tek','cucur','ponorogo','lamongan','lodeh','kediri','bakiak','phitik','kesrut','jangan','pakis','koyong','lemuru','pelasan','uceng','awok','bothok','orok','kucur','nogo','bikang','lempog','lemper','bungkuk','lemet','lanun','lupis','blendung','jemblem','sawot','pukis','cemplong','kecut','lethok','gobet','ragi','pare','gerang','gembos','nus','kelor','madiun','brem','kelinci','jerangking','gado','gobyos','bata','anget','pottre','nyellem','nom','aeng','pokak','syarifah','sekojo','lepet','malang','bakwan','puthu','lanang','mawut','cwie','stmj','mocca','krawu','blawu','rempih','danggur','wa','keningar','penyet','karet','becek','boranan','suwar','suwir','ledre','pedda','manto','keponakan','gangsa','maghadip','musawaroh','putre','nyelem','nomoeng','podak','dolar','bongko','tajin','shobih','dudul','cingu','nyen','onyen','pokeh','mera','betutu','guling','bandot','be','kokak','mekuah','pasih','mesambel','matah','berengkes','grangasem','jejeruk','jukut','ares','srombotan','urab','komoh','lawar','bubuh','tepeng','penyon','kakul','kablet','lilit','pentul','penyu','tusuk','timbungan','jenggo','tum','sagu','jaja','batun','begina','bendu','engol','godoh','jongkok','ketimus','lak-lak','sumping','tain','buati','uli','misi','rahayu','tibah','taliwang','bulayak','plecing','bingka','dolu','ale','bie','pang','cah','ganepuk','dorong','pontianak','kwetiauw','siram','ca','kwe','tar','piring','lidah','dangi','peletuk','masak','pekasam','popat','pelam','pekating','kecap','basah','amparan','tatak','apam','barabai','baras','parangge','balungan','hayam','barandam','gunting','cangkarok','karing','dadar','gagatas','gagati','gaguduh','garigit','gumpal','hintalu','karuang','ipau','pipilan','bajarang','kikicak','kokoleh','lam','lakatan','bahintti','lalampar','lapat','sapat','patah','pupudak','putri','putu','mayang','rangai','rangkasusun','sarimuka','pangantin','slada','gumbili','topi','baja','ulin-ulin','untuk','upak','wadai','gayam','satu','gadang','ganngan','haliling','humbut','kaladi','karuh','ludeh','luncar','rabung','garih','batanak','kalakai','batumis','kambang','tigaron','bajaruk','karih','tarung','babanam','basantan','tumis','using-using','hundang','papai','habang','biawan','baubar','buras','hampap','haruan','kering','jaruk','kalangkala','lumbu','bapais','tarap','katupat','kandangan','lampam','mandai','pundut','papuyu','basanga','saluang','banjar','juhu','umbut','rotan','sawit','tangkiling','buah-buahan','bukang-bukang','pengat','rojak','cabe','keladi','santan','tungkul','gence','ruan','jalo','kelubutan','bella','keat','kamun','kerubut','salab','kepiting','bekepor','lalap','tarakan','sobot','tirom','cumi-cumi','sagal','gamik','kima','raja','mentah','singkil','semor','bola-bola','lemangu','balelo','berbumbu','saus','ala','singapura','abuh','akar','sampai','amplang','amperan','tetak','balembar','babok','bahui','dalam','basong','berongkong','susu','cincin','coplok','kertap','elat','gagodoh','spesial','mesir','gegaok','getas','getok','kararaban','kelepon','keminting','kericak','agar-agar','lemuko','silat','lo','ulung','kasirat','susun','nyahun','nyangau','lok','pepare','podang','pulut','gogos','sokok','rebus','besumap','sanggar','muka','suman','keruang','terang','bulan','temu','kunci','tumpi','angin','ufak','untuk-untuk','apang','coe','bagea','balapis','biapong','jenever','binyolos','bluder','brudel','bobengka','halua','goyang','kawangkoan','klappertaart','kolomben','koyabu','kukis','lalampa','lampu','panada','popaco','susen','rica-rica','isi','di','bulu','paniki','putar','tore','rica','fufu','dabu','lilang','tikus','ular','patola','monyet','midaal','bungkus','jaha','pampis','nike','roa','rw','kolombi','sayor','leilem','pangi','ganemo','daong','popaya','sup','brenebon','tinutuan','tinorangsak','tuturuga','woku','belanga','gohu','cap','pinaraci','saguer','paranggi','bannang','barongko','baruasa','bassang','beppa','janda','biji-biji','bipang','peca','rampah','cucuru','bayao','dange','deppa','tori','kau','sawalla','nagasari','doko','cangkuling','cangkuning','golla','ganepo','tare','toraja','jalang','kote','sinjai','katri','sala','mandi','palu','tawaro','pallu','butung','paserrek','epe','cangkiri','pawa','sakko','tello','penyyu','taripang','kambu','camme','burak','coto','makassar','dangke','kadonteng','kaladde','kapurung','lawa','nande','dalle','nasu','cemba','to','salukanan','basa','kacci','kaloa','mara','sura','piong','sarondeng','songkolo','konro','saudara','ballotuak','kawah','soami','kambalu','luluta','gule-gule','susuru','colo-colo','papeda','lapola','maluku','dengan','ganemu','ulang-ulang','rampai','puding','kohu-kohu','kasbi','manokwari','aunu','anuve','habre','senebre','eurimoo','bihun','kari','jambal','mangkuk','perut','kuk','tepung','ceylon');

if(isset($_GET["bahan"])) {
	$bahanGet = " ".strtolower($_GET["bahan"]);
	foreach ($bahanList as $bahan){
		if(strpos($bahanGet, $bahan)){
			echo $cookpad->search($bahan,1,0);
			break;
		}
	}
}
?>