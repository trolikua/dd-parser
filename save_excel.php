<?php
// save to excel
require_once('PHPExcel/Classes/PHPExcel.php');
require_once ('functions.php');

$xls = new PHPExcel();
$xls->setActiveSheetIndex(0);
$sheet = $xls->getActiveSheet();
$sheet->setTitle('Список товаров');

$sheet->setCellValueByColumnAndRow(0, 1, 'Имя товара');
$sheet->setCellValueByColumnAndRow(1, 1, 'Код');
$sheet->setCellValueByColumnAndRow(2, 1, 'Цена');
$sheet->setCellValueByColumnAndRow(3, 1, 'Изображения');
$sheet->setCellValueByColumnAndRow(4, 1, 'Категория');
$sheet->setCellValueByColumnAndRow(5, 1, 'Инструкция');
$sheet->setCellValueByColumnAndRow(6, 1, 'Дополнительная информация');

$cache = file('cache/cache.dat');
print_r($cache);
echo "<hr>";
//$j = 2;

for ($i = 0; $i < count($cache); $i++) {
	$obj = json_decode(trim($cache[$i]), true);

	for ($k = 0; $k < count($obj); $k++) {
	    $current_row = $sheet->getHighestRow() + 1;

		$sheet->setCellValueByColumnAndRow(0, $current_row, $obj[$k]['name']);
		$sheet->setCellValueByColumnAndRow(1, $current_row, $obj[$k]['code']);
		$sheet->setCellValueByColumnAndRow(2, $current_row, $obj[$k]['price']);

		$sheet->setCellValueByColumnAndRow(4, $current_row, $obj[$k]['category']);
		$sheet->setCellValueByColumnAndRow(5, $current_row, $obj[$k]['instruction']);
		$sheet->setCellValueByColumnAndRow(6, $current_row, $obj[$k]['description']);

		//$j++;
        $dir = array(
            'root' => 'FILES/'.$obj[$k]['top_category'],
            'images' => 'FILES/'.$obj[$k]['top_category'].'/images',
            'instructions' => 'FILES/'.$obj[$k]['top_category'].'/instructions'
        );
        makeDir ($dir);
        $new_img = '';

        if(is_array($obj[$k]['img'])){
            foreach($obj[$k]['img'] as $item) {
                getFileEndStore ($item, $dir['images']);
                $new_img .= $dir['images'].'/'.basename($item) . '$$';
            }
        } elseif (!empty($obj[$k]['img'])) {
            $new_img = $dir['images'].'/'.basename($obj[$k]['img']);
            getFileEndStore ($new_img, $dir['images']);
        }
        else $new_img = '';

        $sheet->setCellValueByColumnAndRow(3, $current_row, $new_img);

        getFileEndStore($obj[$k]['instruction'], $dir['instructions']);

        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'CSV');
        $objWriter->save($dir['root'].'/price.csv');
	}
}

unlink('cache/cache.dat');
echo 'true';
exit;

?>