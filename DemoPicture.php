<?php
class DemoPicturer 
{
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
	private function imageTransferData($image, int $width, int $height)
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
	private function formatTransferData(array $data)
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
}