切割excel大文件，减少内存消耗，解析时间会延长

DEMO

        require_once 'autoloader.php';
        $excel = new \CutExcel\excelCut();
        $excel->cutFromFile('/tmp/20180731.xls');