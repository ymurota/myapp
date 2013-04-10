<?php
/*******************/
/*
/* ToDo:もう閉講した科目は自動削除
/* =>閉講した科目は登録しない。
/* =>新しいデータを取得するまえに削除シークエンスをいれる。
/*
/* ToDo:メモリリーク修正。
/*
/*******************/
require_once 'HTTP/Request.php';
App::import('Vendor', 'simplehtmldom/simple_html_dom');

App::uses('AppModel', 'Model');
App::uses('Hash', 'Utility');

class KamokuData extends AppModel {
	public $name = 'KamokuData';

	public $requestPage = 'https://www.wsl.waseda.jp/syllabus/index.php';
	private $http;

	private $defaultQuery = array(
		//現状、メモリリークして登録出来ない場合はp_numberを小さくし、
		//p_pageを順繰りして細切れにデータを手動で取得
		'p_number' => 1000,
		//'p_page' => 3,
		'pfrontPage' => 'now',
		'keyword' => '',
		'kamoku' => '',
		'kyoin' => '',
		'p_gakki' => '',
		'p_youbi' => '',
		'p_jigen' => '',
		'p_gengo' => '',
		'p_gakubu' => '282006',
		'p_keya' => '2801010',
		'p_searcha' => 'a',
		'p_keyb' => '',
		'p_searchb' => 'b',
		'hidreset' => '',
		'pchgFlg' => '',
		'ControllerParameters' => 'JAA103SubCon',
		'pOcw' => '',
		'pType' => '',
		'pLng' => 'jp'
	);

	//実際のラベルとフィールドの対応付け。仮に一致しないものがあった場合は本家の表示が変わっている可能性が高いので
	//エラーを吐く処理を加える。
	private $formatMap = array(
		'開講年度' => 'year',
		'科目名' => 'subject',
		'担当教員' => '',
		'開講学部' => '',
		'学期' => 'term',
		'曜日時限' => array('day', 'period'),
		'使用教室' => array('building', 'class'),
		'授業概要' => ''
	);

	//展開するフィールド。それぞれのフィールドでデータ数が一致しなければならない。
	//「01:」や「02:」を持つ項目について展開する必要がある。
	private $expandFields = array('曜日時限', '使用教室');

	private $required = array('day', 'period', 'building', 'class');
	
	//指定した項目のデータを任意のモデルメソッドで整形する。
	private $userMethods = array(
		'学期' => array('removeBr'),
		'曜日時限' => array('devideByClass', 'splitDayPeriod'),
		'使用教室' => array('devideByClass', 'parseClass'),
	);

	/* 学部ごとによるデータ取得用メソッド */
	/* 引数にはDepartment.idを用いる */
	public function updateByDeprt($id) {
		$Department = ClassRegistry::init("Department");
		$dId = join("", Hash::extract($Department->findById($id), "Department.post_id"));
		$sIds = Hash::extract($Department->children($id), "{n}.Department.post_id");

		$flag = true;
		foreach ($sIds as $sId) {
			if ($this->update($dId, $sId) && $flag) continue;
			$flag = false;
		}
		return $flag;
	}

	/* 学科ごとに取得。学部と学科のpost_idを用いる */
	public function update($dId, $sId) {
		$rawData = $this->fetch($dId, $sId);
		$data = $this->parseData($rawData, $dId, $sId);

		/* データベースのデータ取得 */
		$dData = $this->find('all', array(
				'conditions' => array('d_id' => $dId, 's_id' => $sId),
				'fields' => array_keys($data[0])
			));
		$alias = $this->alias;
		$dData = array_reduce($dData, function($memo, $item)use($alias){
$memo[] = $item[$alias]; return $memo;}, array());
			
		/* データベースとの差分のみ登録 */
		$diff = $this->diff($data, $dData);
		
		if (empty($diff)) return true;
		if ($this->saveAll($diff)) return true;
		return false;
	}

	/* データベースにすでにあるものとの差分 */
	/* 同じ構造の配列じゃないとダメ。正確にはデータベースに登録するときのような形式 */
	public function diff($yData, $dData) {
		$fields = array_keys($yData[0]);
		$ret = array();

		/* 配列の各要素（配列）を文字列にして比較しやすくする */
		foreach (compact("yData", "dData") as $name => $data) {
			${$name} = array_reduce($data, function($memo, $item){
	/* くっつける文字がデータに入っていないか注意 */
	/* 例えば","はperiodで使われてるのでexplodeする時余分なものまで展開される */
	$memo[] = join('.', $item);
	return $memo;
 }, array());
		}

		foreach ($yData as $k => $val) {
			if (in_array($val, $dData)) unset($yData[$k]);
		}

		$ret = array_reduce($yData, function($memo, $item)use($fields){
 $item = explode('.', $item);
 if(count($fields) != count($item)){
	 pr($fields); pr($item);
 }
 $memo[] = array_combine($fields, $item);
 return $memo;}, array());


		return $ret;
	}
	   
