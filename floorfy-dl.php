<?PHP

if (count($argv)!=2)  { echo ("usage: php ./floorfy-dl.php <url floorfy virtual tour>\n"); exit(0); }
$url = $argv[1];

$floorfy = new Floorfy($url);

$floorfy->download();

echo("done\n");
echo("Support future improvements of this software https://www.buymeacoffee.com/fdd4s\n");


class Floorfy {
	var $id;
	var $url;
	var $validId;
	var $http_referer;
	var $baseUrl;
	var $urlList;

	public function download() {
		if ($this->validId==false) return;
		echo("url ".$this->url."\n");
		$urlHtml = new Url($this->url, "", "");
		$urlHtml->download();
		$urlJson = new Url($this->getUrlJson($urlHtml->data), "", "");
		$urlJson->download();
		$this->parseConfigJson($urlJson->data);
	}

	public function getUrlJson($data) {
		$urlJson = "https://cdn.floorfy.com/".$this->strBtn($data, '<input id="configpath" type="hidden" value="/', '"');
		$arr = explode("?", $urlJson);
		$urlJson2 = $arr[0];
		$this->baseUrl = str_replace("config.json", "", $urlJson2);
		return $urlJson2;
	}

	private function strBtn($str_content, $str_start, $str_end) {
		$pos1 = strpos($str_content, $str_start);
		if ($pos1===FALSE) return "";
		$pos1 = $pos1 + strlen($str_start);

		$pos2 = strpos($str_content, $str_end, $pos1);
		if ($pos2===FALSE) return "";

		$res_len = $pos2 - $pos1;
		$res = substr($str_content, $pos1, $res_len);

		return $res;
	}

	public function parseConfigJson($data) {
		file_put_contents(dirname(__FILE__)."/cfg_".$this->id.".json", $data);

		$data2 = $data;
		$data2 = str_replace('\/equirectangular', "skybox", $data2);
		$data2 = str_replace("equirectangular", "skybox", $data2);
		$data2 = str_replace("panorama", "panorama2", $data2);
		$data2 = str_replace("thumbnail", "thumbnail2", $data2);
		$data2 = str_replace("title", "title2", $data2);
		$data2 = str_replace("author", "author2", $data2);
		$data2 = str_replace("Floorfy", "", $data2);
		$data2 = str_replace("Rotate", "RotateNO", $data2);
		$data2 = str_replace("Fade", "FadeNO", $data2);
		$data2 = str_replace('\/fallback\/', "_", $data2);

		file_put_contents(dirname(__FILE__)."/vtour.json", $data2);


		$json_arr = json_decode($data, true);
		$ids = array();

		$isJpeg = false;

		foreach($json_arr['scenes'] as $scene) {
			$ids[] = $scene['id'];
			if (!$isJpeg && substr($scene['panorama'], -4)=="jpeg") $isJpeg = true;
		}

		$this->makeUrlList($ids, $isJpeg);
	}

	public function makeUrlList($ids, $isJpeg) {
		$cube = "f.b.l.r.u.d";
		$cubeFaces = explode(".", $cube);
		foreach($ids as $id) {
			$url = $this->baseUrl.$id;
			if ($isJpeg==false) $url.=".jpg"; else $url.=".jpeg"; 
					
			$name = "equi_".str_replace("equirectangular_", "", $id).".jpg";

			$this->urlList->add($url, $name, "e");

			foreach($cubeFaces as $cubeFace) {
				$url = $this->baseUrl.$id."/fallback/".$cubeFace.".jpg";
				$name = "skybox_".str_replace("equirectangular_", "", $id)."_".$cubeFace.".jpg";

				$this->urlList->add($url, $name, "s");
			}
		}
		$this->urlList->debug();
		$this->urlList->download();
	}

	public function __construct($url) {
		$this->urlList = new UrlList();
		$this->getIdFromUrl($url);
		if ($this->validId==false) return;
		$this->url = "https://floorfy.com/tour/".$this->id;
	}
	
	private function getIdFromUrl($url) {
		$this->valid_id = false;
		if (strpos($url, "floorfy.com")===false) return;
		if (strpos($url, "tour/")===false) return;
		$arr = explode("tour/", $url);
		$arr2 = explode("/", $arr[1]);
		$this->id = $arr2[0];
		if (strlen($this->id)>0) $this->validId = true;
	}
	
