<?php
class Tool
{
    public static function getNumberString(int $number = 0)
	{
        $number = intval($number);

        if ($number < 0 || $number > 9) {
            return false;
        }
        
		$str = '';
		$fileName = './data/' . $number . '_.txt';
		$data = json_decode(file_get_contents($fileName));
		foreach ($data as $item) {
			$str .= implode('', $item);
		}
		return $str;
	}
}