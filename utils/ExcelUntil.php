<?php
namespace CMS\MTeamServicesSDK\Until;

class ExcelUntil extends BaseUntil
{
    /**
     * 导入excel文件
     * @param  string $file excel文件路径
     * @return array        excel文件内容数组
     */
    public static function import_excelV1($file)
    {
        ini_set('max_execution_time', '0');
       /* require_once __DIR__ . '/../Lib/PHPExcel/Classes/PHPExcel.php';
        require_once __DIR__ . '/../Lib/PHPExcel/Classes/PHPExcel/IOFactory.php';
        require_once __DIR__ . '/../Lib/PHPExcel/Classes/PHPExcel/Reader/Excel5.php';
        require_once __DIR__ . '/../Lib/PHPExcel/Classes/PHPExcel/Cell.php';*/

        // 判断文件是什么格式
        $type = pathinfo($file);
        $type = strtolower($type["extension"]);
        $objReader = null;
        if ($type == 'xls') {
            $objReader = new \PHPExcel_Reader_Excel5();
        } else if ($type == 'xlsx') {
            $objReader = new \PHPExcel_Reader_Excel2007();
        } else if ($type == 'csv') {
            $data = self::getCsvContent($file);
            return $data;
        } else {
            throw new \Exception("文件格式不支持");
        }
        // 判断使用哪种格式
        $objPHPExcel = $objReader->load($file);
        $sheet = $objPHPExcel->getSheet(0);
        // 取得总行数
        $highestRow = $sheet->getHighestRow();
        // 取得总列数
        $highestColumn = $sheet->getHighestColumn();
        //循环读取excel文件,读取一条,插入一条
        $data = array();
        //从第一行开始读取数据
        for ($j = 1; $j <= $highestRow; $j++) {
            //从A列读取数据
            for ($k = 'A'; $k <= $highestColumn; $k++) {
                // 读取单元格
                $data[$j][] = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
            }
        }
        return $data;
    }

    /**
     * 导入excel文件(phpexcel 导入超过26列时的解决方案)
     * @param  string $file excel文件路径
     * @return array        excel文件内容数组
     */
    public static function import_excelV2($file)
    {
        ini_set('max_execution_time', '0');
       /* require_once __DIR__ . '/../Lib/PHPExcel/Classes/PHPExcel.php';
        require_once __DIR__ . '/../Lib/PHPExcel/Classes/PHPExcel/IOFactory.php';
        require_once __DIR__ . '/../Lib/PHPExcel/Classes/PHPExcel/Reader/Excel5.php';
        require_once __DIR__ . '/../Lib/PHPExcel/Classes/PHPExcel/Cell.php';*/

        // 判断文件是什么格式
        $type = pathinfo($file);
        $type = strtolower($type["extension"]);
        $objReader = null;
        if ($type == 'xls') {
            $objReader = new \PHPExcel_Reader_Excel5();
        } else if ($type == 'xlsx') {
            $objReader = new \PHPExcel_Reader_Excel2007();
        } else if ($type == 'csv') {
            $data = self::getCsvContent($file);
            return $data;
        } else {
            throw new \Exception("文件格式不支持");
        }
        // 判断使用哪种格式
        $objPHPExcel = $objReader->load($file);
        $sheet = $objPHPExcel->getSheet(0);
        // 取得总行数
        $highestRow = $sheet->getHighestRow();
        // 取得总列数
        $highestColumn = \PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
        //循环读取excel文件,读取一条,插入一条
        $data = array();
        //从第一行开始读取数据
        for ($j = 1; $j <= $highestRow; $j++) {
            //从A列读取数据
            for ($k = 0; $k < $highestColumn; $k++) {
                // 读取单元格
                $data[$j][] = $sheet->getCellByColumnAndRow($k, $j)->getValue();
            }
        }
        return $data;
    }

    public static function getCsvContent($file)
    {
        $fp = fopen($file, 'r');
        $lines = array();
        while ($data = fgetcsv($fp)) { //每次读取CSV里面的一行内容
            //此为一个数组，要获得每一个数据，访问数组下标即可
            $lines[] = $data;
        }
        fclose($fp);
        return $lines;
    }

    public static function get_file_line($file_name, $line)
    {
        $n = 0;
        $handle = fopen($file_name, 'r');
        if ($handle) {
            while (!feof($handle)) {
                ++$n;
                $out = fgets($handle, 4096);
                if ($line == $n) break;
            }
            fclose($handle);
        }
        if ($line == $n && isset($out)) return $out;
        return false;
    }

    public static function get_file_range($file_name, $line_star, $line_end)
    {
        $n = 0;
        $handle = fopen($file_name, "r");
        if ($handle) {
            while (!feof($handle)) {
                ++$n;
                $out = fgets($handle, 4096);
                if ($line_star <= $n) {
                    $lines[] = $out;
                }
                if ($line_end == $n) break;
            }
            fclose($handle);
        }
        if ($line_end == $n && isset($lines)) return $lines;
        return false;
    }

