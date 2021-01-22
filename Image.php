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
        $font = 'E:\WWW\getNumberFromPic\simhei.ttf';
        $image = imagecreatetruecolor(670, 100);
        $baise = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $baise);
        $color = imagecolorallocate($image, 0, 0, 0);
        imagettftext($image, 100, 0, 0, 100, $color, $font, '0123456789');
        return [$image, 670, 100];
    }

    public function numberCut($image, $width, $height)
    {
        list($image, $width, $height) = $this->resizeByHeight($width, $height, $image, 100);

        $arr = [];
        $buck = 0;

        for ($i = 0; $i < $width; $i++) {
            $haysh = 0;
            $temp = [];
            for ($j = 0; $j < $height; $j++) {
                list($r, $g, $b) = $this->getPixlRGB($image, $i, $j);

                if ($r < 50 && $g < 50 && $b < 50) {
                    $temp[$j] = 1;
                    $haysh++;
                } else {
                    $temp[$j] = 0;
                }
            }
            if ($haysh >= 10) {
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
                for ($i = 0; $i < 100; $i++) {
                    for ($j = 0; $j < $len; $j++) {
                        $str .= $val[$j][$i];
                    }
                }

                foreach ($data as $key => $item) {
                    similar_text($str, $item, $percent);
                    echo "数字:" . $key . '---相似度:' . $percent . ' --- ';
                }
                echo '<br>';
            }
        }
        echo 'end';
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
    public function extractNumber()
    {
        $file = './images/1.jpeg';
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
            if (($blackPointCount / $totalPointCount) >= 0.09) {
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
            if (($blackPointCount / $totalPointCount) >= 0.09) {
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


    public function getPixlRGB($image, int $x, int $y)
    {
        $rgb = ImageColorat($image, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        return [$r, $g, $b];
    }
}

set_time_limit(0);
$obj = new Image();
// list($image, $width, $height) = $obj->extractNumber();
list($image, $width, $height) = $obj->writeDemo();
// imagejpeg($image, '2222222.jpeg');
// exit;
$obj->numberCut($image, $width, $height);
