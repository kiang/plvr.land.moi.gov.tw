<?php

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

$result = array();
foreach (glob(__DIR__ . '/data/20*') AS $dirPath) {
    if (!is_dir($dirPath)) {
        continue;
    }
    error_log("processing {$dirPath}");
    $y = substr($dirPath, -6, 4);
    $q = substr($dirPath, -1);
    foreach ($cities AS $code => $city) {
        foreach ($metaTypes AS $metaType => $rootName) {
            $xml = loadXML("{$dirPath}/{$code}_lvr_land_{$metaType}.XML");
            $targetPath = "{$jsonPath}/{$y}/{$rootName}/{$city}";
            if (!file_exists($targetPath)) {
                mkdir($targetPath, 0777, true);
            }
            $data = array();
            foreach ($xml->{$rootName} AS $node) {
                $nodeCode = (string) $node->編號;
                $data[$nodeCode] = (array) $node;
                $data[$nodeCode]['YQ'] = "{$y}Q{$q}";
                foreach ($data[$nodeCode] AS $k => $v) {
                    if (is_object($v)) {
                        $data[$nodeCode][$k] = (string) $v;
                    } elseif (false !== strpos($k, '平方公尺')) {
                        $data[$nodeCode][$k] = floatval($v);
                    } elseif (false !== strpos($k, '總價')) {
                        $data[$nodeCode][$k] = intval($v);
                    }
                }
            }
            foreach ($subTypes AS $subType => $subTypeRoot) {
                $xmlFile = "{$dirPath}/{$code}_lvr_land_{$metaType}_{$subType}.XML";
                if (file_exists($xmlFile)) {
                    $xml = loadXML($xmlFile);
                    foreach ($xml->{$subTypeRoot} AS $node) {
                        if (isset($node->編號)) {
                            $nodeCode = (string) $node->編號;
                            unset($node->編號);
                            $nodeData = (array) $node;
                            foreach ($nodeData AS $k => $v) {
                                if (is_object($v)) {
                                    $nodeData[$k] = (string) $v;
                                } elseif (false !== strpos($k, '平方公尺')) {
                                    $nodeData[$k] = floatval($v);
                                } elseif (false !== strpos($k, '總價')) {
                                    $nodeData[$k] = intval($v);
                                }
                            }
                            if (!isset($data[$nodeCode][$subTypeRoot])) {
                                $data[$nodeCode][$subTypeRoot] = array();
                            }
                            $data[$nodeCode][$subTypeRoot][] = $nodeData;
                        }
                    }
                }
            }
            foreach ($data AS $k => $v) {
                file_put_contents($targetPath . '/' . $k . '.json', json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }
}

function loadXML($xmlFile) {
    $s = file_get_contents($xmlFile);
    $s = str_replace('&', '&amp;', $s);
    return simplexml_load_string($s);
}
