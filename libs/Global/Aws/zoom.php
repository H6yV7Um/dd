<?php
/**
 * Created by PhpStorm.
 * User: muchao
 * Date: 15/7/14
 * Time: 上午10:09
 */

define("AWS_PATH", dirname(__FILE__) . '/s3/');
require_once 's3.php';

function change($url) {
    $filename = basename($url);
    var_dump($filename);
    $filepath      = "/tmp/";
    $file          = $filepath . $filename;
    $new_file_name = 'picture' . time() . md5($file) . '.jpg';
    $new_file      = $filepath . $new_file_name;
    var_dump($new_file);
    file_put_contents($file, file_get_contents($url));
    imagezoom($file, $new_file, 300, 300, '#000000');
    unlink($file);
    //$result = Aws_s3::getInstance()->upload($new_file, $new_file_name, 'vshow');
    var_dump($result);
    return $result;
}


$file = "IMG_2089.JPG";
$size = getimagesize($file);
var_dump($size);
$weight   = $size[0];
$heigh    = $size[1];
$new_file = "new_test_300.jpg";
if ($weight >= $heigh) {
    // 以高为主裁剪
    imagecropper($file, $new_file, 300, 300);
} else {
    // 以宽为主裁剪
    imagecropper($file, $new_file, 300, 300);
}

function imagecropper($source_path, $dest_file, $target_width, $target_height) {
    $source_info   = getimagesize($source_path);
    $source_width  = $source_info[0];
    $source_height = $source_info[1];
    $source_mime   = $source_info['mime'];
    $source_ratio  = $source_height / $source_width;
    $target_ratio  = $target_height / $target_width;

    // 源图过高
    if ($source_ratio > $target_ratio) {
        $cropped_width  = $source_width;
        $cropped_height = $source_width * $target_ratio;
        $source_x       = 0;
        $source_y       = ($source_height - $cropped_height) / 2;
    } // 源图过宽
    elseif ($source_ratio < $target_ratio) {
        $cropped_width  = $source_height / $target_ratio;
        $cropped_height = $source_height;
        $source_x       = ($source_width - $cropped_width) / 2;
        $source_y       = 0;
    } // 源图适中
    else {
        $cropped_width  = $source_width;
        $cropped_height = $source_height;
        $source_x       = 0;
        $source_y       = 0;
    }

    switch ($source_mime) {
        case 'image/gif':
            $source_image = imagecreatefromgif($source_path);
            break;

        case 'image/jpeg':
            $source_image = imagecreatefromjpeg($source_path);
            break;

        case 'image/png':
            $source_image = imagecreatefrompng($source_path);
            break;

        default:
            return false;
            break;
    }

    $target_image  = imagecreatetruecolor($target_width, $target_height);
    $cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);

    // 裁剪
    imagecopy($cropped_image, $source_image, 0, 0, $source_x, $source_y, $cropped_width, $cropped_height);
    // 缩放
    imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $target_width, $target_height, $cropped_width, $cropped_height);

    imagejpeg($target_image, $dest_file, 95);
    imagedestroy($source_image);
    imagedestroy($target_image);
    imagedestroy($cropped_image);
}


function imagezoom($srcimage, $dstimage, $dst_width, $dst_height, $backgroundcolor) {
    // 中文件名乱码
    if (PHP_OS == 'WINNT') {
        $srcimage = iconv('UTF-8', 'GBK', $srcimage);
        $dstimage = iconv('UTF-8', 'GBK', $dstimage);
    }
    $dstimg = imagecreatetruecolor($dst_width, $dst_height);
    $color  = imagecolorallocate($dstimg
        , hexdec(substr($backgroundcolor, 1, 2))
        , hexdec(substr($backgroundcolor, 3, 2))
        , hexdec(substr($backgroundcolor, 5, 2))
    );
    imagefill($dstimg, 0, 0, $color);
    if (!$arr = getimagesize($srcimage)) {
        return false;
        // throw new Exception("the src file is not found!");
    }
    $src_width  = $arr[0];
    $src_height = $arr[1];
    $srcimg     = null;
    $method     = getcreatemethod($srcimage);
    if ($method) {
        eval('$srcimg = ' . $method . ';');
    }
    $dst_x = 0;
    $dst_y = 0;
    $dst_w = $dst_width;
    $dst_h = $dst_height;
    if (($dst_width / $dst_height - $src_width / $src_height) > 0) {
        $dst_w = $src_width * ($dst_height / $src_height);
        $dst_x = ($dst_width - $dst_w) / 2;
    } elseif (($dst_width / $dst_height - $src_width / $src_height) < 0) {
        $dst_h = $src_height * ($dst_width / $src_width);
        $dst_y = ($dst_height - $dst_h) / 2;
    }
    imagecopyresampled($dstimg, $srcimg, $dst_x
        , $dst_y, 0, 0, $dst_w, $dst_h, $src_width, $src_height);
    // 保存格式
    $arr    = array(
        'jpg'    => 'imagejpeg'
        , 'jpeg' => 'imagejpeg'
        , 'png'  => 'imagepng'
        , 'gif'  => 'imagegif'
        , 'bmp'  => 'imagebmp'
    );
    $suffix = strtolower(array_pop(explode('.', $dstimage)));
    if (!in_array($suffix, array_keys($arr))) {
        return false;
        // throw new Exception("dest file create failed!");
    } else {
        eval($arr[$suffix] . '($dstimg, "' . $dstimage . '");');
    }
    imagejpeg($dstimg, $dstimage);
    imagedestroy($dstimg);
    imagedestroy($srcimg);
}

