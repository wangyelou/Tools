<?php
/**
 * Created by PhpStorm.
 * User: wangyelou
 * Date: 2018/5/15
 * Time: 16:16
 * Description:
 */

use PHPUnit\Framework\TestCase;
require_once '../../autoloader.php';

class TxtCutTest extends TestCase
{
    public function testReadaheadFromFile()
    {
        $datas = array(
            '工作表_1' => array(
                array(1,2,3,4),
                array('a', 'b', 'c', 'd'),
                array(1,2,3,4),
            ),
            '工作表_2' => array(
                array('a', 'b', 'c', 'd'),
                array('a', 'b', 'c', 'd'),
                array(1,2,3,4),
                array('a', 'b', 'c', 'd'),
                array('a', 'b', 'c', 'd'),
                array(1,2,3,4),
                array(1,2,3,4),
                array('a', 'b', 'c', 'd'),
                array(1,2,3,4),
                array('a', 'b', 'c', 'd'),
                array('a', 'b', 'c', 'd'),
                array('a', 'b', 'c', 'd'),
                array('a', 'b', 'c', 'd'),
                array('a', 'b', 'c', 'd'),
                array('a', 'b', 'c', 'd'),
                array('a', 'b', 'c', 'd'),
                array('a', 'b', 'c', 'd'),
                array('a', 'b', 'c', 'd'),
            )
        );
        
        require_once '../src/PhpSpreadsheet/autoload.php';
		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $i = 0;
        foreach ($datas as $sheetName => $sheetData) {
            if ($i > 0) {
                $spreadsheet->createSheet();
                $spreadsheet->setActiveSheetIndex($i);
            }
            $spreadsheet->getActiveSheet()->setTitle($sheetName);
            foreach ($sheetData as $rowIndex => $row) {
                foreach ($row as $colIndex => $col) {
                    $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($colIndex+1, $rowIndex+1, $col);
                }
            }             
            $i ++;
        }        
		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('/tmp/a.xlsx');

        $excel = new \CutExcel\excelCut();
        $excel->cutNum = 5;
        $result = $excel->readaheadFromFile('/tmp/a.xlsx');

        $this->assertEquals($result, array(
        	'a_工作表_1_0' => array(0, 4, '工作表_1'),
        	'a_工作表_2_0' => array(0, 4, '工作表_2'),
        	'a_工作表_2_1' => array(5, 9, '工作表_2'),
        	'a_工作表_2_2' => array(10, 14, '工作表_2'),
        	'a_工作表_2_3' => array(15, 19, '工作表_2'),
        ));
        unlink('/tmp/a.xlsx');
    }

    public function testCutFromFile()
    {
        $file = '/tmp/a.xlsx';
        $datas = array(
            '工作表_1' => array(
                array(1, 'a'),
                array(2, 'b'),
                array(3, 'c'),
                array(4, 'd'),
            ),
            '工作表_2' => array(
                array(10, 'ab'),
                array(20, 'bc'),
                array(30, 'cd'),
            )
        );
        require_once '../src/PhpSpreadsheet/autoload.php';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $i = 0;
        foreach ($datas as $sheetName => $sheetData) {
            if ($i > 0) {
                $spreadsheet->createSheet();
                $spreadsheet->setActiveSheetIndex($i);
            }
            $spreadsheet->getActiveSheet()->setTitle($sheetName);
            foreach ($sheetData as $rowIndex => $row) {
                foreach ($row as $colIndex => $col) {
                    $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($colIndex+1, $rowIndex+1, $col);
                }
            }             
            $i ++;
        }
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($file);

        $excel = new \CutExcel\excelCut();
        $excel->cutNum = 4;
        $excel->returnType = 'Csv';
        $result = $excel->cutFromFile($file);

        $this->assertEquals(basename($result[0]), 'a_工作表_1_0.Csv');
        $this->assertEquals(basename($result[1]), 'a_工作表_1_1.Csv');
        $this->assertEquals(basename($result[2]), 'a_工作表_2_0.Csv');
        $this->assertEquals(basename($result[3]), 'a_工作表_2_1.Csv');


        $this->assertEquals(preg_split("/(\s|,)+/", file_get_contents($result[0])), array('"1"', '"a"', '"2"', '"b"', ''));
        $this->assertEquals(preg_split("/(\s|,)+/", file_get_contents($result[1])), array('"3"', '"c"', '"4"', '"d"', ""));
        $this->assertEquals(preg_split("/(\s|,)+/", file_get_contents($result[2])), array('"10"', '"ab"', '"20"', '"bc"', ""));
        $this->assertEquals(preg_split("/(\s|,)+/", file_get_contents($result[3])), array('"30"', '"cd"', ""));
        unlink($file);
    }





}