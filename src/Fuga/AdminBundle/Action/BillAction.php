<?php

namespace Fuga\AdminBundle\Action;
	
class BillAction extends Action {

	public $item;

	function __construct(&$adminController) {
		parent::__construct($adminController);
		$this->item = $this->dataTable->getItem($this->get('router')->getParam('id')); 
	}


	function getText() {
		global $PRJ_DIR;

		$order = $this->item;
		if (!$order) {
			throw $this->createNotFoundException('Не найден заказ');
		}

		$file = 'bill_colors_'.$order['id'].'.xlsx';


		$filepath = $PRJ_DIR.'/bills/'.$file;

		@copy($PRJ_DIR.'/bills/bill_template.xlsx', $filepath);

		$objPHPExcel = \PHPExcel_IOFactory::createReader('Excel2007');
		$objPHPExcel = $objPHPExcel->load($filepath);
		$objPHPExcel->setActiveSheetIndex(0);

		$i = 21;
		$sum = 0;
		$num = 1;

		$products0 = explode("\n", $order['order_txt']);

		$products = array();
		foreach ($products0 as $product) {
			$item = explode("\t", $product);
			if (count($item) == 1) {
				continue;
			}
			$item[0] = preg_replace('/^(\[[0-9]+\])+/', '', $item[0]);
			$item[0] = trim(str_replace('Заказ:', 'Размер ', $item[0]));
			$item[0] = str_replace('\\', '', $item[0]);
			$item[0] = substr($item[0], 0, strlen($item[0])-1);
			$item[2] = str_replace(' руб.', '', $item[2]);
			$item[2] = str_replace(',', '.', $item[2]);
			$item[2] = floatval(str_replace(' ', '', $item[2]));
			$item[3] = intval($item[3]);
			$products[] = $item;
		}

		$created = new \DateTime($order['created']);

		$gdImage = imagecreatefromjpeg($PRJ_DIR.'/bills/logo.jpg');

		$objDrawing = new \PHPExcel_Worksheet_MemoryDrawing();
		$objDrawing->setName('Logo');
		$objDrawing->setDescription('Logotype');
		$objDrawing->setImageResource($gdImage);
		$objDrawing->setRenderingFunction(\PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
		$objDrawing->setMimeType(\PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
//		$objDrawing->setWidth(200);
		$objDrawing->setHeight(100);
		$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
		$objDrawing->setCoordinates('A1');


		$objPHPExcel->getActiveSheet()->setCellValue('F2', date('d.m.Y').'г.');

		$objPHPExcel->getActiveSheet()->setCellValue('A6', 'НАКЛАДНАЯ № ТН-'.sprintf('%06d', $order['id']));

		$objPHPExcel->getActiveSheet()->getStyle('A8:A11')->getFont()->setSize(14);

		$objRichText = new \PHPExcel_RichText();
		$objRichText->createTextRun('Получатель: ')->getFont()->setBold(true)->setSize(14);
		$objRichText->createTextRun($order['fio'])->getFont()->setSize(14);
		$objPHPExcel->getActiveSheet()->setCellValue('A8', $objRichText);

		$objRichText = new \PHPExcel_RichText();
		$objRichText->createTextRun('Контактный телефон: ')->getFont()->setBold(true)->setSize(14);
		$objRichText->createTextRun($order['phone'])->getFont()->setSize(14);
		$objPHPExcel->getActiveSheet()->setCellValue('A9', $objRichText);

		$objRichText = new \PHPExcel_RichText();
		$objRichText->createTextRun('Заказ: № ')->getFont()->setBold(true)->setSize(14);
		$objRichText->createTextRun($order['id'].' от '.$created->format('d.m.Y'))->getFont()->setSize(14);
		$objPHPExcel->getActiveSheet()->setCellValue('A10', $objRichText);

		$objRichText = new \PHPExcel_RichText();
		$objRichText->createTextRun('Адрес доставки: ')->getFont()->setBold(true)->setSize(14);
		$objRichText->createTextRun($order['address'])->getFont()->setSize(14);
		$objPHPExcel->getActiveSheet()->setCellValue('A11', $objRichText);
		$objPHPExcel->getActiveSheet()->getStyle('A11')->getAlignment()->setWrapText(true);

		$objPHPExcel->getActiveSheet()->getRowDimension(12)->setRowHeight(-1);
		$objRichText = new \PHPExcel_RichText();
		$objRichText->createTextRun('Дополнительная информация: ')->getFont()->setBold(true)->setSize(14);
		$objRichText->createTextRun($order['additions'])->getFont()->setSize(14);
		$objPHPExcel->getActiveSheet()->setCellValue('A12', $objRichText);
		$objPHPExcel->getActiveSheet()->getStyle('A12')->getAlignment()->setWrapText(true);


		foreach ($products as $product) {
			$objPHPExcel->getActiveSheet()
				->getStyle('A'.$i.':F'.$i)
				->getBorders()
				->getAllBorders()
				->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

			$objPHPExcel->getActiveSheet()->getStyle('B'.$i)
				->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()
				->getStyle('A'.$i.':F'.$i)
				->getAlignment()
				->setVertical(\PHPExcel_Style_Alignment::VERTICAL_TOP);
			$objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);

			$sum0 = $product['2']*$product['3'];
			if ($order['discount']) {
				$sum0 *= (100-$order['discount'])/100;
			}

			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $num);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $product[0]);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $product[2]);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $order['discount'] ? $order['discount'] : '-');
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $product[3]);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $sum0);

			$objPHPExcel->getActiveSheet()->getStyle('C'.$i)->getNumberFormat()->setFormatCode('# ##0.00');
			$objPHPExcel->getActiveSheet()->getStyle('D'.$i)->getNumberFormat()->setFormatCode('# ##0.00');
			$objPHPExcel->getActiveSheet()->getStyle('F'.$i)->getNumberFormat()->setFormatCode('# ##0.00');
			$i++;
			$num++;
			$sum += $sum0;
		}

		if (intval($order['delivery_cost']) > 0) {
			$objPHPExcel->getActiveSheet()
				->getStyle('A'.$i.':F'.$i)
				->getBorders()
				->getAllBorders()
				->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $num);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, 'Доставка');
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $order['delivery_cost']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, '-');
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, 1);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $order['delivery_cost']);

			$objPHPExcel->getActiveSheet()->getStyle('D'.$i)
				->getAlignment()
				->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);;
			$objPHPExcel->getActiveSheet()->getStyle('C'.$i)->getNumberFormat()->setFormatCode('# ##0.00');
			$objPHPExcel->getActiveSheet()->getStyle('F'.$i)->getNumberFormat()->setFormatCode('# ##0.00');
			$i++;
			$sum += $order['delivery_cost'];
		}

		$objPHPExcel->getActiveSheet()
			->getStyle('E'.$i.':F'.$i)
			->getBorders()
			->getAllBorders()
			->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, 'Итого:');
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $sum);
		$objPHPExcel->getActiveSheet()->getStyle('F'.$i)->getNumberFormat()->setFormatCode('# ##0.00');
		$i += 4;
		$objPHPExcel->getActiveSheet()
			->getStyle('B'.$i)
			->getBorders()
			->getBottom()
			->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
		$i++;
		$objPHPExcel->getActiveSheet()
			->getStyle('B'.$i)
			->getAlignment()
			->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, 'М.П.');


		$objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
		$objWriter->save($filepath);

		if (!file_exists($filepath)) {
			header ("HTTP/1.0 404 Not Found");
			die();
		}
		// сообщаем размер файла
		header( 'Content-Length: '.filesize($filepath) );
		// дата модификации файла для кеширования
		header( 'Last-Modified: '.date("D, d M Y H:i:s T", filemtime($filepath)) );
		// сообщаем тип данных - zip-архив
		header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		// файл будет получен с именем $filename
		header('Content-Disposition: attachment; filename="'.$file.'"');
		// начинаем передачу содержимого файла
		readfile($filepath);
	}
}