	/* HTTP通信系メソッド */
	public function getHTTP() {
		$http = new HTTP_Request($this->requestPage);
		$http->setMethod(HTTP_REQUEST_METHOD_POST);
		return $http;
	}

	public function setPOST(HTTP_Request $http, $params = array()) {
		foreach ($params as $k => $v) {
			$http->addPostData($k, $v);
		}
		return $http;
	}

	public function isDirty(HTTP_Request $http) {
		if (empty($http->_postData)) return true;
		return false;
	}

	public function clearPOST(HTTP_Request $http) {
			$http->clearPostData();
	}

	public function fetch($dId, $sId) {
		$post = array('p_gakubu' => $dId, 'p_keya' => $sId);
		
		$http = $this->getHTTP();
 		$http = $this->setPOST($http, array_merge($this->defaultQuery, $post));
		if (!PEAR::isError($http->sendRequest())) {
			$body = $http->getResponseBody();
		}

		return $body;
	}

	/* SimpleHtmlDomsはめっちゃメモリを食うらしいので一括解放 */
	private function clearDoms($dom) {
		if (is_array($dom)) {
			foreach ($dom as $k => $v) {
				$this->clearDoms(&$dom[$k]);
			}
		}
		if (method_exists($dom, "clear")) $dom->clear();
		unset($dom);
	}
	
	/* 早稲田からのレスポンスをデータベースに登録可能な形に整形する。 */
	public function parseData($data, $dId, $sId) {
		$html = str_get_html($data);
		$trs = $html->find("table.ct-vh tr");
		
		$item = array();
		$label = array();
		foreach ($trs[0]->find('th') as $th) {
			$str = $this->trim($th->plaintext);
			
			if ($str == "") continue;
			$label[] = $str;
		}
		
		foreach ($trs as $tr) {
			if (empty($tr->plaintext)) continue;
			$tds = $tr->find('td');
			
			if (empty($tds) || count($tds) < 2) continue;

			$tmp = array();
			foreach ($label as $k => $f) {
				if (!in_array($f, array_keys($this->formatMap))) trigger_error("このデータは登録できません。本家のデータ構造が変更されていないか確認してください。", E_USER_ERROR);
				if ($this->formatMap[$f] == '') continue;

				if ($this->trim($tds[$k]->plaintext) == "教室未定") {
					$tmp = array();
					break;
				}
				
				$tmp[$f] = $this->trim($tds[$k]->plaintext);
			}

			if (empty($tmp)) continue;
			
			/* ここで科目の学部、学科情報を挿入 */
			/* 突貫工事。どうにかする！！ */
			$tmp['d_id'] = $dId;
			$tmp['s_id'] = $sId;
			
			$item = array_merge($item, $this->parseParam($tmp));
		}

		$html->clear();
		unset($html);
		
		/* メモリ解放 */
		//$this->clearDoms(array(&$html, &$trs, &$tds));
		return $item;
	}

	private function parseParam($data) {
		$ret = array();
		foreach ($data as $k => $v) {
			if (isset($this->userMethods[$k])) {
				$methods = $this->userMethods[$k];
				if (is_array($methods)) {
					foreach ($methods as $method) {
						$v = $this->{$method}($v);
					}
				} else {
					$v = $this->{$methods}($v);
				}
			}
			$ret[$k] = $v;
		}
		return $this->expandParam($ret);
	}

	/* データを展開しながらデータベースに登録可能な形にする。 */
	/* データが揃っていない、かけているものは弾く */
	private function expandParam($data) {
		$ret = array();
		$flag = true;
		$count = count($data[$this->expandFields[0]]);
		for ($i = 1; $i < count($this->expandFields); $i++) {
			if ($count != count($data[$this->expandFields[$i]])) {
				$flag = false;
				break;
			}
			$count = count($data[$this->expandFields[$i]]);
		}

		if ($flag) {
			for ($i = 0; $i < $count; $i++) {
				$tmp = array();

				/* TODO: 展開の仕様、要検討！もっと抽象的（expandFieldsつかう！！）に展開する! */
				/* 教室で展開 */
				foreach ($data as $k => $v) {
					/* 突貫工事。今後別のとこで処理する */
					if (in_array($k, array('d_id', 's_id'))) {
						$tmp[$k] = $v;
						continue;
					}
					/*------------------------------*/
					
					if (!is_array($v)) {
						$tmp[$this->formatMap[$k]] = mb_convert_encoding($v, 'utf8', 'utf8');
					} else {
						$tmp = array_merge($tmp, $v[$i]);
					}
				}

				/* 今度は時限(period)で展開 */
				$tmp2 = array();
				if (is_array($tmp['period'])) {
					for ($j = 0; $j < count($tmp['period']); $j++) {
						$tmp2[$j] = $tmp;
						$tmp2[$j]['period'] = $tmp2[$j]['period'][$j];

						foreach($this->required as $f) {
							if ($tmp2[$j][$f] == "") {
								unset($tmp2[$j]);
								break;
							}
						}
						
					}
				}
				$ret = array_merge($ret, $tmp2);
			}
		}
		return $ret;
	}

