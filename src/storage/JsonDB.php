<?php
class JsonDB {
    private static $path = __DIR__ . "/../repositores/";


    public static function read($filename) {
        $file = self::$path . $filename;
        return json_decode(file_get_contents($file), true) ?? [];
    }

    public static function write($filename, $data) {
        $file = self::$path . $filename;
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    public static function getJsonById($id, $name, $key) {
        $items = self::read($name.".json");
        foreach ($items as $item) {
            if ($item[$key] == $id) {
                return $item;
            }
        }
        return null;
    }
}
?>
