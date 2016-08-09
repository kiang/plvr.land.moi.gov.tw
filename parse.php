<?php

include '/home/kiang/public_html/add/taiwan-address-data-master/lookup.php';

$cities = array(
    'A' => '臺北市',
    'B' => '臺中市',
    'C' => '基隆市',
    'D' => '臺南市',
    'E' => '高雄市',
    'F' => '新北市',
    'G' => '宜蘭縣',
    'H' => '桃園縣',
    'I' => '嘉義市',
    'J' => '新竹縣',
    'K' => '苗栗縣',
    'M' => '南投縣',
    'N' => '彰化縣',
    'O' => '新竹市',
    'P' => '雲林縣',
    'Q' => '嘉義縣',
    'T' => '屏東縣',
    'U' => '花蓮縣',
    'V' => '臺東縣',
    'W' => '金門縣',
    'X' => '澎湖縣',
    'Z' => '連江縣',
);
/*
 * 不動產買賣 - A
  預售屋買賣 - B
  不動產租賃 - C
 */
$metaTypes = array(
    'A' => '買賣',
    'B' => '預售屋',
    'C' => '租賃',
);
$subTypes = array(
    'BUILD' => '建物',
    'LAND' => '土地',
    'PARK' => '停車位',
);
$jsonPath = __DIR__ . '/json/';

$ref = array();
foreach (glob(__DIR__ . '/data/20*') AS $dirPath) {
    if (!is_dir($dirPath)) {
        continue;
    }
    $prefix = substr($dirPath, -6);
    if (file_exists(__DIR__ . '/tmp/' . $prefix)) {
        continue;
    }
    file_put_contents(__DIR__ . '/tmp/' . $prefix, '1');
    $oFh = fopen(__DIR__ . '/' . $prefix . 'address.csv', 'w');
    fputcsv($oFh, array('address', 'county', 'town', 'village', 'x', 'y'));
    foreach ($cities AS $code => $city) {
        foreach ($metaTypes AS $metaType => $rootName) {
            error_log("processing {$dirPath}/{$code}_lvr_land_{$metaType}.XML");
            $xml = loadXML("{$dirPath}/{$code}_lvr_land_{$metaType}.XML");
            $data = array();
            foreach ($xml->{$rootName} AS $node) {
                if (false !== strpos((string) $node->交易標的, '房地')) {
                    $address = (string) $node->土地區段位置或建物區門牌;
                    if (!isset($ref[$address])) {
                        $line = array(
                            $address,
                        );
                        $ref[$address] = true;
                        preg_match_all('/[0-9]+/i', $address, $matches, PREG_OFFSET_CAPTURE);
                        $number2 = array_pop($matches[0]);
                        $number1 = array_pop($matches[0]);
                        $address = substr($address, 0, $number1[1]) . substr($address, $number2[1]);
                        $result = AddressLookup::lookup($address);
                        if (is_array($result) && is_object($result[0])) {
                            $line[] = $result[0]->COUNTY;
                            $line[] = $result[0]->TOWN;
                            $line[] = $result[0]->VILLAGE;
                            $line[] = $result[0]->X;
                            $line[] = $result[0]->Y;
                            fputcsv($oFh, $line);
                        }
                    } elseif (is_array($ref[$address])) {
                        fputcsv($oFh, $ref[$address]);
                    }
                }
            }
        }
    }
}

function loadXML($xmlFile) {
    $s = file_get_contents($xmlFile);
    $s = str_replace('&', '&amp;', $s);
    return simplexml_load_string($s);
}
