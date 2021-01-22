<?php

class Image
{
    /**
     * 设置三基色 小于此值都认为是黑色
     */
    const R = 50;
    const G = 50;
    const B = 50;

    const DEMO_FONT_SIZE = 100;

    /**
     * 缩放图片尺寸
     */
    public function toSmallim($width, $height, $image, $trueWith = 100)
    {
        if ($width > $trueWith) {
            $times = floor($width / $trueWith);
            $smwidth = $trueWith;
            $smheight = floor($height / $times);
            $im = imagecreatetruecolor($smwidth, $smheight);
            imagecopyresampled($im, $image, 0, 0, 0, 0, $smwidth, $smheight, $width, $height);
            return [$im, $smwidth, $smheight];
        }
        return [$image, $width, $height];
    }

    public function resizeByHeight($width, $height, $image, $trueHeight = 100)
    {
        if ($width > $trueHeight) {
            $times = floor($height / $trueHeight);
            $trueWith = floor($width / $times);
            $im = imagecreatetruecolor($trueWith, $trueHeight);
            imagecopyresampled($im, $image, 0, 0, 0, 0, $trueWith, $trueHeight, $width, $height);
            return [$im, $trueWith, $trueHeight];
        }
        return [$image, $width, $height];
    }

    public function imageToGray($fileName)
    {
        if (!preg_match('/^http|^https/', $fileName) && !is_file($fileName)) {
            exit('文件不存在');
        }

        $size = getimagesize($fileName);
        $width = $size[0];
        $height = $size[1];

        if ($size['mime'] == 'image/png') {
            $image = imagecreatefrompng($fileName);
        } else if ($size['mime'] == 'image/jpeg' || $size['mime'] == 'image/jpg') {
            $image = imagecreatefromjpeg($fileName);
        } else {
            exit('图片格式不符');
        }

        if (!$image) {
            exit('获取图片失败');
        }

        // list($image, $width, $height) = $this->tosmallim($width, $height, $image, 500);

        $imageNew = imagecreatetruecolor($width, $height);
        for ($i = 0; $i < $height; $i++) {
            for ($j = 0; $j < $width; $j++) {
                $rgb = ImageColorat($image, $j, $i);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $gray = floor(($r + $g + $b) / 3);
                $color = imagecolorallocate($imageNew, $gray, $gray, $gray);

                imagesetpixel($imageNew, $j, $i, $color);
            }
        }
        imagejpeg($imageNew, '111.jpeg');
        imagedestroy($image);
        imagedestroy($imageNew);
    }



    public function numberMatch($file)
    {
        list($image, $width, $height) = $this->extractNumber($file);

        list($image, $width, $height) = $this->resizeByHeight($width, $height, $image, 100);

        $transferData = $this->imageTransferData($image, $width, $height);

        $stringData[0] = $this->RsaEncrypt($this->getNumberString(0));
        $stringData[1] = $this->RsaEncrypt($this->getNumberString(1));
        //$data[2] = $this->RsaEncrypt($this->getNumberString(2));
        $stringData[3] = $this->RsaEncrypt($this->getNumberString(3));
        //$data[4] = $this->RsaEncrypt($this->getNumberString(4));
        $stringData[5] = $this->RsaEncrypt($this->getNumberString(5));
        //$data[6] = $this->RsaEncrypt($this->getNumberString(6));
        $stringData[7] = $this->RsaEncrypt($this->getNumberString(7));
        $stringData[8] = $this->RsaEncrypt($this->getNumberString(8));
        $stringData[9] = $this->RsaEncrypt($this->getNumberString(9));

        $data = [];
        foreach ($transferData as $val) {
            if ($val && count($val) >= 30) {
                $data[] = $val;
            }
        }

        foreach ($data as $item) {
            $item = $this->formatTransferData($item);
            foreach ($item as $a) {
                echo implode('', $a) . '<br>';
            }
            echo '<br><br><br><br>';
            // $mergeData = $this->RsaEncrypt($this->mergeTransferData($item));
            // foreach ($stringData as $k => $v) {
            //     similar_text($mergeData, $v, $percent);
            //     echo "数字:" . $k . '---相似度:' . $percent . '<br />';
            // }
            // echo '<br /><br /><br /><br /><br />';
            // break;
        }
        echo '结束';
    }

    public function getNumberString(int $number = 0)
    {
        $str = '';
        $fileName = './data/' . $number . '_.txt';
        $data = json_decode(file_get_contents($fileName));
        foreach ($data as $item) {
            $str .= implode('', $item);
        }
        return $str;
    }

