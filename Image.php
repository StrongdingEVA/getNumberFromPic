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

	const DEMO_WITH = 800;

	const DEMO_HEIGHT = 200;

	private $fileName;

	private $error;

	private $imgType;

	public function __construct(string $fileName)
	{
		$this->fileName = $fileName;
		if (!$this->checkAndSetType($fileName)) {
			return $this->error;
		}
	}

	/**
	 * 从图片中匹配数字
	 */
	public function numberMatch()
	{
		list($image, $width, $height) = $this->crop();

		list($image, $width, $height) = $this->resizeByHeight($width, $height, $image, 100);

		$transferData = self::toData($image, $width, $height);

		return $this->doMatch($transferData);
	}

	/**
	 * 创建测试用的图片并保存为数据
	 */
	public static function createDemoPicAndSvaeToData(string $number, string $saveName)
	{
		if (!preg_match('/^\d+$/', $number)) {
			return false;
		}

		if (empty($saveName)) {
			return false;
		}

		//创建demo图片
		list($image, $width, $height) = self::createDemo($number);
		if (!$image) {
			return false;
		}

		//图片转数字
		$transferData = self::toData($image, $width, $height, $saveName);

		self::saveTranferData($transferData, $saveName);
		imagejpeg($image, './images/' . $saveName);
		return true;
	}

	private function doMatch($transferData)
	{
		$stringData[0] = Tool::getNumberString(0);
		$stringData[1] = Tool::getNumberString(1);
		$stringData[2] = Tool::getNumberString(2);
		$stringData[3] = Tool::getNumberString(3);
		$stringData[4] = Tool::getNumberString(4);
		$stringData[5] = Tool::getNumberString(5);
		$stringData[6] = Tool::getNumberString(6);
		$stringData[7] = Tool::getNumberString(7);
		$stringData[8] = Tool::getNumberString(8);
		$stringData[9] = Tool::getNumberString(9);

		$data = [];
		foreach ($transferData as $val) {
			if ($val && count($val) >= 30) {
				$data[] = $val;
			}
		}

		$res = [];
		foreach ($data as $item) {
			// $item = $this->formatTransferData($item);
			// foreach ($item as $a) {
			// 	echo implode('', $a) . '<br>';
			// }
			// echo '<br><br><br><br>';
			$mergeData = $this->mergeTransferData($item);
			foreach ($stringData as $k => $v) {
				$similarData = [];
			    similar_text($mergeData, $v, $percent);
				$similarData['number'] = $k;
				$similarData['similar'] = $percent;

				$res[] = $similarData;
			}
		}
		return $res;
	}

	/**
	 * 根据图片的宽缩放图片尺寸
	 */
	private function resizeByWidth($width, $height, $image, $trueWith = 100)
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

	/**
	 * 根据图片的长缩放图片尺寸
	 */
	private function resizeByHeight($width, $height, $image, $trueHeight = 100)
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

	/**
	 * 转成灰度图
	 */
	private function toGray($fileName)
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
	 * 截取含有数字部分的图片
	 */
	private function crop()
	{
		$image = $this->getImageHandle();
		if (!$image) {
			return false;
		}

		$size = getimagesize($this->fileName);
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
				if ($r < self::R && $g < self::G && $b < self::B) { //黑色
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

	private function getImageHandle()
	{
		if ($this->imgType == 'image/png') {
			$image = imagecreatefrompng($this->fileName);
		} else if ($this->imgType == 'image/jpeg' || $this->imgType == 'image/jpg') {
			$image = imagecreatefromjpeg($this->fileName);
		} else {
			$this->error = '图片格式不符';
			$image = false;
		}

		return $image;
	}

	/**
	 * 获取像素点rgb
	 */
	private static function getPixlRGB($image, int $x, int $y)
	{
		$rgb = ImageColorat($image, $x, $y);
		$r = ($rgb >> 16) & 0xFF;
		$g = ($rgb >> 8) & 0xFF;
		$b = $rgb & 0xFF;
		return [$r, $g, $b];
	}
	
	/**
	 * 创建demo图片
	 */
	private static function createDemo(string $number)
	{
		$font = __DIR__ . '\fonts\OCR-B10BT.ttf';
		$image = imagecreatetruecolor(self::DEMO_WITH, self::DEMO_HEIGHT);
		$baise = imagecolorallocate($image, 255, 255, 255);
		imagefill($image, 0, 0, $baise);
		$color = imagecolorallocate($image, 0, 0, 0);
		imagettftext($image, self::DEMO_HEIGHT, 0, 0, self::DEMO_HEIGHT, $color, $font, $number);

		return [$image, self::DEMO_WITH, self::DEMO_HEIGHT];
	}

	/**
	 * 图片转字符串 白色像素点为0黑色为1
	 */
	private static function toData($image, int $width, int $height)
	{
		$data = [];
		$block = 0;
		$flag = false;
		for ($i = 0; $i < $width; $i++) {
			$hit = 0;
			$temp = [];
			for ($j = 0; $j < $height; $j++) {
				list($r, $g, $b) = self::getPixlRGB($image, $i, $j);
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
	private function mergeTransferData(array $data)
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
	private static function formatTransferData(array $data)
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
	private static function saveTranferData(array $data, string $saveName)
	{
		foreach ($data as $key => $val) {
			if ($val) {
				$data = self::formatTransferData($val);
				file_put_contents('./data/' . $saveName . '_' . $key . '_.txt', json_encode($data));
			}
		}
	}

	/**
	 * 检查图片并设置类型
	 */
	private function checkAndSetType() 
	{
		$fileName = $this->fileName;
		if (!is_file($fileName)) {
			$this->error = '文件不存在';
			return false;
		}

		$size = getimagesize($fileName);
		if (isset($size['mime'])) {
			$this->imgType = $size['mime'];
		}else{
			$this->error = '图片格式不符';
			return false;
		}
	}
}

Image::createDemoPicAndSvaeToData('4455667', 'test');