<?php


if (!isset($_GET['crypt'])) {
	die('<br>Valore del parametro crypt non definito');
}


if (!isset($_GET['test'])) {
	die('<br>Valore del parametro test non definito');
}


if (!isset($_GET['proc'])) {
	die('<br>Valore del parametro proc non definito');
}

require_once('./conn.php');


function base64url_encode($str) {
    return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
}


function generate_jwt($headers, $payload, $secret = 'secret') {
	$headers_encoded = base64url_encode(json_encode($headers));
	
	$payload_encoded = base64url_encode(json_encode($payload));
	
	$signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);
	$signature_encoded = base64url_encode($signature);
	
	$jwt = "$headers_encoded.$payload_encoded.$signature_encoded";
	
	return $jwt;
}



//   http://smartwebi:300/Default.aspx?proc=3&crypt=8cc85f4258c91021eb11bad738095187


// 	 https://amiugis.amiu.genova.i/idp_from_intranet/check.php?test=0&proc=1&crypt=8cc85f4258c91021eb11bad738095187


$user=$_GET['crypt']; // leggo l'utente criptato dalla intranet

$proc=$_GET['proc'];
//echo $user."<br>";


$query_proc="SELECT id, url from utils.proc WHERE id=$1;";
// Prepare a query for execution
$result0 = pg_prepare($conn, "my_query0", $query_proc);
// Execute the prepared query. 
$result0 = pg_execute($conn, "my_query0", array($proc));


// faccio un ciclo sugli utenti
while($r0 = pg_fetch_assoc($result0)) {
	$url0=$r0["url"];
}
//echo $url0."<br>";

$chek=0; // parametro di controllo impostato a 0

$query_user="SELECT id, intranet_username from utils.users WHERE id_proc=$1";
// Prepare a query for execution
$result1 = pg_prepare($conn, "my_query1", $query_user);
// Execute the prepared query. 
$result1 = pg_execute($conn, "my_query1", array($proc));

//echo $query_user."<br>";
//echo $proc."<br>";
// faccio un ciclo sugli utenti
while($r1 = pg_fetch_assoc($result1)) {

	$data=$r1["intranet_username"];
	$id_user=$r1["id"];
	//echo $data."<br>";
	$hash=hash(md5, $data);

	// Print the generated hash
	//echo "Generated hash: ".$hash;
	//echo "<br>";
	//echo "Crypt è". $user."<br>";
	
	
	if ($user==$hash){
		$check=1;
	
		$issuedAt   = new DateTimeImmutable();
		$expire     = $issuedAt->modify('+420 minutes')->getTimestamp();
	
	
		$headers = array('alg'=>'HS256','typ'=>'JWT');
		$payload = array('role'=>'ADMIN',
						'name'=>'r.marzocchi',
						'iat'  => $issuedAt->getTimestamp(),         // Issued at: time when the token was generated
						'nbf'  => $issuedAt->getTimestamp(),
						//'exp'	=>(time() + 60)
						'exp'  => $expire,                           // Expire
					);
	
		$jwt = generate_jwt($headers, $payload);
	
		//echo $jwt;

		$query_log="INSERT INTO utils.log_accessi
		(id_user, id_proc)
		VALUES($1, $2);";
		// Prepare a query for execution
		$result2 = pg_prepare($conn, "my_query2", $query_log);
		// Execute the prepared query. 
		$result2 = pg_execute($conn, "my_query2", array($id_user, $proc));


		$url=$url0."?token=".$jwt;
		die("Qua faccio il redirect alla pagina<br><br>".$url);
		//header("Location:$url");

	}
}

if ($chek==0) {
	
	$query_log1="INSERT INTO utils.log_accessi_negati
		(crypt_user, id_proc)
		VALUES($1, $2);";
		// Prepare a query for execution
		$result2 = pg_prepare($conn, "my_query3", $query_log1);
		// Execute the prepared query. 
		$result2 = pg_execute($conn, "my_query3", array($user, $proc));
	


	?>
	<!DOCTYPE html>
	<html lang="en">

	<head>

		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="roberto" >

		<title>Utente non autorizzato</title>
	<?php 
	require_once('./req.php');	
	?>

</head>

<body>

<?php require_once('./navbar_up.php');?>


    <div class="container">
		Utente NON autorizzato

</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>
	<?php
	
}

?>