    /**
     * 提取数字
     */
    public function extractNumber($file)
    {
        $size = getimagesize($file);
        $image = imagecreatefromjpeg($file);

        $width = $size[0];
        $height = $size[1];

        //list($image, $width, $height) = $this->toSmallim($width, $height, $image, 500);

        $hayshStartY = 0;
        $hayshStartX = 0;

        $hayshEndY = 0;
        $hayshEndX = 0;

        //由上到下取出数字开始的纵坐标
        for ($i = 0; $i < $height; $i++) {
            $blackPointCount = 0;
            $totalPointCount = $width;
            for ($j = 0; $j < $width; $j++) {
                list($r, $g, $b) = $this->getPixlRGB($image, $j, $i);
                if ($r < 50 && $g < 50 && $b < 50) { //黑色
                    $blackPointCount++;
                }
            }
            if (($blackPointCount / $totalPointCount) >= 0.005) {
                $hayshStartY = $i;
                break;
            }
        }

        //由下到上取出数字结束的纵坐标
        for ($i = $height - 1; $i >= 0; $i--) {
            $blackPointCount = 0;
            $totalPointCount = $width;
            for ($j = 0; $j < $width; $j++) {
                list($r, $g, $b) = $this->getPixlRGB($image, $j, $i);
                if ($r < 50 && $g < 50 && $b < 50) { //黑色
                    $blackPointCount++;
                }
            }
            if (($blackPointCount / $totalPointCount) >= 0.09) {
                $hayshEndY = $i;
                break;
            }
        }

        //由左到右取出数字开始的横坐标
        for ($i = 0; $i < $width; $i++) {
            $blackPointCount = 0;
            $totalPointCount = $height;
            for ($j = 0; $j < $height; $j++) {
                list($r, $g, $b) = $this->getPixlRGB($image, $i, $j);
                if ($r < 50 && $g < 50 && $b < 50) { //黑色
                    $blackPointCount++;
                }
            }
            if (($blackPointCount / $totalPointCount) >= 0.15) {
                $hayshStartX = $i;
                break;
            }
        }

        //由右到左取出数字结束的横坐标
        for ($i = $width - 1; $i >= 0; $i--) {
            $blackPointCount = 0;
            $totalPointCount = $height;
            for ($j = 0; $j < $height; $j++) {
                list($r, $g, $b) = $this->getPixlRGB($image, $i, $j);
                if ($r < 50 && $g < 50 && $b < 50) { //黑色
                    $blackPointCount++;
                }
            }
            if (($blackPointCount / $totalPointCount) >= 0.09) {
                $hayshEndX = $i;
                break;
            }
        }
        // print_r([$hayshStartX, $hayshStartY, $hayshEndX, $hayshEndY]);
        // exit;
        if ($hayshStartX && $hayshStartY && $hayshEndX && $hayshEndY) {
            $widthNew = $hayshEndX - $hayshStartX;
            $heightNew = $hayshEndY - $hayshStartY;
            $imageNew = imagecreatetruecolor($widthNew, $heightNew);
            $color = imagecolorallocate($imageNew, 255, 255, 255);
            imagefill($imageNew, 0, 0, $color);

            imagecopyresampled($imageNew, $image, 0, 0, $hayshStartX, $hayshStartY, $widthNew, $heightNew, $widthNew, $heightNew);

            imagedestroy($image);
            return [$imageNew, $widthNew, $heightNew];
        }
        return false;
    }



    /**
     * 获取像素点rgb
     */
    public function getPixlRGB($image, int $x, int $y)
    {
        $rgb = ImageColorat($image, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        return [$r, $g, $b];
    }

    public function createDemoData()
    {
        //创建demo图片
        list($image, $width, $height) = $this->createDemoImage();
        //图片转数字
        $transferData = $this->imageTransferData($image, $width, $height);

        $this->saveTranferData($transferData);
        imagejpeg($image, './images/demo.jpeg');
        echo '完成!';
    }

    /**
     * 创建demo图片
     */
    public function createDemoImage()
    {
        $width = 800;
        $height = 100;
        $font = __DIR__ . '\fonts\OCR-B10BT.ttf';
        $image = imagecreatetruecolor($width, $height);
        $baise = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $baise);
        $color = imagecolorallocate($image, 0, 0, 0);
        imagettftext($image, $height, 0, 0, $height, $color, $font, '0123456789');
        return [$image, $width, $height];
    }

    /**
     * 图片转字符串 白色像素点为0黑色为1
     */
    public function imageTransferData($image, int $width, int $height)
    {
        $data = [];
        $block = 0;
        $flag = false;
        for ($i = 0; $i < $width; $i++) {
            $hit = 0;
            $temp = [];
            for ($j = 0; $j < $height; $j++) {
                list($r, $g, $b) = $this->getPixlRGB($image, $i, $j);
                if ($r < self::R && $g < self::G && $b < self::B) {
                    $hit++;
                    $temp[$j] = 1;
                } else {
                    $temp[$j] = 0;
                }
            }
            if ($hit >= 5) {
                $flag = true;
                $data[$block][] = $temp;
            } else {
                if ($flag) {
                    $block++;
                }
                $flag = false;
            }
        }
        return $data;
    }

    /**
     * 拼接转换后的数字为字符串
     */
    public function mergeTransferData(array $data)
    {
        $mergeData = '';
        $len = count($data); //图片中数字的长度
        for ($i = 0; $i < self::DEMO_FONT_SIZE; $i++) {
            $temp = [];
            for ($j = 0; $j < $len; $j++) {
                $temp[] = $data[$j][$i];
            }
            $mergeData .= implode('', $temp);
        }
        return $mergeData;
    }

    /**
     * 格式化转换后的数字用于存储
     */
    public function formatTransferData(array $data)
    {
        $transferData = [];
        $len = count($data); //图片中数字的长度
        for ($i = 0; $i < self::DEMO_FONT_SIZE; $i++) {
            $temp = [];
            for ($j = 0; $j < $len; $j++) {
                $temp[] = $data[$j][$i];
            }
            $transferData[] = $temp;
        }
        return $transferData;
    }

    /**
     * 保存demo图片数字化信息
     */
    public function saveTranferData(array $data)
    {
        foreach ($data as $key => $val) {
            if ($val) {
                $data = $this->formatTransferData($val);
                foreach ($data as $a) {
                    echo implode('', $a) . '<br>';
                }
                echo '<br><br><br><br>';
                //file_put_contents('./data/' . $key . '_.txt', json_encode($data));
            }
        }
    }

    /**
     * 加密字符串
     */
    public function RsaEncrypt($str)
    {
        return $str;
        // return password_hash($str, PASSWORD_BCRYPT, ['cost' => 8]);
    }
}

set_time_limit(0);
$obj = new Image();
$obj->numberMatch('./images/1.jpeg');
// $obj->createDemoData();
