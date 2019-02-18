<?php


// get page
function getPageCurl ($url, $available = false) {
//	if($available){
//      $url = $url . '/sklad/in/';
//  }
	$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
	$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
	$header[] = "Cache-Control: max-age=0";
	$header[] = "Connection: keep-alive";
	$header[] = "Keep-Alive: 300";
	$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
	$header[] = "Accept-Language:ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3";
	$header[] = "Pragma: ";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_COOKIEFILE,  dirname(__FILE__).'/cookie.txt');
	curl_setopt($ch, CURLOPT_ENCODING, '');
	$data = curl_exec($ch);
	if ($data === false) {
		echo 'Ошибка curl: '.curl_error($ch);
	}
	
	curl_close($ch);
	
	//$data = set_utf8_meta($data);
	
	return $data;
	
}

function set_utf8_meta ($page) {
	return preg_replace('/<head[^>]*>/',
	'<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">',
	$page);
}

function getListCategory ($res) {
    $dom = new DOMDocument();
    $dom->strictErrorChecking = false;
    $dom->recover = true;
    @$dom->loadHTML($res);

    $ul = array();

	foreach ($dom->getElementsByTagName('a') as $elem) {
		if ($elem->getAttribute('class') == 'subnav-anchor--level3') {
            $ul[] = $elem->getAttribute('href');
		}
	}

	return $ul;
}

// get objects from page
function getObjectsPage ($cat_id, &$offset, &$max_offset) {
    // add "/?limit=all" to category link to show all products
	$content = getPageCurl($cat_id.'/?limit=all', true);
	$content = mb_convert_encoding($content, 'html-entities', 'utf-8');

	$dom = new DOMDocument();
	$dom->strictErrorChecking = false;
	$dom->recover = true;
	@$dom->loadHTML($content);

    $href = array();

    foreach ($dom->getElementsByTagName('a') as $elem) {
        if($elem->getAttribute('class') == 'product-image'){
            $href[] = $elem->getAttribute('href');
        }
    }


   $obj = array();
    for($e=0; $e < count($href); $e++){
        $obj[] = getObject($href[$e], $cat_id);

    }

	return $obj;
}

// get object
function getObject ($link, $category) {
	$content = getPageCurl($link);
	$content = mb_convert_encoding($content, 'html-entities', 'utf-8');
	
	$dom = new DOMDocument();
	$dom->strictErrorChecking = false;
	$dom->recover = true;
	@$dom->loadHTML($content);
	
	$arr = array();

	// name
    foreach ($dom->getElementsByTagName('h1') as $elem) {
        if($elem->nodeValue) {
            $arr['name'] = trim($elem->nodeValue);
        }
    }

    //code SKU
    foreach($dom->getElementsByTagName('div') as $elem) {
        if ($elem->getAttribute('class') == 'product-sku pg-code' && $elem->nodeValue) {
            $code = $elem->nodeValue;
            $position = strrpos($code, ':');
            $arr['code'] = trim(substr($code, $position + 1));
        }
    }

    //price
    foreach($dom->getElementsByTagName('span') as $elem) {
        if ($elem->getAttribute('class') == 'price' && $elem->nodeValue) {
            $price = $elem->nodeValue;
            $position = strpos($price, 'г');
            $arr['price'] = trim(substr($price, 0, $position));
        }
    }

    // image
//$arr['img'] = '';
    foreach ($dom->getElementsByTagName('div') as $elem) {
        if ($elem->getAttribute('class') == 'product-image-gallery') {
            foreach ($elem->getElementsByTagName('img') as $item) {
                if ($item->getAttribute('class') == 'gallery-image') {
                    $arr['img'][] = $item->getAttribute('src');
                }
                else $arr['img'][] = '';
            }
        }
    }

    //category
    //$arr['category'] = '';
    $arr['top_category'] = basename($category);
    foreach ($dom->getElementsByTagName('div') as $elem) {
        if ($elem->getAttribute('class') == 'breadcrumbs') {
            foreach ($elem->getElementsByTagName('span') as $item) {

                for ($i=1; $i<=2; $i++) {
                    if ($elem->getElementsByTagName('span')[$i]->nodeValue) {
                        $arr['category'] .= trim($elem->getElementsByTagName('span')[$i]->nodeValue) . '$$';
                    }
                }
            }
        }
    }

    //description
    $descr = $dom->getElementById('product-attribute-specs-table');
    $html = $dom->saveHTML($descr);
    $arr['description'] = removeLinks($html);

    //instruction
    foreach ($dom->getElementsByTagName('a') as $item) {
        if ($item->getAttribute('class') == 'pg-download-inst') {
            $instruction = $item->getAttribute('href');
            $arr['instruction'] = $instruction;

        }
    }
	return $arr;
}

function removeLinks ($html) {
    return preg_replace('#<a.*?>([^>]*)</a>#i', '$1', $html);
}

function saveCache ($list) {
	file_put_contents('cache/cache.dat', $list."\r\n", FILE_APPEND);
}

function getFileEndStore ($link, $destination, $prefix='') {
    if (!empty($link)) {
        $name = $prefix.basename($link);
        $content = file_get_contents($link);
        file_put_contents($destination.'/'.$name, $content);
        return true;
    }
    else return false;
}

function makeDir ($path) {
    if (is_array($path)) {
        foreach ($path as $item) {
            simpleMakeDir ($item);
        }
    }
    else {
        simpleMakeDir ($path);
    }
}

function simpleMakeDir ($dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

?>