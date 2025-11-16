<?php
    define('ROUTE', '/export-csv');
    define('JSON_SRC_ADDR', 'https://jsonplaceholder.typicode.com/users');
    define('FILE_NAME', 'users.csv');
    define('USER_FIELDS', array(
        'ID'            => ['fieldName' => 'id'],
        'Імʼя'          => ['fieldName' => 'name'],
        'Логін'         => ['fieldName' => 'username'],
        'Пошта'         => ['fieldName' => 'email'],
        'Адреса'        => ['fieldName' => 'address', 'subFields' => ['street', 'suite', 'city', 'zipcode']],
        'Телефон'       => ['fieldName' => 'phone'],
        'Сайт'          => ['fieldName' => 'website'],
        'Організація'   => ['fieldName' => 'company', 'subFields' => ['name', 'catchPhrase', 'bs']]
    ));
    define('EMPTY_VALUE', '<не зазначено>');

    if ($_SERVER['REQUEST_URI'] == ROUTE) {
        $content = json_decode(getContentViaHTTP(JSON_SRC_ADDR));
        $fullPathFileName = __DIR__.DIRECTORY_SEPARATOR.FILE_NAME;
        $file = fopen($fullPathFileName, 'w');
        fputcsv($file, array_keys(USER_FIELDS));
        foreach ($content as $rawRecord) fputcsv($file, normalizeRecord($rawRecord));
        fclose($file);
        header("Content-Disposition: attachment; filename=".FILE_NAME);
        ob_clean();
        readfile($fullPathFileName);
    } else {
        echo('<html><body><button type="button" onclick="document.location=\''.ROUTE.'\';">Завантажити CSV</button><br></body></html>');
    }

    function getContentViaHTTP($url) {
        $curl_handle = curl_init($url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        $query = curl_exec($curl_handle);
        curl_close($curl_handle);
        return $query;
    }

    function normalizeRecord($rawData) {
        $result = [];
        foreach (USER_FIELDS as $description=>$params) {
            if (array_key_exists('subFields', $params)) {
                $subResult = [];
                foreach ($params['subFields'] as $subFieldName) {
                    if (property_exists($rawData, $params['fieldName']) && is_object($rawData->{$params['fieldName']})
                    && property_exists($rawData->{$params['fieldName']}, $subFieldName)) {
                        $subResult[] = $rawData->{$params['fieldName']}->$subFieldName;
                    } else {
                        $subResult[] = EMPTY_VALUE;
                    }
                }
                $result[] = implode(', ', $subResult);
            } else {
                $result[] = property_exists($rawData, $params['fieldName']) ? $rawData->{$params['fieldName']} : EMPTY_VALUE;
            }
        }
        return $result;
    }

?>