	/* 曜日と時限を切り離して配列で返す */
	public function splitDayPeriod($vals) {
		$ret = array();
		foreach ($vals as $v) {
			$v = $this->mb_str_split($v);
			$day = $v[0];
			$period = array();

			for ($i = 1; $i < count($v); $i++) {
				$tmp = mb_convert_kana($v[$i], "n", "utf-8");
				if($tmp+0 === 0) continue;
				$period[] = $tmp+0;
			}

			/* 時限が連続である保証。もし1-3とかなら1-2-3に直す。 */
			if (count($period) > 1) {
				for ($i = 0; $i < count($period)-1; $i++) {
					if ($period[$i] < $period[$i+1] && $period[$i]+1 != $period[$i+1]) {
						$diff = $period[$i+1] - $period[$i];
						$base = $period[$i];
						for ($j = 1; $j < $diff; $j++) {
							array_splice($period, $i+1, 0, $base+$j);
							$i += 1;
						}
					}
				}
			}

			/* date関数のwに対応 */
			$day_k = array("日" => 0, "月" => 1, "火" => 2, "水" => 3, "木" => 4, "金" => 5, "土" => 6);
			if (in_array($day, array_keys($day_k))) {
				$day = $day_k[$day];
			} else {
				$day = null;
				$period = null;
			}

			$ret[] = array('day' => $day, 'period' => $period);
 		}

		return $ret;
	}

	/* 謎の「01：」という文字列を削除 */
	/* クラス分けだったーめんどー */
	/* Deplicated */
	public function trimTrash($str) {
		return preg_replace("/\d+:/", "", $str);
	}

	/* 同じ科目で2つの班に分かれていたら2科目として扱う。そのためにそれぞれのデータを配列に分ける。 */
	public function devideByClass($v) {
		$v = explode("\r\n", $v);
		foreach ($v as $k => $val) {
			$v[$k] = $this->trimTrash($val);
		}
		return $v;
	}
	
	public function parseClass($vals) {
		$ret = array();
		foreach ($vals as $val) {
			/* 52-100のような場合は"-"でexplodeしてbuildingを取得 */
			$val = mb_convert_kana($val, "n", "utf-8");
			$val = mb_convert_kana($val, "a", "utf-8");
			$val = explode("-", $val);

			if (!empty($val[0]) && empty($val[1])) {
				/* 63号館3階端末室Cルームの場合はちょっと面倒くさく処理 */
				$val = explode("号館", $val[0]);
				if (!empty($val[0]) && !empty($val[1])) {
					$val[1] = str_replace("階端末室", "-", $val[1]);
					$val[1] = str_replace("ルーム", "", $val[1]);
					$ret[] = array('building' => $val[0], 'class' => $val[1]);
					continue;
				}
				
				$ret[] = array('building' => null, 'class' => null);
				continue;
			}

			/* "別棟"が含むと最悪。時限のデータ数1に対して教室データ数2になる。 */
			/* とりあえずすのデータを無効にする。 */
			/* 将来的には曖昧な情報は別途管理したい */
			if (strstr($val[0], "別棟")) continue;

			/* 全角文字、および無駄な()の削除 */
			$val[1] = preg_replace("/[^\x01-\x7E]|[()]/", "", $val[1]);
			$ret[] = array('building' => $val[0], 'class' => $val[1]);
		}

		return $ret;
	}
		
	public function removeBr($str) {
		return str_replace("\r\n", ",", $str);
	}
	/* Utility */
	/* 謎の空白を強引に削除。しきれないかもしれない。 */
	public function trim($str) {
		$str = str_replace("&nbsp;", "", $str);
        $str = str_replace(" ", "", $str);
        $str = str_replace("　", "", $str);
		$str = htmlspecialchars($str);
		return $str;
	}

	/* str_splitのマルチバイト対応版。パクリ。 */
	function mb_str_split($str, $split_len = 1) {

		mb_internal_encoding('UTF-8');
		mb_regex_encoding('UTF-8');

		if ($split_len <= 0) {
			$split_len = 1;
		}

		$strlen = mb_strlen($str, 'UTF-8');
		$ret    = array();

		for ($i = 0; $i < $strlen; $i += $split_len) {
			$ret[ ] = mb_substr($str, $i, $split_len);
		}
		return $ret;
	}
}	

?>