    /**
     * 数据转csv格式的excle
     * @param  array $data      需要转的数组
     * @param  string $header   要生成的excel表头
     * @param  string $filename 生成的excel文件名
     *      示例数组：
    $data = array(
        '1,2,3,4,5',
        '6,7,8,9,0',
        '1,3,5,6,7'
    );
    $header='用户名,密码,头像,性别,手机号';
     */
    //在每个单元格后面追加一个看不见的tab制表符；这样就不是数字格式；也就不会被显示成科学计数法了；
   public static function create_csv($data, $header=null, $filename='simple.csv'){
        // 如果手动设置表头；则放在第一行
        if (!is_null($header)) {
            array_unshift($data, $header);
        }
        // 防止没有添加文件后缀
        $filename=str_replace('.csv', '', $filename).'.csv';
        Header( "Content-type:  application/octet-stream ");
        Header( "Accept-Ranges:  bytes ");
        Header( "Content-Disposition:  attachment;  filename=".$filename);
        foreach( $data as $k => $v){
            // 如果是二维数组；转成一维
            if (is_array($v)) {
                $v=implode(',', $v);
            }
            // 替换掉换行
            $v=preg_replace('//s*/', '', $v);
            // 解决导出的数字会显示成科学计数法的问题
            $v=str_replace(',', "/t,", $v);
            // 转成gbk以兼容office乱码的问题
            echo iconv('UTF-8','GBK',$v)."/r/n";
        }
    }

    /**
     * 数组转xls格式的excel文件
     * @param  array  $data      需要生成excel文件的数组
     * @param  string $filename  生成的excel文件名
     *      示例数据：
    $data = array(
        array(NULL, 2010, 2011, 2012),  //NULL显示为空格
        array('Q1',   12,   15,   21),
        array('Q2',   56,   73,   86),
        array('Q3',   52,   61,   69),
        array('Q4',   30,   32,    0),
    );
    $header= array('用户名','密码','头像','性别');
     */
    public static function create_xls($data, $header=null, $filename='simple.xls')
    {
        ini_set('max_execution_time', '0');
        /*require_once __DIR__ . '/../Lib/PHPExcel/Classes/PHPExcel.php';
        require_once __DIR__ . '/../Lib/PHPExcel/Classes/PHPExcel/IOFactory.php';
        require_once __DIR__ . '/../Lib/PHPExcel/Classes/PHPExcel/Reader/Excel5.php';
        require_once __DIR__ . '/../Lib/PHPExcel/Classes/PHPExcel/Cell.php';*/

        // 如果手动设置表头；则放在第一行
        if (!is_null($header)) {
            array_unshift($data, $header);
        }

        $filename=str_replace('.xls', '', $filename).'.xls';
        $phpexcel = new \PHPExcel();
        $phpexcel->getProperties()
            ->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        $phpexcel->getActiveSheet()->fromArray($data);
        $phpexcel->getActiveSheet()->setTitle('Sheet1');
        $phpexcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$filename");
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        $objwriter = \PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
        $objwriter->save('php://output');
        exit;
    }

    /**
     * 数组转xlsx格式的excel文件
     * @param  array  $data      需要生成excel文件的数组
     * @param  string $filename  生成的excel文件名
     *      示例数据：
    $data = array(
        array(NULL, 2010, 2011, 2012),  //NULL显示为空格
        array('Q1',   12,   15,   21),
        array('Q2',   56,   73,   86),
        array('Q3',   52,   61,   69),
        array('Q4',   30,   32,    0),
    );
    $header= array('用户名','密码','头像','性别');
     */
    public static function create_xlsx($data, $header=null, $filename='simple.xlsx')
    {
        ini_set('max_execution_time', '0');
        /*require_once __DIR__ . '/../Lib/PHPExcel/Classes/PHPExcel.php';
        require_once __DIR__ . '/../Lib/PHPExcel/Classes/PHPExcel/IOFactory.php';
        require_once __DIR__ . '/../Lib/PHPExcel/Classes/PHPExcel/Reader/Excel5.php';
        require_once __DIR__ . '/../Lib/PHPExcel/Classes/PHPExcel/Cell.php';*/

        // 如果手动设置表头；则放在第一行
        if (!is_null($header)) {
            array_unshift($data, $header);
        }

        $filename=str_replace('.xlsx', '', $filename).'.xlsx';
        $phpexcel = new \PHPExcel();
        $phpexcel->getProperties()
            ->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        $phpexcel->getActiveSheet()->fromArray($data);
        $phpexcel->getActiveSheet()->setTitle('Sheet1');
        $phpexcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$filename");
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        $objwriter = \PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
        $objwriter->save('php://output');
        exit;
    }
}