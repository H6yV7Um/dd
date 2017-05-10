<?php
/**
 * Created by PhpStorm.
 * User: muchao
 * Date: 15/7/13
 * Time: 下午3:10
 */

require_once(AWS_PATH . "/aws-autoloader.php");

use Aws\S3\S3Client;

class Aws_s3 {
    private static $client = null;
    private static $ins = null;

    // get key&secret from config file or somewhere
    private function __construct() {
        // Creating a client
        self::$client = S3Client::factory(array(
            'key'     => 'AKIAJ4JKKKW5U6WWLE2A',
            'secret'  => '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',
            'region'  => 'us-east-1',
            'version' => 'latest',
            'debug'=>true,
        ));
        //        var_dump(self::$client);
    }

    // singleton
    public static function getInstance() {
        if (!(self::$ins instanceof self)) {
            self::$ins = new self();
        }
        return self::$ins;
    }

    /**
     * @param        $file_path : file in local path
     * @param        $filename  : filename or empty which will generate a random one
     * @param string $bucket    : aws bucket
     *
     * @return array
     */
    public static function upload($file_path, $filename, $bucket = 'default') {
        $ret = array();

        $ret['err'] = 0;
        $ret['msg'] = 'success';

        if (empty($filename)) {
            $filename = md5($file_path);
        }

        $params               = array();
        $params['Bucket']     = $bucket;
        $params['Key']        = $filename;
        $params['SourceFile'] = $file_path;
        $params['ACL']        = 'public-read';
        try {
            $result = self::$client->putObject($params);
        } catch (Exception $e) {
            $ret['err'] = '-1';
            $ret['msg'] = $e->getMessage();
            return $ret;
        }
        $result     = $result->toArray();
        $ret['url'] = $result['ObjectURL'];

        return $ret;
    }

    public static function display() {
        var_dump(self::$client->getBucketAcl(array('Bucket' => 'test4talebox')));
        var_dump(self::$client->getRegion());
        var_dump(self::$client->listBuckets());
    }
}


