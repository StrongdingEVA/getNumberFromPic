<?php

class Image
{
    //字符集
    const charHash = "#ABCDEFGHIJKLMNOPQRSTUVWXYZ@$%??__ff--++~~''  ::..  ``  ";

    const level = 5;
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

    /**
     * 通过像素rgb值计算灰度值 然后通过灰度值获取相对应的字符
     */
    public function tochars($r, $g, $b)
    {
        //计算灰度值 
        //1.浮点算法：Gray=R0.3+G0.59+B*0.11 
        //2.整数方法：Gray=(R30+G59+B*11)/100 
        //3.移位方法：Gray =(R76+G151+B*28)>>8; 
        //4.平均值法：Gray=（R+G+B）/3; 
        //5.仅取绿色：Gray=G；
        $gray = floor(($r + $g + $b) / 3);


        //分级转换成字符表示
        //要想把0-255(rgb值)转换成相对应字符，由于 ceil(255 / (字符集长度)) 的结果值为 self::level
        $index = floor($gray / self::level);
        // if ($index >= strlen(self::charHash)) {
        //     $index = strlen(self::charHash) - 1;
        // }
        $char = self::charHash[(int)$index];
        if ($char == '#' || $char == '  ') {
            return $char;
        } else {
            return ' ';
        }
    }

    public function transferToAsc($fileName)
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

        list($image, $width, $height) = $this->tosmallim($width, $height, $image, 800);

        $str = '<span style="font-size: 8pt;
        letter-spacing: 4px;
        line-height: 8pt;
        font-weight: bold;display: block;
        font-family: monospace;
        white-space: pre;
        margin: 1em 0;">';
        for ($i = 0; $i < $height; $i++) {
            for ($j = 0; $j < $width; $j++) {
                $rgb = ImageColorat($image, $j, $i);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $str .= $this->tochars($r, $g, $b);
            }
            $str .= '<br>';
        }
        $str .= '</span>';
        echo $str;
    }

    public function black($fileName)
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

        $imageNew = imagecreatetruecolor($width, $height);
        for ($i = 0; $i < $height; $i++) {
            for ($j = 0; $j < $width; $j++) {
                $rgb = ImageColorat($image, $j, $i);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                if ($r >= 100 && $g >= 100 && $b >= 100) {
                    $color = imagecolorallocate($imageNew, 255, 255, 255);
                    imagesetpixel($imageNew, $j, $i, $color);
                } else {
                    $color = imagecolorallocate($imageNew, 0, 0, 0);
                    imagesetpixel($imageNew, $j, $i, $color);
                }
            }
        }

        imagejpeg($imageNew, '1.jpeg');
        imagedestroy($image);
        imagedestroy($imageNew);

        $this->transferToAsc('1.jpeg');
    }

    public function writeDemo()
    {
        $font = 'F:\www\IDcard\simhei.ttf';
        $image = imagecreatetruecolor(670, 100);
        $baise = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $baise);
        $color = imagecolorallocate($image, 0, 0, 0);
        imagettftext($image, 100, 0, 0, 100, $color, $font, '0123456789');
        imagejpeg($image, '1_100.jpeg');
    }

    public function numberCut()
    {
        $file = './1.jpeg';
        $image = imagecreatefromjpeg($file);
        $imageInfo = getimagesize($file);
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $arr = [];
        $buck = 0;

        for ($i = 0; $i < $width; $i++) {
            $haysh = 0;
            $temp = [];
            for ($j = 0; $j < $height; $j++) {
                $rgb = ImageColorat($image, $i, $j);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                if ($r < 50 && $g < 50 && $b < 50) {
                    $temp[$j] = 1;
                    $haysh++;
                } else {
                    $temp[$j] = 0;
                }
            }
            if ($haysh >= 5) {
                $arr[$buck][] = $temp;
            } else {
                $buck++;
            }
        }

        $data[0] = $this->getNumberString(0);
        $data[1] = $this->getNumberString(1);
        $data[2] = $this->getNumberString(2);
        $data[3] = $this->getNumberString(3);
        $data[4] = $this->getNumberString(4);
        $data[5] = $this->getNumberString(5);
        $data[6] = $this->getNumberString(6);
        $data[7] = $this->getNumberString(7);
        $data[8] = $this->getNumberString(8);
        $data[9] = $this->getNumberString(9);

        foreach ($arr as $val) {
            if ($val) {
                $str = '';
                $len = count($val);
                for ($i = 0; $i < 230; $i++) {
                    for ($j = 0; $j < $len; $j++) {
                        $str .= $val[$j][$i];
                    }
                }

                foreach ($data as $key => $item) {
                    similar_text(md5($str), md5($item), $percent);
                    if ($percent >= 40) {
                        echo $key;
                        continue;
                    }
                }
                // exit;
            }
        }
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
}

set_time_limit(0);
$obj = new Image();
$obj->numberCut();