function getcreatemethod($file) {
    $arr  = array(
        '474946'   => "imagecreatefromgif('$file')"
        , 'FFD8FF' => "imagecreatefromjpeg('$file')"
        , '424D'   => "imagecreatefrombmp('$file')"
        , '89504E' => "imagecreatefrompng('$file')"
    );
    $fd   = fopen($file, "rb");
    $data = fread($fd, 3);
    $data = str2hex($data);
    if (array_key_exists($data, $arr)) {
        return $arr[$data];
    } elseif (array_key_exists(substr($data, 0, 4), $arr)) {
        return $arr[substr($data, 0, 4)];
    } else {
        return false;
    }
}

function str2hex($str) {
    $ret = "";
    for ($i = 0; $i < strlen($str); $i++) {
        $ret .= ord($str[$i]) >= 16 ? strval(dechex(ord($str[$i])))
            : '0' . strval(dechex(ord($str[$i])));
    }
    return strtoupper($ret);
}

// BMP 创建函数 php本身无
function imagecreatefrombmp($filename) {
    if (!$f1 = fopen($filename, "rb")) {
        return FALSE;
    }
    $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1, 14));
    if ($FILE['file_type'] != 19778) {
        return FALSE;
    }
    $BMP           = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' .
        '/Vcompression/Vsize_bitmap/Vhoriz_resolution' .
        '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1, 40));
    $BMP['colors'] = pow(2, $BMP['bits_per_pixel']);
    if ($BMP['size_bitmap'] == 0) {
        $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
    }
    $BMP['bytes_per_pixel']  = $BMP['bits_per_pixel'] / 8;
    $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
    $BMP['decal']            = ($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
    $BMP['decal'] -= floor($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
    $BMP['decal'] = 4 - (4 * $BMP['decal']);
    if ($BMP['decal'] == 4) {
        $BMP['decal'] = 0;
    }
    $PALETTE = array();
    if ($BMP['colors'] < 16777216) {
        $PALETTE = unpack('V' . $BMP['colors'], fread($f1, $BMP['colors'] * 4));
    }
    $IMG  = fread($f1, $BMP['size_bitmap']);
    $VIDE = chr(0);
    $res  = imagecreatetruecolor($BMP['width'], $BMP['height']);
    $P    = 0;
    $Y    = $BMP['height'] - 1;
    while ($Y >= 0) {
        $X = 0;
        while ($X < $BMP['width']) {
            if ($BMP['bits_per_pixel'] == 24) {
                $COLOR = unpack("V", substr($IMG, $P, 3) . $VIDE);
            } elseif ($BMP['bits_per_pixel'] == 16) {
                $COLOR    = unpack("n", substr($IMG, $P, 2));
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            } elseif ($BMP['bits_per_pixel'] == 8) {
                $COLOR    = unpack("n", $VIDE . substr($IMG, $P, 1));
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            } elseif ($BMP['bits_per_pixel'] == 4) {
                $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
                if (($P * 2) % 2 == 0) {
                    $COLOR[1] = ($COLOR[1] >> 4);
                } else {
                    $COLOR[1] = ($COLOR[1] & 0x0F);
                }
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            } elseif ($BMP['bits_per_pixel'] == 1) {
                $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
                if (($P * 8) % 8 == 0) {
                    $COLOR[1] = $COLOR[1] >> 7;
                } elseif (($P * 8) % 8 == 1) {
                    $COLOR[1] = ($COLOR[1] & 0x40) >> 6;
                } elseif (($P * 8) % 8 == 2) {
                    $COLOR[1] = ($COLOR[1] & 0x20) >> 5;
                } elseif (($P * 8) % 8 == 3) {
                    $COLOR[1] = ($COLOR[1] & 0x10) >> 4;
                } elseif (($P * 8) % 8 == 4) {
                    $COLOR[1] = ($COLOR[1] & 0x8) >> 3;
                } elseif (($P * 8) % 8 == 5) {
                    $COLOR[1] = ($COLOR[1] & 0x4) >> 2;
                } elseif (($P * 8) % 8 == 6) {
                    $COLOR[1] = ($COLOR[1] & 0x2) >> 1;
                } elseif (($P * 8) % 8 == 7) {
                    $COLOR[1] = ($COLOR[1] & 0x1);
                }
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            } else {
                return false;
            }
            imagesetpixel($res, $X, $Y, $COLOR[1]);
            $X++;
            $P += $BMP['bytes_per_pixel'];
        }
        $Y--;
        $P += $BMP['decal'];
    }
    fclose($f1);
    return $res;
}

// BMP 保存函数，php本身无
function imagebmp($im, $fn = false) {
    if (!$im) {
        return false;
    }
    if ($fn === false) {
        $fn = 'php://output';
    }
    $f = fopen($fn, "w");
    if (!$f) {
        return false;
    }
    $biWidth     = imagesx($im);
    $biHeight    = imagesy($im);
    $biBPLine    = $biWidth * 3;
    $biStride    = ($biBPLine + 3) & ~3;
    $biSizeImage = $biStride * $biHeight;
    $bfOffBits   = 54;
    $bfSize      = $bfOffBits + $biSizeImage;
    fwrite($f, 'BM', 2);
    fwrite($f, pack('VvvV', $bfSize, 0, 0, $bfOffBits));
    fwrite($f, pack('VVVvvVVVVVV', 40, $biWidth, $biHeight, 1, 24, 0, $biSizeImage, 0, 0, 0, 0));
    $numpad = $biStride - $biBPLine;
    for ($y = $biHeight - 1; $y >= 0; --$y) {
        for ($x = 0; $x < $biWidth; ++$x) {
            $col = imagecolorat($im, $x, $y);
            fwrite($f, pack('V', $col), 3);
        }
        for ($i = 0; $i < $numpad; ++$i) {
            fwrite($f, pack('C', 0));
        }
    }
    fclose($f);
    return true;
}