	public function debug() {
		echo("floorfly->validId	= ".$this->valid_id.";\n");
		echo("floorfly->id	= ".$this->id.";\n");
		echo("floorfly->url	= ".$this->url.";\n");
	}
}

class UrlList {
	var $arr;

	public function __construct() {
		$this->arr = array();
	}

	public function add($url, $name, $type) {
		$url = new Url($url, $name, $type);
		$this->arr[] = $url; 
	}

	public function download() {
		foreach($this->arr as $url) {
			$url->download();
		}
	}

	public function debug() {
		foreach ($this->arr as $url) {
			$url->debug();
		}
	}
}

class Url {
	const HTTP_USER_AGENT = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36";
	const HTTP_ACCEPT = "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7";
	const HTTP_ACCEPT_LANGUAGE = "en-US,en;q=0.5";
	
	var $url;
	var $path;
	var $type;
	var $name;
	var $isOk;
	var $isData;
	var $data;

	public function __construct($url, $name, $type) {
		$this->isOk = false;
		$this->url = $url;
		$this->type = $type;
		if (strlen($name)==0) {
			$this->isOk = true;
			$this->isData = true;
		} else {
			$this->setName($name);
			$this->isData = false;
		}
	}
	
	public function debug() {
		echo("url->url = ".$this->url.";\n");
		//echo("url->path = ".$this->path.";\n");
		//echo("url->name = ".$this->name.";\n");
		//echo("url->type = ".$this->type.";\n");
	}

	public function setName($name) {
		$name = $this->formatFilename($name);
		if (strlen(str_replace(".", "", $name))<1) return;
		$this->name = $name;
		$this->path = dirname(__FILE__)."/".$name;
		$this->isOk = true;
	}

	private function formatFilename($cad) {
		$cad = str_replace("/", "_", $cad);
		$cad = str_replace("-", "_", $cad);
		$imax = strlen($cad);
		$char_val = " ";
		$char_num = 0;
		$char_valid = false;

		$res = "";
		for ($i=0; $i<$imax; $i++) {
			$char_val = substr($cad, $i, 1);
			$char_num = ord($char_val);
			$char_valid = false;

			if ($char_num >= ord("0") && $char_num <= ord("9")) {
				$char_valid = true;
			}


			if ($char_num >= ord("a") && $char_num <= ord("z")) {
				$char_valid = true;
			}


			if ($char_num >= ord("A") && $char_num <= ord("Z")) {
				$char_valid = true;
			}

			if ($char_val==".") $char_valid = true;
			if ($char_val=="_") $char_valid = true;
			if ($char_val=="(") $char_valid = true;
			if ($char_val==")") $char_valid = true;

			if ($char_valid==true) {
				$res .= $char_val;
			}
		}
		return $res;
	}

	public function download() {
		echo("Downloading ".$this->url."\n");

		if ($this->isData==true) {
			$this->data = $this->httpStr($this->url);
		} else {
			if (file_exists($this->path)) return true;
			$this->httpPath($this->url, $this->path);
		}
		return true;
	}

	private function httpStr($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		$headers = array();
		$headers[] = "Accept: ".self::HTTP_ACCEPT;
		$headers[] = "Accept-Language: ".self::HTTP_ACCEPT_LANGUAGE;

		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); 

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, self::HTTP_USER_AGENT);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  

		$output = curl_exec($ch);  

		curl_close($ch); 

		return $output;
	}
	
	private function httpPath($url, $path) {
		$fp = fopen($path, 'w+');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		$headers = array();
		//$headers[] = "Referer: ".$this->http_referer;
		$headers[] = "Accept: ".self::HTTP_ACCEPT;
		$headers[] = "Accept-Language: ".self::HTTP_ACCEPT_LANGUAGE;

		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); 

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, self::HTTP_USER_AGENT);
		curl_setopt($ch, CURLOPT_FILE, $fp); 
		curl_exec($ch);

		$res = false;
		if(curl_errno($ch)==0) $res = true; else { echo ("error curl ".curl_errno($ch)."\n"); }

		curl_close($ch); 
		fclose($fp);

		return $res;
	}
}

?>
